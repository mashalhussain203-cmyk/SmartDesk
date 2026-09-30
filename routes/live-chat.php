<?php

use App\Http\Controllers\AdminLiveChatController;
use App\Http\Controllers\LiveChatController;
use Illuminate\Support\Facades\Route;

Route::prefix('live-chat')->name('live-chat.')->group(function (): void {
    Route::get('/', [LiveChatController::class, 'show'])
        ->middleware('throttle:90,1,visitor-live-chat-show')
        ->block(10, 10)
        ->name('show');

    Route::post('/messages', [LiveChatController::class, 'store'])
        ->middleware('throttle:20,1,visitor-live-chat-store')
        ->block(10, 10)
        ->name('store');

    Route::post('/typing', [LiveChatController::class, 'typing'])
        ->middleware('throttle:120,1,visitor-live-chat-typing')
        ->name('typing');

    Route::delete('/messages/{message}', [LiveChatController::class, 'destroy'])
        ->whereNumber('message')
        ->middleware('throttle:20,1,visitor-live-chat-destroy')
        ->name('destroy');

    Route::post('/reopen', [LiveChatController::class, 'reopen'])
        ->middleware('throttle:5,1,visitor-live-chat-reopen')
        ->block(10, 10)
        ->name('reopen');
});


Route::middleware('auth')
    ->prefix('admin/live-chat')
    ->name('admin.live-chat.')
    ->group(function (): void {
        Route::get('/', [AdminLiveChatController::class, 'index'])
            ->name('index');

        Route::get('/conversations', [AdminLiveChatController::class, 'conversations'])
            ->middleware('throttle:90,1,admin-live-chat-conversations')
            ->name('conversations');

        Route::post('/presence', [AdminLiveChatController::class, 'presence'])
            ->middleware('throttle:30,1,admin-live-chat-presence')
            ->name('presence');


        Route::post('/email-sync', [AdminLiveChatController::class, 'emailSync'])
            ->middleware('throttle:10,1,admin-live-chat-email-sync')
            ->name('email-sync');

        Route::get('/conversations/{conversation}', [AdminLiveChatController::class, 'show'])
            ->whereNumber('conversation')
            ->middleware('throttle:90,1,admin-live-chat-show')
            ->name('show');

        Route::post('/conversations/{conversation}/messages', [AdminLiveChatController::class, 'store'])
            ->whereNumber('conversation')
            ->middleware('throttle:30,1,admin-live-chat-store')
            ->name('store');

        Route::post('/conversations/{conversation}/typing', [AdminLiveChatController::class, 'typing'])
            ->whereNumber('conversation')
            ->middleware('throttle:120,1,admin-live-chat-typing')
            ->name('typing');

        Route::post('/conversations/{conversation}/email-handoff', [AdminLiveChatController::class, 'emailHandoff'])
            ->whereNumber('conversation')
            ->middleware('throttle:20,1,admin-live-chat-email-handoff')
            ->name('email-handoff');

        Route::delete('/conversations/{conversation}/messages/{message}', [AdminLiveChatController::class, 'destroy'])
            ->whereNumber('conversation')
            ->whereNumber('message')
            ->middleware('throttle:30,1,admin-live-chat-destroy')
            ->name('destroy');

        Route::patch('/conversations/{conversation}', [AdminLiveChatController::class, 'update'])
            ->whereNumber('conversation')
            ->middleware('throttle:30,1,admin-live-chat-update')
            ->name('update');
    });
