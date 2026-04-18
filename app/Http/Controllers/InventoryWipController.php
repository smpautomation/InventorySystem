<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InventoryWipController extends Controller
{
    private string $apiBase = 'http://172.17.2.236/inventory/api/inventory_wip.php';

    private function externalPlantName(string $plant): string
    {
        return match($plant) {
            'main'   => 'main',
            'plant7' => 'plant7',
            'plant8-1st' => 'plant8-1st',
            'plant8-2nd' => 'plant8-2nd',
            default  => ucfirst($plant),
        };
    }

    // Extract unique areas from a raw API response
    private function areasFromResponse(array $rows): array
    {
        return collect($rows)
            ->pluck('Area')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    public function init()
    {
        $cacheKey = 'inventory_wip:init';

        $data = Cache::remember($cacheKey, now()->addHour(), function () use ($cacheKey) {
            try {
                $response = Http::timeout(15)->get($this->apiBase, ['init' => 'true']);
                if ($response->failed()) {
                    throw new \Exception("External API error: {$response->status()}");
                }
                return $response->json();
            } catch (\Exception $e) {
                $stale = Cache::get("{$cacheKey}:stale");
                if ($stale) return $stale;
                throw $e;
            }
        });

        Cache::put("{$cacheKey}:stale", $data, now()->addDay());

        return response()->json($data);
    }

    public function graph(Request $request, string $plant)
    {
        $now   = now(config('app.timezone'));
        $year  = $now->year;
        $month = $now->month;

        // Build expected 6AM filenames from day 1 to today
        $expectedFiles = [];
        for ($day = 1; $day <= $now->day; $day++) {
            $date            = \Carbon\Carbon::create($year, $month, $day, 0, 0, 0, config('app.timezone'));
            $formatted       = $date->format('mdY');
            $expectedFiles[] = "WO_{$formatted}_(6AM).xlsx";
        }

        // Find fully snapshotted files for this plant
        $fullySnapshotted = DB::table('inventory_wip_snapshots')
            ->where('plant', $plant)
            ->where('daily_check_file', 'like', '%6AM%')
            ->whereYear('queried_at', $year)
            ->whereMonth('queried_at', $month)
            ->pluck('daily_check_file')
            ->unique()
            ->toArray();

        $missingFiles = array_diff($expectedFiles, $fullySnapshotted);

        // Always re-check today in case WIP has changed
        $todayFile = 'WO_' . $now->format('mdY') . '_(6AM).xlsx';
        if (!in_array($todayFile, $missingFiles)) {
            $lastSnapshotTime = DB::table('inventory_wip_snapshots')
                ->where('plant', $plant)
                ->where('daily_check_file', $todayFile)
                ->max('queried_at');

            if (!$lastSnapshotTime || $now->diffInMinutes($lastSnapshotTime) > 30) {
                $missingFiles[] = $todayFile;
                Cache::forget('inventory_wip:snapshot:' . md5($plant . $todayFile));
                DB::table('inventory_wip_snapshots')
                    ->where('plant', $plant)
                    ->where('daily_check_file', $todayFile)
                    ->delete();
            }
        }

        if (!empty($missingFiles)) {
            $availableFiles = $this->fetchAvailableFiles();
            $this->backfillMissingDays(
                array_values($missingFiles),
                $availableFiles,
                $plant,
            );
        }

        // Fetch all snapshot rows for this plant
        $rows = DB::table('inventory_wip_snapshots')
            ->where('plant', $plant)
            ->where('daily_check_file', 'like', '%6AM%')
            ->whereYear('queried_at', $year)
            ->whereMonth('queried_at', $month)
            ->get(['daily_check_file', 'process', 'area', 'tons'])
            ->toArray();

        // Initialize all expected days so empty ones appear in graph
        $grouped = [];
        foreach ($expectedFiles as $file) {
            $grouped[$file] = [];
        }

        foreach ($rows as $row) {
            $file    = $row->daily_check_file;
            $process = $row->process;

            if (!array_key_exists($file, $grouped)) continue;

            $grouped[$file][$process] = ($grouped[$file][$process] ?? 0.0) + (float) $row->tons;
        }

        $todayFile   = 'WO_' . $now->format('mdY') . '_(6AM).xlsx';
        $washRows    = DB::table('inventory_wip_snapshots')
            ->where('plant', $plant)
            ->where('daily_check_file', $todayFile)
            ->where('process', 'WASH')
            ->get(['area', 'tons', 'work_order_count'])
            ->map(fn($row) => (array) $row)
            ->values()
            ->toArray();

        return response()->json([
            'graph'        => $grouped,
            'wash_summary' => $washRows,
            'wash_date'    => $todayFile,
        ]);

    }

    public function getWIP(Request $request, string $plant)
    {
        $now  = now(config('app.timezone'));
        $file = 'WO_' . $now->format('mdY') . '_(6AM).xlsx';

        // Serve from snapshot if today's already exists and is fresh
        $snapshot = $this->getSnapshot($plant, $file);
        if ($snapshot !== null) {
            return response()->json($snapshot);
        }

        $cacheKey = 'inventory_wip:stale:' . md5($plant . $file);

        try {
            $query    = http_build_query([
                'daily_check_file' => $file,
                'plant'            => $this->externalPlantName($plant),
            ]);
            $response = Http::timeout(15)->get($this->apiBase . '?' . $query);

            if ($response->failed()) {
                throw new \Exception("External API error: {$response->status()}");
            }

            $rawData = $response->json();
        } catch (\Exception $e) {
            $stale = Cache::get($cacheKey);
            if ($stale) return response()->json($stale);

            return response()->json([
                'error'   => 'Query failed',
                'message' => $e->getMessage(),
            ], 500);
        }

        Cache::put($cacheKey, $rawData, now()->addHours(2));

        $grouped = $this->saveSnapshot($plant, $file, $rawData);

        return response()->json($grouped);
    }

    private function getSnapshot(string $plant, string $dailyCheckFile): ?array
    {
        $cacheKey = 'inventory_wip:snapshot:' . md5($plant . $dailyCheckFile);

        $exists = DB::table('inventory_wip_snapshots')
            ->where('plant', $plant)
            ->where('daily_check_file', $dailyCheckFile)
            ->exists();

        if (!$exists) return null;

        return Cache::rememberForever($cacheKey, function () use ($plant, $dailyCheckFile) {
            return DB::table('inventory_wip_snapshots')
                ->where('plant', $plant)
                ->where('daily_check_file', $dailyCheckFile)
                ->get(['process', 'area', 'tons', 'work_order_count', 'queried_at'])
                ->map(fn($row) => (array) $row)
                ->values()
                ->toArray();
        });
    }

    private function saveSnapshot(string $plant, string $dailyCheckFile, array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $process = $this->groupProcess($row['Process'] ?? '');
            if ($process === null) continue;

            $area = $row['Area']  ?? '';
            $tons = (float) ($row['Tons'] ?? 0);
            $key  = $process . '|' . $area;

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'plant'            => $plant,
                    'daily_check_file' => $dailyCheckFile,
                    'process'          => $process,
                    'area'             => $area,
                    'tons'             => 0.0,
                    'work_order_count' => 0,
                    'queried_at'       => now(config('app.timezone')),
                ];
            }

            $grouped[$key]['tons']            += $tons;
            $grouped[$key]['work_order_count'] += 1;
        }

        if (!empty($grouped)) {
            DB::table('inventory_wip_snapshots')->insertOrIgnore(array_values($grouped));

            $cacheKey = 'inventory_wip:snapshot:' . md5($plant . $dailyCheckFile);
            Cache::forget($cacheKey);
            Cache::rememberForever($cacheKey, function () use ($plant, $dailyCheckFile) {
                return DB::table('inventory_wip_snapshots')
                    ->where('plant', $plant)
                    ->where('daily_check_file', $dailyCheckFile)
                    ->get(['process', 'area', 'tons', 'work_order_count', 'queried_at'])
                    ->map(fn($row) => (array) $row)
                    ->values()
                    ->toArray();
            });
        }

        return array_values(array_map(fn($row) => [
            'process'          => $row['process'],
            'area'             => $row['area'],
            'tons'             => $row['tons'],
            'work_order_count' => $row['work_order_count'],
        ], $grouped));
    }

    private function fetchAvailableFiles(): array
    {
        try {
            $response = Http::timeout(15)->get($this->apiBase, ['init' => 'true']);
            if ($response->failed()) return [];
            return $response->json()['Daily_Check_file'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function backfillMissingDays(
        array  $missingFiles,
        array  $availableFiles,
        string $plant,
    ): void {
        foreach ($missingFiles as $file) {
            if (!in_array($file, $availableFiles)) continue;

            try {
                $query = http_build_query([
                    'daily_check_file' => $file,
                    'plant'            => $this->externalPlantName($plant),
                ]);

                $response = Http::timeout(15)->get($this->apiBase . '?' . $query);

                if ($response->failed()) continue;

                $rawData = $response->json();
                if (empty($rawData) || !is_array($rawData)) continue;

                $this->saveSnapshot($plant, $file, $rawData);

            } catch (\Exception $e) {
                Log::warning("WIP backfill failed for {$file}: {$e->getMessage()}");
                continue;
            }
        }
    }

    private function groupProcess(string $process): ?string
    {
        $upper = strtoupper(trim($process));

        if ($upper === '-' || $upper === '')                    return null;
        if (str_contains($upper, 'INSPECTION'))                 return null;
        if (str_contains($upper, 'PREHEAT'))                    return null;
        if (str_contains($upper, 'ARRANGE'))                    return null;
        if (str_contains($upper, 'PAINTING '))                  return 'PAINT PREP';
        if (str_contains($upper, 'WASH'))                       return 'WASH';
        if (str_contains($upper, 'SHAPE GRINDING'))             return 'SG';
        if (str_starts_with($upper, 'RM PAINTING'))             return 'RM PAINT';
        if (str_starts_with($upper, 'RM RECEIVING'))            return 'RM REC';
        if (str_contains($upper, 'BAR'))                        return 'BAR';
        if (str_contains($upper, 'CHAMFER'))                    return 'CHAMFER';
        if (str_contains($upper, 'OUTER-R'))                    return 'OG';
        if (str_contains($upper, 'SLICING'))                    return 'SL';
        if (str_contains($upper, 'HDDG'))                       return 'HD';
        if (str_contains($upper, 'CGH'))                        return 'CGH';

        $base = trim(preg_replace('/\([^)]*\)/', '', $process));

        if (str_contains($base, '-')) {
            return trim(explode('-', $base)[0]);
        }

        return $base;
    }
}
