<?php

use App\Http\Controllers\LiveRecordingController;
use Illuminate\Support\Facades\Route;

/*
 * Private SmartDesk live recordings. Every endpoint is behind the existing
 * authenticated session and the controller checks the user's is_admin flag.
 * Never serve recording files through the public storage symlink.
 */
Route::middleware('auth')->prefix('live')->group(function () {
    Route::get('/', [LiveRecordingController::class, 'index'])->name('live.index');
    Route::get('/status', [LiveRecordingController::class, 'status'])->name('live.status');

    Route::get('/{account}/{filename}/watch', [LiveRecordingController::class, 'play'])
        ->where(['account' => 'knock1knock|emyii', 'filename' => '[A-Za-z0-9_.-]+\.mp4'])
        ->name('live.play');

    Route::get('/{account}/{filename}/download', [LiveRecordingController::class, 'download'])
        ->where(['account' => 'knock1knock|emyii', 'filename' => '[A-Za-z0-9_.-]+\.mp4'])
        ->name('live.download');

    Route::delete('/{account}/{filename}', [LiveRecordingController::class, 'destroy'])
        ->where(['account' => 'knock1knock|emyii', 'filename' => '[A-Za-z0-9_.-]+\.mp4'])
        ->name('live.destroy');
});
