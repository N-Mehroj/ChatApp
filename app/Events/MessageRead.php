<?php

namespace App\Events;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Chat $chat;
    public User $reader;
    public array $messageIds;

    public function __construct(Chat $chat, User $reader, array $messageIds)
    {
        $this->chat = $chat;
        $this->reader = $reader;
        $this->messageIds = $messageIds;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->chat->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.read';
    }

    public function broadcastWith(): array
    {
        return [
            'chat_id' => $this->chat->id,
            'reader' => [
                'id' => $this->reader->id,
                'name' => $this->reader->name,
            ],
            'message_ids' => $this->messageIds,
            'read_at' => now()->toISOString(),
        ];
    }
}
