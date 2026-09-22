<?php

use App\Http\Controllers\AiWorkspaceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('ai-chat/workspace')
    ->name('ai.workspace.')
    ->group(function (): void {
        Route::get('/bootstrap', [AiWorkspaceController::class, 'bootstrap'])->name('bootstrap');
        Route::post('/conversation/sync', [AiWorkspaceController::class, 'syncConversation'])->name('conversation.sync');
        Route::post('/conversation/delete', [AiWorkspaceController::class, 'deleteConversation'])->name('conversation.delete');

        Route::post('/project/save', [AiWorkspaceController::class, 'saveProject'])->name('project.save');
        Route::post('/project/delete', [AiWorkspaceController::class, 'deleteProject'])->name('project.delete');

        Route::get('/documents', [AiWorkspaceController::class, 'listDocuments'])->name('documents');
        Route::post('/document/upload', [AiWorkspaceController::class, 'uploadDocument'])->name('document.upload');
        Route::post('/document/delete', [AiWorkspaceController::class, 'deleteDocument'])->name('document.delete');
        Route::get('/document/{document}/download', [AiWorkspaceController::class, 'downloadDocument'])->name('document.download');

        Route::post('/memory/save', [AiWorkspaceController::class, 'saveMemory'])->name('memory.save');
        Route::post('/memory/delete', [AiWorkspaceController::class, 'deleteMemory'])->name('memory.delete');

        Route::get('/search', [AiWorkspaceController::class, 'search'])->name('search');
        Route::post('/share', [AiWorkspaceController::class, 'share'])->name('share');
        Route::get('/export/{conversation}', [AiWorkspaceController::class, 'export'])->name('export');
    });

Route::get(
    '/ai-chat/shared/{token}',
    [AiWorkspaceController::class, 'shared']
)->name('ai.workspace.shared');
