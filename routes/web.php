<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');
Route::get('/ProcessTime', function () {
    return Inertia::render('ProcessTime');
})->name('ProcessTime');

Route::get('/scan/single', function () {
    return Inertia::render('Scan/Single');
})->name('scan.single');
Route::get('/scan/multiple', function () {
    return Inertia::render('Scan/Multiple');
})->name('scan.multiple');
Route::get('/scan/route', function () {
    return Inertia::render('Scan/Route');
})->name('scan.route');


Route::get('/summary/inventory/main', function () {
    return Inertia::render('Summary/Inventory/Main');
})->name('summary.inventory.main');
Route::get('/summary/inventory/plant7', function () {
    return Inertia::render('Summary/Inventory/Plant7');
})->name('summary.inventory.plant7');
Route::get('/summary/inventory/plant8-1st', function () {
    return Inertia::render('Summary/Inventory/Plant8-1st');
})->name('summary.inventory.plant8-1st');
Route::get('/summary/inventory/plant8-2nd', function () {
    return Inertia::render('Summary/Inventory/Plant8-2nd');
})->name('summary.inventory.plant8-2nd');

Route::get('/summary/tons/main', function () {
    return Inertia::render('Summary/Tons/Main');
})->name('summary.tons.main');
Route::get('/summary/tons/plant7', function () {
    return Inertia::render('Summary/Tons/Plant7');
})->name('summary.tons.plant7');
Route::get('/summary/tons/plant8-1st', function () {
    return Inertia::render('Summary/Tons/Plant8-1st');
})->name('summary.tons.plant8-1st');
Route::get('/summary/tons/plant8-2nd', function () {
    return Inertia::render('Summary/Tons/Plant8-2nd');
})->name('summary.tons.plant8-2nd');


Route::get('/summary/pieces/main', function () {
    return Inertia::render('Summary/Pieces/Main');
})->name('summary.pieces.main');
Route::get('/summary/pieces/plant7', function () {
    return Inertia::render('Summary/Pieces/Plant7');
})->name('summary.pieces.plant7');
Route::get('/summary/pieces/plant8-1st', function () {
    return Inertia::render('Summary/Pieces/Plant8-1st');
})->name('summary.pieces.plant8-1st');
Route::get('/summary/pieces/plant8-2nd', function () {
    return Inertia::render('Summary/Pieces/Plant8-2nd');
})->name('summary.pieces.plant8-2nd');
Route::get('/summary/pieces/ncp2', function () {
    return Inertia::render('Summary/Pieces/NCP2');
})->name('summary.pieces.ncp2');
Route::get('/summary/pieces/ncp3', function () {
    return Inertia::render('Summary/Pieces/NCP3');
})->name('summary.pieces.ncp3');
Route::get('/summary/pieces/epoxy', function () {
    return Inertia::render('Summary/Pieces/EPOXY');
})->name('summary.pieces.epoxy');
Route::get('/summary/pieces/inorganic', function () {
    return Inertia::render('Summary/Pieces/INORGANIC');
})->name('summary.pieces.inorganic');
Route::get('/summary/pieces/chiptype', function () {
    return Inertia::render('Summary/Pieces/CHIPTYPE');
})->name('summary.pieces.chiptype');
Route::get('/summary/pieces/ncp8', function () {
    return Inertia::render('Summary/Pieces/NCP8');
})->name('summary.pieces.ncp8');
Route::get('/summary/pieces/p10epoxy', function () {
    return Inertia::render('Summary/Pieces/P10EPOXY');
})->name('summary.pieces.p10epoxy');
Route::get('/summary/pieces/vcm', function () {
    return Inertia::render('Summary/Pieces/VCM');
})->name('summary.pieces.vcm');




require __DIR__.'/settings.php';
