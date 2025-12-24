<?php

use App\Http\Controllers\Api\WidgetController;
use Illuminate\Support\Facades\Route;

// Widget API routes (no authentication required for public widget)
Route::prefix('widget')->group(function () {
    Route::post('/session', [WidgetController::class, 'createSession']);
    Route::post('/message', [WidgetController::class, 'sendMessage']);
    Route::get('/messages/{sessionId}', [WidgetController::class, 'getMessages']);
    Route::post('/typing', [WidgetController::class, 'handleTyping']);
    Route::post('/end-session', [WidgetController::class, 'endSession']);
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
