<?php

namespace App\Events;

use App\Models\ChatMessage;
use App\Models\WidgetSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WidgetMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public $session;

    /**
     * Create a new event instance.
     */
    public function __construct(ChatMessage $message, WidgetSession $session)
    {
        $this->message = $message;
        $this->session = $session;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('widget.session.' . $this->session->session_id),
            new PrivateChannel('widget.merchant.' . $this->session->merchant_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'widget.message.sent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'message' => $this->message->message,
                'is_from_user' => $this->message->user_id === null, // widget visitor
                'user_id' => $this->message->user_id,
                'created_at' => $this->message->created_at->toISOString(),
            ],
            'session' => [
                'id' => $this->session->session_id,
                'visitor_name' => $this->session->visitor_name,
                'visitor_email' => $this->session->visitor_email,
                'visitor_phone' => $this->session->visitor_phone,
                'user_id' => $this->session->user_id,
            ],
            'chat_id' => $this->message->chat_id,
        ];
    }
}
