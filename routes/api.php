<?php

use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\StateController;
use App\Http\Controllers\Api\CityController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Countries
    Route::get('/countries', [CountryController::class, 'index']);
    Route::get('/countries/{country}', [CountryController::class, 'show']);
    Route::get('/countries/{country}/states', [CountryController::class, 'states']);

    // States
    Route::get('/states', [StateController::class, 'index']);
    Route::get('/states/{state}', [StateController::class, 'show']);

    // Cities (New)
    Route::get('/cities', [CityController::class, 'index']);
    Route::get('/cities/{city}', [CityController::class, 'show']);
});