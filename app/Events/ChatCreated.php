<?php

namespace App\Events;

use App\Models\Chat;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Chat $chat;

    public array $participantIds;

    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
        $this->participantIds = [$chat->user_id]; // Only customer for support tickets
    }

    public function broadcastOn(): array
    {
        $channels = [];
        foreach ($this->participantIds as $userId) {
            $channels[] = new PrivateChannel('user.chats.' . $userId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'chat.created';
    }

    public function broadcastWith(): array
    {
        return [
            'chat' => $this->chat->load(['user', 'lastMessage']),
        ];
    }
}
