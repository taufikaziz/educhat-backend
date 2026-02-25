<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\DocumentController;
use App\Http\Controllers\API\ChatController;

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'rag_service' => app(\App\Services\RAGService::class)->healthCheck() ? 'connected' : 'disconnected'
    ]);
});

Route::prefix('documents')->group(function () {
    Route::post('/upload', [DocumentController::class, 'upload']);
    Route::get('/status/{sessionId}', [DocumentController::class, 'status']);
    Route::get('/file/{sessionId}', [DocumentController::class, 'file']);
});

Route::prefix('chat')->group(function () {
    Route::post('/query', [ChatController::class, 'query']);
    Route::post('/summary', [ChatController::class, 'summary']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('documents')->group(function () {
        Route::get('/', [DocumentController::class, 'index']);
        Route::delete('/{sessionId}', [DocumentController::class, 'destroy']);
    });

    Route::prefix('chat')->group(function () {
        Route::post('/session', [ChatController::class, 'createSession']);
        Route::get('/messages/{sessionId}', [ChatController::class, 'messages']);
        Route::get('/sessions', [ChatController::class, 'index']);
        Route::delete('/{sessionId}', [ChatController::class, 'destroy']);
    });
});
