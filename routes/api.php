<?php

use App\Http\Controllers\InventoryWipController;
use App\Http\Controllers\IpAddressController;
use App\Http\Controllers\PlantOutputController;
use App\Http\Controllers\HomeController;
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

Route::get('/home-dashboard', [HomeController::class, 'index']);
