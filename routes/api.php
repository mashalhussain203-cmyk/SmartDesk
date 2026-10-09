<?php

use App\Http\Controllers\LiveRecorderIngestController;
use Illuminate\Support\Facades\Route;

/** Machine-to-machine ingest only; these routes never return recordings. */
Route::prefix('internal/live-recordings')->middleware('throttle:240,1')->group(function (): void {
    Route::post('/{account}/status', [LiveRecorderIngestController::class, 'status']);
    Route::post('/{account}/uploads', [LiveRecorderIngestController::class, 'begin']);
    Route::put('/{account}/uploads/{upload}/parts/{index}', [LiveRecorderIngestController::class, 'part'])
        ->where(['upload' => '[0-9a-f-]{36}', 'index' => '[0-9]+']);
    Route::post('/{account}/uploads/{upload}/complete', [LiveRecorderIngestController::class, 'complete'])
        ->where('upload', '[0-9a-f-]{36}');
});
