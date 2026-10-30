<?php

use App\Http\Controllers\LiveRecorderIngestController;
use Illuminate\Support\Facades\Route;

/**
 * Machine-to-machine ingest only; these routes never return recordings.
 * Authentication is enforced by the controller with a 32+ character bearer
 * secret. Do not attach Laravel's cache-backed throttle middleware here:
 * recorder status must keep working while MySQL/cache is unavailable.
 */
Route::prefix('internal/live-recordings')->group(function (): void {
    Route::post('/{account}/status', [LiveRecorderIngestController::class, 'status']);
    Route::post('/{account}/uploads', [LiveRecorderIngestController::class, 'begin']);
    Route::put('/{account}/uploads/{upload}/parts/{index}', [LiveRecorderIngestController::class, 'part'])
        ->where(['upload' => '[0-9a-f-]{36}', 'index' => '[0-9]+']);
    Route::post('/{account}/uploads/{upload}/complete', [LiveRecorderIngestController::class, 'complete'])
        ->where('upload', '[0-9a-f-]{36}');
});
