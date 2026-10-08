<?php

use App\Http\Controllers\AdminLiveChatController;
use App\Http\Controllers\AdminLiveChatCallController;
use App\Http\Controllers\AdminLiveChatFeaturesController;
use App\Http\Controllers\GmailLiveChatOAuthController;
use App\Http\Controllers\LiveChatController;
use App\Http\Controllers\LiveChatCallController;
use Illuminate\Support\Facades\Route;

Route::prefix('live-chat')->name('live-chat.')->group(function (): void {
    Route::get('/calls/config', [LiveChatCallController::class, 'config'])
        ->middleware('throttle:30,1,visitor-live-chat-call-config')
        ->name('calls.config');

    Route::get('/calls/current', [LiveChatCallController::class, 'current'])
        ->middleware('throttle:180,1,visitor-live-chat-call-current')
        ->name('calls.current');

    Route::post('/calls', [LiveChatCallController::class, 'start'])
        ->middleware('throttle:20,1,visitor-live-chat-call-start')
        ->name('calls.start');

    Route::post('/calls/{call}/answer', [LiveChatCallController::class, 'answer'])
        ->whereUuid('call')
        ->middleware('throttle:30,1,visitor-live-chat-call-answer')
        ->name('calls.answer');

    Route::post('/calls/{call}/decline', [LiveChatCallController::class, 'decline'])
        ->whereUuid('call')
        ->middleware('throttle:30,1,visitor-live-chat-call-decline')
        ->name('calls.decline');

    Route::post('/calls/{call}/video', [LiveChatCallController::class, 'videoUpgrade'])
        ->whereUuid('call')
        ->middleware('throttle:15,1,visitor-live-chat-call-video')
        ->name('calls.video-upgrade');

    Route::post('/calls/{call}/end', [LiveChatCallController::class, 'end'])
        ->whereUuid('call')
        ->middleware('throttle:60,1,visitor-live-chat-call-end')
        ->name('calls.end');

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

    Route::post('/typing', [LiveChatController::class, 'typing'])
        ->middleware('throttle:120,1,visitor-live-chat-typing')
        ->name('typing');

    Route::patch('/messages/{message}', [LiveChatController::class, 'editMessage'])
        ->whereNumber('message')
        ->middleware('throttle:20,1,visitor-live-chat-edit')
        ->name('edit-message');

    Route::post('/messages/{message}/reaction', [LiveChatController::class, 'react'])
        ->whereNumber('message')
        ->middleware('throttle:60,1,visitor-live-chat-reaction')
        ->name('reaction');

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
        Route::get('/calls/incoming', [AdminLiveChatCallController::class, 'incoming'])
            ->middleware('throttle:180,1,admin-live-chat-call-incoming')
            ->name('calls.incoming');

        Route::get('/conversations/{conversation}/calls/current', [AdminLiveChatCallController::class, 'current'])
            ->whereNumber('conversation')
            ->middleware('throttle:180,1,admin-live-chat-call-current')
            ->name('calls.current');

        Route::post('/conversations/{conversation}/calls', [AdminLiveChatCallController::class, 'start'])
            ->whereNumber('conversation')
            ->middleware('throttle:20,1,admin-live-chat-call-start')
            ->name('calls.start');

        Route::post('/conversations/{conversation}/calls/{call}/answer', [AdminLiveChatCallController::class, 'answer'])
            ->whereNumber('conversation')
            ->whereUuid('call')
            ->middleware('throttle:30,1,admin-live-chat-call-answer')
            ->name('calls.answer');

        Route::post('/conversations/{conversation}/calls/{call}/decline', [AdminLiveChatCallController::class, 'decline'])
            ->whereNumber('conversation')
            ->whereUuid('call')
            ->middleware('throttle:30,1,admin-live-chat-call-decline')
            ->name('calls.decline');

        Route::post('/conversations/{conversation}/calls/{call}/video', [AdminLiveChatCallController::class, 'videoUpgrade'])
            ->whereNumber('conversation')
            ->whereUuid('call')
            ->middleware('throttle:15,1,admin-live-chat-call-video')
            ->name('calls.video-upgrade');

        Route::post('/conversations/{conversation}/calls/{call}/end', [AdminLiveChatCallController::class, 'end'])
            ->whereNumber('conversation')
            ->whereUuid('call')
            ->middleware('throttle:60,1,admin-live-chat-call-end')
            ->name('calls.end');

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

        Route::get('/stats', [AdminLiveChatFeaturesController::class, 'stats'])
            ->middleware('throttle:60,1,admin-live-chat-stats')
            ->name('stats');

        Route::patch('/conversations/{conversation}/meta', [AdminLiveChatFeaturesController::class, 'updateMeta'])
            ->whereNumber('conversation')->name('meta');
        Route::get('/conversations/{conversation}/notes', [AdminLiveChatFeaturesController::class, 'notes'])
            ->whereNumber('conversation')->name('notes');
        Route::post('/conversations/{conversation}/notes', [AdminLiveChatFeaturesController::class, 'storeNote'])
            ->whereNumber('conversation')->name('notes.store');
        Route::delete('/conversations/{conversation}/notes/{note}', [AdminLiveChatFeaturesController::class, 'deleteNote'])
            ->whereNumber('conversation')->whereNumber('note')->name('notes.delete');
        Route::post('/conversations/{conversation}/messages/{message}/reaction', [AdminLiveChatFeaturesController::class, 'react'])
            ->whereNumber('conversation')->whereNumber('message')->name('reaction');
        Route::patch('/conversations/{conversation}/messages/{message}', [AdminLiveChatFeaturesController::class, 'editMessage'])
            ->whereNumber('conversation')->whereNumber('message')->name('edit-message');
        Route::post('/conversations/{conversation}/block', [AdminLiveChatFeaturesController::class, 'block'])
            ->whereNumber('conversation')->name('block');
        Route::delete('/conversations/{conversation}/block', [AdminLiveChatFeaturesController::class, 'unblock'])
            ->whereNumber('conversation')->name('unblock');
        Route::get('/conversations/{conversation}/history', [AdminLiveChatFeaturesController::class, 'history'])
            ->whereNumber('conversation')->name('history');
        Route::get('/conversations/{conversation}/media', [AdminLiveChatFeaturesController::class, 'media'])
            ->whereNumber('conversation')->name('media');
        Route::get('/conversations/{conversation}/profile', [AdminLiveChatFeaturesController::class, 'profile'])
            ->whereNumber('conversation')->name('profile');
        Route::get('/conversations/{conversation}/transcript', [AdminLiveChatFeaturesController::class, 'transcript'])
            ->whereNumber('conversation')->name('transcript');
        Route::get('/conversations/{conversation}/export.zip', [AdminLiveChatFeaturesController::class, 'exportZip'])
            ->whereNumber('conversation')->name('export-zip');

        Route::patch('/conversations/{conversation}', [AdminLiveChatController::class, 'update'])
            ->whereNumber('conversation')
            ->middleware('throttle:30,1,admin-live-chat-update')
            ->name('update');
    });
