<?php

namespace App\Http\Controllers;

use App\Models\MonthlyTargetTons;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PlantOutputController extends Controller
{
    private string $apiBase = 'http://172.17.2.235/inventory/api/tons_output.php';

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
                    'target'       => (int) $row['target'],
                    'working_days' => (int) ($row['working_days'] ?? 0),
                ]
            );
        }

        Cache::forget("plant_targets:{$plant}:{$rows[0]['year']}");

        return response()->json(['success' => true]);
    }
}
