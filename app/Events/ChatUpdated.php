<?php

namespace App\Events;

use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Chat $chat;
    public ?ChatMessage $lastMessage;
    public array $participantIds;

    public function __construct(Chat $chat, ?ChatMessage $lastMessage = null)
    {
        $this->chat = $chat;
        $this->lastMessage = $lastMessage;
        $this->participantIds = [$chat->user_id, $chat->recipient_id];
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
        return 'chat.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'chat' => $this->chat->load(['user', 'recipient', 'lastMessage']),
            'last_message' => $this->lastMessage?->load('user'),
        ];
    }
}
