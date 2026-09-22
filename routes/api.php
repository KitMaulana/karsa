<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\HotspotController;
use App\Http\Controllers\Api\OpenDataController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RiskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API internal KARSA -- CLAUDE.md §15, prefix /api/v1, rate limit 60/menit/IP
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    Route::get('/regions', [RegionController::class, 'index']);
    Route::get('/regions/geojson', [RegionController::class, 'geojson']);
    Route::get('/risk/{district:slug}', [RiskController::class, 'show']);
    Route::get('/hotspots', [HotspotController::class, 'index']);
    Route::get('/alerts/active', [AlertController::class, 'active']);
    Route::get('/recommendations', [RecommendationController::class, 'index']);
    Route::get('/open/reports.geojson', [OpenDataController::class, 'reports']);

    Route::post('/reports', [ReportController::class, 'store'])->middleware('auth:sanctum,web');
});
