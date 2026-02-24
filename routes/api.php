<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\DocumentController;
use App\Http\Controllers\API\ChatController;

/*
|--------------------------------------------------------------------------
| API Auth Strategy
|--------------------------------------------------------------------------
| Set API_REQUIRE_AUTH=true to enforce auth:sanctum on app endpoints.
| Health endpoint remains public.
*/
$apiAuthMiddleware = env('API_REQUIRE_AUTH', false) ? ['auth:sanctum'] : [];

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'rag_service' => app(\App\Services\RAGService::class)->healthCheck() ? 'connected' : 'disconnected'
    ]);
});

Route::middleware($apiAuthMiddleware)->group(function () {
    Route::prefix('documents')->group(function () {
        Route::post('/upload', [DocumentController::class, 'upload']);
        Route::get('/status/{sessionId}', [DocumentController::class, 'status']);
        Route::get('/file/{sessionId}', [DocumentController::class, 'file']);
        Route::get('/', [DocumentController::class, 'index']);
        Route::delete('/{sessionId}', [DocumentController::class, 'destroy']);
    });

    Route::prefix('chat')->group(function () {
        Route::post('/session', [ChatController::class, 'createSession']);
        Route::post('/query', [ChatController::class, 'query']);
        Route::post('/summary', [ChatController::class, 'summary']);
        Route::get('/messages/{sessionId}', [ChatController::class, 'messages']);
        Route::get('/sessions', [ChatController::class, 'index']);
        Route::delete('/{sessionId}', [ChatController::class, 'destroy']);
    });
});
