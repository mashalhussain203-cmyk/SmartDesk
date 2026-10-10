<?php

use App\Http\Controllers\LiveRecordingController;
use App\Http\Controllers\LiveRecordingUploadController;
use Illuminate\Support\Facades\Route;

/*
 * Private SmartDesk live recordings. Every endpoint is behind the existing
 * authenticated session and the controller checks the user's is_admin flag.
 * Never serve recording files through the public storage symlink.
 */
Route::middleware('auth')->prefix('live')->group(function () {
    Route::get('/', [LiveRecordingController::class, 'index'])->name('live.index');
    Route::get('/status', [LiveRecordingController::class, 'status'])->name('live.status');

    // Admin-only chunked upload; avoids the small default PHP multipart limit.
    Route::post('/uploads', [LiveRecordingUploadController::class, 'begin'])
        ->middleware('throttle:10,1')->name('live.upload.begin');
    Route::put('/uploads/{upload}/parts/{index}', [LiveRecordingUploadController::class, 'part'])
        ->where(['upload' => '[0-9a-f-]{36}', 'index' => '[0-9]+'])
        ->middleware('throttle:240,1')->name('live.upload.part');
    Route::post('/uploads/{upload}/complete', [LiveRecordingUploadController::class, 'complete'])
        ->where('upload', '[0-9a-f-]{36}')
        ->middleware('throttle:10,1')->name('live.upload.complete');
    Route::delete('/uploads/{upload}', [LiveRecordingUploadController::class, 'cancel'])
        ->where('upload', '[0-9a-f-]{36}')
        ->middleware('throttle:20,1')->name('live.upload.cancel');

    Route::get('/{account}/{filename}/watch', [LiveRecordingController::class, 'play'])
        ->where(['account' => 'knock1knock|emyii|cutefacebigass|ricasashaa|julesxdann|dellris', 'filename' => '[A-Za-z0-9_.-]+\.mp4'])
        ->name('live.play');

    Route::get('/{account}/{filename}/download', [LiveRecordingController::class, 'download'])
        ->where(['account' => 'knock1knock|emyii|cutefacebigass|ricasashaa|julesxdann|dellris', 'filename' => '[A-Za-z0-9_.-]+\.mp4'])
        ->name('live.download');

    Route::delete('/{account}/{filename}', [LiveRecordingController::class, 'destroy'])
        ->where(['account' => 'knock1knock|emyii|cutefacebigass|ricasashaa|julesxdann|dellris', 'filename' => '[A-Za-z0-9_.-]+\.mp4'])
        ->name('live.destroy');
});
