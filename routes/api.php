<?php

use App\Http\Controllers\Api\WidgetController;
use Illuminate\Support\Facades\Route;

// Widget API routes (no authentication required for public widget)
Route::prefix('widget')->group(function () {
    // Vue.js widget authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/session', [WidgetController::class, 'session']);
        Route::get('/chats/{chat}/messages', [WidgetController::class, 'getMessages']);
        Route::post('/chats/{chat}/messages', [WidgetController::class, 'sendWidgetMessage']);
        Route::patch('/chats/{chat}/messages/{message}/read', [WidgetController::class, 'markAsRead']);
        Route::get('/operators', [WidgetController::class, 'operators']);
    });

    // Legacy widget routes (for existing widget)
    Route::post('/session', [WidgetController::class, 'createSession']);
    Route::post('/message', [WidgetController::class, 'sendMessage']);
    Route::get('/messages/{sessionId}', [WidgetController::class, 'getMessages']);
    Route::post('/typing', [WidgetController::class, 'handleTyping']);
    Route::post('/end-session', [WidgetController::class, 'endSession']);

    // Public config
    Route::get('/config', [WidgetController::class, 'config']);
});

// User info API (for authenticated users)
Route::middleware('auth')->get('/user/info', function () {
    $user = auth()->user();
    return response()->json([
        'id' => $user->id,
        'name' => $user->name ?? ($user->first_name . ' ' . $user->last_name),
        'email' => $user->email,
        'role' => $user->role?->value ?? 'user',
        'isLoggedIn' => true
    ]);
});
