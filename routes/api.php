<?php

use App\Http\Controllers\InventoryWipController;
use App\Http\Controllers\IpAddressController;
use App\Http\Controllers\PlantOutputController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/ip-details', [IpAddressController::class, 'getIpDetails']);

Route::get('/plant-output/{plant}', [PlantOutputController::class, 'index']);
Route::get('/plant-target/{plant}', [PlantOutputController::class, 'targets']);
Route::post('/plant-target/{plant}', [PlantOutputController::class, 'storeTargets']);
Route::get('/plant-daily-target/{plant}',  [PlantOutputController::class, 'dailyTargets']);
Route::post('/plant-daily-target/{plant}', [PlantOutputController::class, 'storeDailyTargets']);

Route::get('/inventory-wip/init',          [InventoryWipController::class, 'init']);
Route::get('/inventory-wip/graph/{plant}',  [InventoryWipController::class, 'graph']);
Route::get('/inventory-wip/getWIP/{plant}', [InventoryWipController::class, 'getWIP']);

Route::get('/inventory-wip/debug/{plant}', function (\Illuminate\Http\Request $request, string $plant) {
    $now   = now();
    $year  = $now->year;
    $month = $now->month;

    $expectedFiles = [];
    for ($day = 1; $day <= $now->day; $day++) {
        $date            = \Carbon\Carbon::create($year, $month, $day);
        $formatted       = $date->format('m') . $date->format('d') . $date->format('Y');
        $expectedFiles[] = "WO_{$formatted}_(6AM).xlsx";
    }

    $snapshotFiles = \Illuminate\Support\Facades\DB::table('inventory_wip_snapshots')
        ->where('daily_check_file', 'like', '%6AM%')
        ->whereYear('queried_at', $year)
        ->whereMonth('queried_at', $month)
        ->pluck('daily_check_file')
        ->unique()
        ->values()
        ->toArray();

    $availableFiles = \Illuminate\Support\Facades\Http::timeout(15)
        ->get('http://172.17.2.236/inventory/api/inventory_wip.php', ['init' => 'true'])
        ->json()['Daily_Check_file'] ?? [];

    $available6am = array_filter($availableFiles, fn($f) => str_contains($f, '6AM'));

    return response()->json([
        'today'           => $now->toDateString(),
        'expected_files'  => $expectedFiles,
        'snapshot_files'  => $snapshotFiles,
        'missing'         => array_values(array_diff($expectedFiles, $snapshotFiles)),
        'available_6am'   => array_values($available6am),
        'missing_and_available' => array_values(
            array_intersect(
                array_diff($expectedFiles, $snapshotFiles),
                $availableFiles
            )
        ),
    ]);
});
