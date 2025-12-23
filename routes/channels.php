<?php

use App\Models\Chat;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);

    if (!$chat) {
        return false;
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
    return (int) $user->id === (int) $userId;
});
