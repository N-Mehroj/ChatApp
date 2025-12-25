<?php

use App\Models\Chat;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {

    $chat = Chat::find($chatId);

    if (! $chat) {
        return false;
    }

    // Allow support role to access all chats
    if ($user->isSupport()) {
        return true;
    }

    return $chat->isParticipant($user) ||
        $chat->messages()->where('user_id', $user->id)->exists();
});

// Public channel for user online status - no authentication needed
Broadcast::channel('user-status.{userId}', function () {
    return true; // Allow everyone to listen to status updates
});

// Private channel for user's chat list updates
Broadcast::channel('user.chats.{userId}', function ($user, $userId) {
    if ($user->isSupport()) {
        return true;
    }

    return (int) $user->id === (int) $userId;
});

// Widget session channel for real-time communication
Broadcast::channel('widget.session.{sessionId}', function ($user, $sessionId) {
    // Support users can access all widget sessions
    if ($user && $user->isSupport()) {
        return true;
    }

    // Allow public access for widget sessions (guests)
    return true;
});

// Widget merchant channel - for merchants to receive widget messages
Broadcast::channel('widget.merchant.{merchantId}', function ($user, $merchantId) {
    // Only support users and the merchant can access
    if ($user && ($user->isSupport() || $user->id == $merchantId)) {
        return true;
    }

    return false;
});
