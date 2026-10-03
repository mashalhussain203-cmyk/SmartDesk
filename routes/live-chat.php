<?php

use App\Http\Controllers\AdminLiveChatController;
use App\Http\Controllers\GmailLiveChatOAuthController;
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


    Route::post('/uploads/start', [LiveChatController::class, 'uploadStart'])
        ->middleware('throttle:30,1,visitor-live-chat-upload-start')
        ->name('upload-start');

    Route::get('/uploads/{upload}', [LiveChatController::class, 'uploadStatus'])
        ->whereUuid('upload')
        ->middleware('throttle:120,1,visitor-live-chat-upload-status')
        ->name('upload-status');

    Route::post('/uploads/{upload}/chunks/{index}', [LiveChatController::class, 'uploadChunk'])
        ->whereUuid('upload')
        ->whereNumber('index')
        ->middleware('throttle:300,1,visitor-live-chat-upload-chunk')
        ->name('upload-chunk');

    Route::post('/uploads/{upload}/complete', [LiveChatController::class, 'uploadComplete'])
        ->whereUuid('upload')
        ->middleware('throttle:30,1,visitor-live-chat-upload-complete')
        ->name('upload-complete');

    Route::delete('/uploads/{upload}', [LiveChatController::class, 'uploadCancel'])
        ->whereUuid('upload')
        ->middleware('throttle:30,1,visitor-live-chat-upload-cancel')
        ->name('upload-cancel');

    Route::get('/messages/{message}/attachment', [LiveChatController::class, 'attachment'])
        ->whereNumber('message')
        ->middleware('throttle:180,1,visitor-live-chat-attachment')
        ->name('attachment');

    Route::get('/email/messages/{message}/attachment', [LiveChatController::class, 'emailAttachment'])
        ->whereNumber('message')
        ->middleware(['signed', 'throttle:180,1,live-chat-email-attachment'])
        ->name('email-attachment');

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

        Route::get('/gmail/connect', [GmailLiveChatOAuthController::class, 'connect'])
            ->middleware('throttle:10,1,admin-live-chat-gmail-connect')
            ->name('gmail.connect');

        Route::get('/gmail/callback', [GmailLiveChatOAuthController::class, 'callback'])
            ->middleware('throttle:20,1,admin-live-chat-gmail-callback')
            ->name('gmail.callback');

        Route::get('/conversations/{conversation}', [AdminLiveChatController::class, 'show'])
            ->whereNumber('conversation')
            ->middleware('throttle:90,1,admin-live-chat-show')
            ->name('show');

        Route::post('/conversations/{conversation}/messages', [AdminLiveChatController::class, 'store'])
            ->whereNumber('conversation')
            ->middleware('throttle:30,1,admin-live-chat-store')
            ->name('store');


        Route::post('/conversations/{conversation}/uploads/start', [AdminLiveChatController::class, 'uploadStart'])
            ->whereNumber('conversation')
            ->middleware('throttle:30,1,admin-live-chat-upload-start')
            ->name('upload-start');

        Route::get('/conversations/{conversation}/uploads/{upload}', [AdminLiveChatController::class, 'uploadStatus'])
            ->whereNumber('conversation')
            ->whereUuid('upload')
            ->middleware('throttle:120,1,admin-live-chat-upload-status')
            ->name('upload-status');

        Route::post('/conversations/{conversation}/uploads/{upload}/chunks/{index}', [AdminLiveChatController::class, 'uploadChunk'])
            ->whereNumber('conversation')
            ->whereUuid('upload')
            ->whereNumber('index')
            ->middleware('throttle:300,1,admin-live-chat-upload-chunk')
            ->name('upload-chunk');

        Route::post('/conversations/{conversation}/uploads/{upload}/complete', [AdminLiveChatController::class, 'uploadComplete'])
            ->whereNumber('conversation')
            ->whereUuid('upload')
            ->middleware('throttle:30,1,admin-live-chat-upload-complete')
            ->name('upload-complete');

        Route::delete('/conversations/{conversation}/uploads/{upload}', [AdminLiveChatController::class, 'uploadCancel'])
            ->whereNumber('conversation')
            ->whereUuid('upload')
            ->middleware('throttle:30,1,admin-live-chat-upload-cancel')
            ->name('upload-cancel');

        Route::get('/conversations/{conversation}/messages/{message}/attachment', [AdminLiveChatController::class, 'attachment'])
            ->whereNumber('conversation')
            ->whereNumber('message')
            ->middleware('throttle:180,1,admin-live-chat-attachment')
            ->name('attachment');

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
