<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Widget demo routes
require __DIR__.'/widget.php';

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Chat routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{chat}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat', [ChatController::class, 'store'])->name('chat.store');
    Route::post('/chat/{chat}/message', [ChatController::class, 'sendMessage'])->name('chat.send-message');
    Route::get('/chat/{chat}/messages', [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/{chat}/mark-read', [ChatController::class, 'markAsRead'])->name('chat.mark-read');
    Route::get('/api/user/chats', [ChatController::class, 'getUserChats'])->name('api.user.chats');
    Route::get('/api/users/search', [ChatController::class, 'searchUsers'])->name('api.users.search');

    // Online status routes
    Route::post('/api/user/online', [ChatController::class, 'updateOnlineStatus'])->name('api.user.online');
    Route::post('/api/user/offline', [ChatController::class, 'setOfflineStatus'])->name('api.user.offline');
});

// Widget routes (public access)
Route::prefix('widget')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Widget/ChatWidget');
    })->name('widget.index');

    Route::get('/demo', function () {
        return view('widget.demo');
    })->name('widget.demo');

    // Vue.js Chat Demo with User Identification
    Route::get('/chat/demo', function () {
        return view('demo');
    })->name('chat.demo');

    // Widget user test page
    Route::get('/test', function () {
        return response()->file(public_path('widget-user-test.html'));
    })->name('widget.test');
});

// Serve widget assets
Route::get('/widget/sdk.js', function () {
    return response()->file(public_path('widget/sdk.js'));
})->name('widget.sdk');

require __DIR__.'/settings.php';
