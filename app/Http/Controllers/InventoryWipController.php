<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class InventoryWipController extends Controller
{
    private string $apiBase = 'http://172.17.2.236/inventory/api/inventory_wip.php';

    public function init()
    {
        $cacheKey = 'inventory_wip:init';

        $data = Cache::remember($cacheKey, now()->addHour(), function () {
            try {
                $response = Http::timeout(15)->get($this->apiBase, [
                    'init' => 'true',
                ]);

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

        // Keep a stale copy in case the external API goes down
        Cache::put("{$cacheKey}:stale", $data, now()->addDay());

        return response()->json($data);
    }

    public function getWIP(Request $request)
    {
        $request->validate([
            'daily_check_file' => 'required|string',
            'areas'            => 'required|array|min:1',
            'areas.*'          => 'string',
        ]);

        $dailyCheckFile = $request->input('daily_check_file');
        $areas          = $request->input('areas');

        // Sort areas so ["A","B"] and ["B","A"] produce the same cache key
        $sortedAreas = $areas;
        sort($sortedAreas);
        $areasHash = md5(implode(',', $sortedAreas));
        $cacheKey  = 'inventory_wip:query:' . md5($dailyCheckFile) . ':' . $areasHash;

        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($dailyCheckFile, $areas, $cacheKey) {
            try {
                $response = Http::timeout(15)->get($this->apiBase, [
                    'daily_check_file' => $dailyCheckFile,
                    'areas'            => $areas,
                ]);

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

        // Keep a stale copy for fallback
        Cache::put("{$cacheKey}:stale", $data, now()->addHours(2));

        return response()->json($data);
    }
}
