<?php

use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\StateController;
use Illuminate\Support\Facades\Route;

// API Version 1
Route::prefix('v1')->group(function () {

    // Countries Routes
    Route::get('/countries', [CountryController::class, 'index']);
    Route::get('/countries/{country}', [CountryController::class, 'show']);
    Route::get('/countries/{country}/states', [CountryController::class, 'states']);

    // States Routes
    Route::get('/states', [StateController::class, 'index']);
    Route::get('/states/{state}', [StateController::class, 'show']);
});