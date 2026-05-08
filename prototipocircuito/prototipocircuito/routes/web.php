<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/map', [MapController::class, 'show'])->name('map.show');

// Map Editor routes
Route::get('/map/editor', [MapController::class, 'editor'])->name('map.editor');
Route::post('/map/save', [MapController::class, 'saveMap'])->name('map.save');
