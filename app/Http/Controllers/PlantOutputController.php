<?php
namespace App\Http\Controllers;
use App\Models\MonthlyTargetTons;
use App\Models\DailyTargetTons;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
class PlantOutputController extends Controller
{
    private string $apiBase = 'http://172.17.2.236/inventory/api/tons_output.php';
    public function index(Request $request, string $plant)
    {
        $now          = now();
        $currentYear  = $now->year;
        $currentMonth = $now->month;
        $requestedYear  = $request->integer('year',  $currentYear);
        $requestedMonth = $request->integer('month', $currentMonth);
        $isCurrent = $requestedYear  === $currentYear
                  && $requestedMonth === $currentMonth;
        $ttl = $isCurrent ? now()->addHour() : now()->addWeek();
        $cacheKey = "plant_output:{$plant}:{$requestedYear}:{$requestedMonth}";
        $data = Cache::remember($cacheKey, $ttl, function () use ($plant, $requestedYear, $requestedMonth, $cacheKey) {
            try {
                $response = Http::timeout(15)->get("{$this->apiBase}/getOutput.php", [
                    'plant' => $plant,
                    'year'  => $requestedYear,
                    'month' => $requestedMonth,
                ]);
                if ($response->failed()) {
                    throw new \Exception("External API error: {$response->status()}");
                }
                \Log::info('External API response', [
                    'status' => $response->status(),
                    'body'   => $response->json(),
                ]);
                return $response->json();
            } catch (\Exception $e) {
                $stale = Cache::get("{$cacheKey}:stale");
                if ($stale) return $stale;
                throw $e;
            }
        });
        return response()->json([
            'plant' => $plant,
            'year'  => $requestedYear,
            'month' => $requestedMonth,
            'data'  => $data,
        ]);
    }

    public function targets(Request $request, string $plant)
    {
        $year     = $request->integer('year', now()->year);
        $cacheKey = "plant_targets:{$plant}:{$year}";
        $data = Cache::remember($cacheKey, now()->addHours(24), function () use ($plant, $year) {
            return MonthlyTargetTons::where('plant', $plant)
                ->where('year', $year)
                ->get(['month', 'target', 'working_days'])
                ->keyBy('month')
                ->map(fn($row) => [
                    'target'       => $row->target,
                    'working_days' => $row->working_days,
                ])
                ->toArray();
        });
        return response()->json($data);
    }

    public function invalidate(string $plant, int $year, int $month): void
    {
        Cache::forget("plant_output:{$plant}:{$year}:{$month}");
    }

    public function storeTargets(Request $request, string $plant)
    {
        \Log::info('storeTargets payload', [
            'plant'   => $plant,
            'targets' => $request->input('targets')
        ]);
        $rows = $request->input('targets');
        if (empty($rows)) {
            return response()->json(['error' => 'No targets provided'], 422);
        }
        foreach ($rows as $row) {
            MonthlyTargetTons::updateOrCreate(
                [
                    'plant' => $plant,
                    'month' => $row['month'],
                    'year'  => (int) $row['year'],
                ],
                [
                    'target'       => (float) $row['target'],
                    'working_days' => (float) ($row['working_days'] ?? 0),
                ]
            );
        }
        Cache::forget("plant_targets:{$plant}:{$rows[0]['year']}");
        return response()->json(['success' => true]);
    }

    /**
     * GET /api/plant-daily-target/{plant}?year=&month=
     * Returns daily targets summed across all areas, keyed by day number.
     * e.g. { "1": 120.5, "2": 98.0, ... }
     */
    public function dailyTargets(Request $request, string $plant)
    {
        $year     = $request->integer('year',  now()->year);
        $monthInt = $request->integer('month', now()->month);
        $month    = \Carbon\Carbon::create()->month($monthInt)->format('F');
        $cacheKey = "plant_daily_targets:{$plant}:{$year}:{$monthInt}";

        $data = Cache::remember($cacheKey, now()->addHours(24), function () use ($plant, $year, $month) {
            return DailyTargetTons::where('plant', $plant)
                ->where('year',  $year)
                ->where('month', $month)   // now "March" instead of 3
                ->where('area', '!=', 'ALL')
                ->get(['area', 'day', 'target'])
                ->groupBy('area')
                ->map(fn($rows) => $rows->pluck('target', 'day')->toArray())
                ->toArray();
        });

        return response()->json($data);
    }

    /**
     * POST /api/plant-daily-target/{plant}
     * Payload: { "targets": [ { "month": "January", "year": 2025, "area": "ALL", "day": 1, "target": 120.5 }, ... ] }
     */
    public function storeDailyTargets(Request $request, string $plant)
    {
        \Log::info('storeDailyTargets payload', [
            'plant'   => $plant,
            'targets' => $request->input('targets')
        ]);

        $rows = $request->input('targets');
        if (empty($rows)) {
            return response()->json(['error' => 'No targets provided'], 422);
        }

        foreach ($rows as $row) {
            DailyTargetTons::updateOrCreate(
                [
                    'plant' => $plant,
                    'area'  => $row['area'],
                    'month' => $row['month'],
                    'year'  => (int) $row['year'],
                    'day'   => (int) $row['day'],
                ],
                [
                    'target' => (float) $row['target'],
                ]
            );
        }

        // Bust cache for every unique year+month combo in the payload
        collect($rows)
        ->map(fn($r) => ['year' => (int)$r['year'], 'month' => (int)$r['month_number']])
        ->unique(fn($k) => $k['year'] . '-' . $k['month'])
        ->each(fn($k) => Cache::forget("plant_daily_targets:{$plant}:{$k['year']}:{$k['month']}"));

        return response()->json(['success' => true]);
    }
}
