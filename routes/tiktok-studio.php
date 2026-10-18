<?php

use App\Http\Controllers\TikTokStudioController;
use Illuminate\Support\Facades\Route;

Route::get('/tools/tiktok-upload-studio', [TikTokStudioController::class, 'index'])
    ->name('tiktok-studio.index');

Route::middleware('auth')->group(function (): void {
    Route::post('/tools/tiktok-upload-studio/connect', [TikTokStudioController::class, 'connect'])
        ->middleware('throttle:5,1')
        ->name('tiktok-studio.connect');

    Route::get('/tools/tiktok-upload-studio/callback', [TikTokStudioController::class, 'callback'])
        ->middleware('throttle:15,1')
        ->name('tiktok-studio.callback');

    Route::post('/tools/tiktok-upload-studio/disconnect', [TikTokStudioController::class, 'disconnect'])
        ->middleware('throttle:5,1')
        ->name('tiktok-studio.disconnect');

    Route::post('/tools/tiktok-upload-studio/upload', [TikTokStudioController::class, 'upload'])
        ->middleware('throttle:5,60')
        ->name('tiktok-studio.upload');

    Route::post('/tools/tiktok-upload-studio/status', [TikTokStudioController::class, 'status'])
        ->middleware('throttle:20,1')
        ->name('tiktok-studio.status');
});
