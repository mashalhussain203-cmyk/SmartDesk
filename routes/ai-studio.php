<?php

use App\Http\Controllers\AiStudioController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('ai-studio')
    ->name('ai.studio.')
    ->group(function (): void {
        Route::get(
            '/',
            [AiStudioController::class, 'index']
        )->name('dashboard');

        Route::get(
            '/status',
            [AiStudioController::class, 'status']
        )->name('status');
    });
