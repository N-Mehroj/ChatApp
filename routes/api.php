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
