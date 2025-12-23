<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(ChatMessage $message)
    {
        $this->message = $message;
        Log::info('MessageSent event created', [
            'message_id' => $message->id,
            'chat_id' => $message->chat_id,
            'user_id' => $message->user_id
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('chat.' . $this->message->chat_id),
        ];

        Log::info('Broadcasting to channels', [
            'channels' => array_map(fn($channel) => $channel->name, $channels),
            'message_id' => $this->message->id
        ]);

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'chat_id' => $this->message->chat_id,
                'user_id' => $this->message->user_id,
                'message' => $this->message->message,
                'from_operator' => $this->message->from_operator,
                'created_at' => $this->message->created_at,
                'read_at' => $this->message->read_at,
                'read_by' => $this->message->read_by,
                'user' => [
                    'id' => $this->message->user->id,
                    'first_name' => $this->message->user->first_name,
                    'last_name' => $this->message->user->last_name,
                ]
            ]
        ];
    }
}
