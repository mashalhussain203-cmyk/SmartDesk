<?php

use App\Http\Controllers\TikTokCounterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| TikTok Live Count
|--------------------------------------------------------------------------
*/

Route::get(
    '/tools/tiktok-counter',
    [TikTokCounterController::class, 'index']
)->name('tiktok-counter.index');

Route::post(
    '/tools/tiktok-counter',
    [TikTokCounterController::class, 'lookup']
)
    ->middleware('throttle:20,1')
    ->name('tiktok-counter.lookup');

Route::get(
    '/tools/tiktok-counter/{videoId}',
    [TikTokCounterController::class, 'show']
)
    ->whereNumber('videoId')
    ->name('tiktok-counter.show');

Route::get(
    '/api/tools/tiktok-counter/{videoId}',
    [TikTokCounterController::class, 'stats']
)
    ->whereNumber('videoId')
    ->middleware('throttle:180,1')
    ->name('tiktok-counter.stats');
