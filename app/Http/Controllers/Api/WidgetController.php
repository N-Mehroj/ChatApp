<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\WidgetSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WidgetController extends Controller
{
    /**
     * Initialize a new widget session
     */
    public function createSession(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'api_key' => 'required|string',
        ]);

        try {
            // Find the merchant by API key
            $merchant = User::where('api_key', $request->api_key)
                ->where('role', 'merchant')
                ->first();

            if (! $merchant) {
                return response()->json([
                    'error' => 'Invalid API key',
                ], 401);
            }

            // Create or find existing session
            $sessionId = $request->session_id ?? Str::uuid();
            $visitorId = $request->visitor_id ?? $request->ip();

            $session = WidgetSession::firstOrCreate([
                'session_id' => $sessionId,
                'merchant_id' => $merchant->id,
                'visitor_id' => $visitorId,
            ], [
                'visitor_ip' => $request->ip(),
                'visitor_user_agent' => $request->userAgent(),
                'is_active' => true,
            ]);

            // Get or create chat for this session
            $chat = Chat::firstOrCreate([
                'user_id' => $merchant->id,
                'recipient_id' => null, // Widget visitor
                'widget_session_id' => $session->id,
            ]);

            // Get recent messages
            $messages = ChatMessage::where('chat_id', $chat->id)
                ->orderBy('created_at', 'asc')
                ->limit(50)
                ->get()
                ->map(function ($message) use ($merchant) {
                    return [
                        'id' => $message->id,
                        'message' => $message->message,
                        'is_from_user' => $message->user_id !== $merchant->id,
                        'created_at' => $message->created_at->toISOString(),
                    ];
                });

            return response()->json([
                'session_id' => $sessionId,
                'chat_id' => $chat->id,
                'messages' => $messages,
                'agent' => [
                    'name' => $merchant->name,
                    'avatar' => $merchant->avatar ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Widget session creation failed: '.$e->getMessage());

            return response()->json([
                'error' => 'Failed to create session',
            ], 500);
        }
    }

    /**
     * Send a message from widget
     */
    public function sendMessage(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'api_key' => 'required|string',
            'session_id' => 'required|string',
            'message' => 'required|string|max:1000',
        ]);

        try {
            // Validate session and merchant
            $merchant = User::where('api_key', $request->api_key)
                ->where('role', 'merchant')
                ->first();

            if (! $merchant) {
                return response()->json(['error' => 'Invalid API key'], 401);
            }

            $session = WidgetSession::where('session_id', $request->session_id)
                ->where('merchant_id', $merchant->id)
                ->first();

            if (! $session) {
                return response()->json(['error' => 'Invalid session'], 404);
            }

            // Find the chat
            $chat = Chat::where('widget_session_id', $session->id)->first();

            if (! $chat) {
                return response()->json(['error' => 'Chat not found'], 404);
            }

            // Create message from visitor
            $message = ChatMessage::create([
                'chat_id' => $chat->id,
                'user_id' => null, // Widget visitor
                'message' => $request->message,
                'widget_session_id' => $session->id,
            ]);

            // Update session activity
            $session->update(['last_activity' => now()]);

            // Update chat with latest message
            $chat->update([
                'latest_message_id' => $message->id,
                'updated_at' => now(),
            ]);

            // Broadcast to merchant (support agent)
            broadcast(new MessageSent($message, $chat))->toOthers();

            return response()->json([
                'message' => [
                    'id' => $message->id,
                    'message' => $message->message,
                    'is_from_user' => true,
                    'created_at' => $message->created_at->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Widget message send failed: '.$e->getMessage());

            return response()->json([
                'error' => 'Failed to send message',
            ], 500);
        }
    }

    /**
     * Get messages for a session
     */
    public function getMessages(Request $request, string $sessionId): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'api_key' => 'required|string',
        ]);

        try {
            $merchant = User::where('api_key', $request->api_key)->first();

            if (! $merchant) {
                return response()->json(['error' => 'Invalid API key'], 401);
            }

            $session = WidgetSession::where('session_id', $sessionId)
                ->where('merchant_id', $merchant->id)
                ->first();

            if (! $session) {
                return response()->json(['error' => 'Session not found'], 404);
            }

            $chat = Chat::where('widget_session_id', $session->id)->first();

            if (! $chat) {
                return response()->json(['messages' => [], 'agent_typing' => false]);
            }

            $messages = ChatMessage::where('chat_id', $chat->id)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($message) use ($merchant) {
                    return [
                        'id' => $message->id,
                        'message' => $message->message,
                        'is_from_user' => $message->user_id !== $merchant->id,
                        'created_at' => $message->created_at->toISOString(),
                    ];
                });

            // Check if agent is typing (you can implement this based on your needs)
            $agentTyping = false;

            return response()->json([
                'messages' => $messages,
                'agent_typing' => $agentTyping,
            ]);
        } catch (\Exception $e) {
            \Log::error('Get widget messages failed: '.$e->getMessage());

            return response()->json([
                'error' => 'Failed to get messages',
            ], 500);
        }
    }

    /**
     * Handle typing indicator
     */
    public function handleTyping(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'api_key' => 'required|string',
            'session_id' => 'required|string',
            'typing' => 'required|boolean',
        ]);

        try {
            $merchant = User::where('api_key', $request->api_key)->first();

            if (! $merchant) {
                return response()->json(['error' => 'Invalid API key'], 401);
            }

            $session = WidgetSession::where('session_id', $request->session_id)
                ->where('merchant_id', $merchant->id)
                ->first();

            if (! $session) {
                return response()->json(['error' => 'Session not found'], 404);
            }

            // Update typing status in session or cache
            // You could use Redis/Cache to store typing status
            $session->update([
                'visitor_typing' => $request->typing,
                'last_typing_at' => $request->typing ? now() : null,
            ]);

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            \Log::error('Widget typing update failed: '.$e->getMessage());

            return response()->json([
                'error' => 'Failed to update typing status',
            ], 500);
        }
    }

    /**
     * End widget session
     */
    public function endSession(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'api_key' => 'required|string',
            'session_id' => 'required|string',
        ]);

        try {
            $merchant = User::where('api_key', $request->api_key)->first();

            if (! $merchant) {
                return response()->json(['error' => 'Invalid API key'], 401);
            }

            $session = WidgetSession::where('session_id', $request->session_id)
                ->where('merchant_id', $merchant->id)
                ->first();

            if ($session) {
                $session->update([
                    'is_active' => false,
                    'ended_at' => now(),
                ]);
            }

            return response()->json(['status' => 'session ended']);
        } catch (\Exception $e) {
            \Log::error('Widget session end failed: '.$e->getMessage());

            return response()->json([
                'error' => 'Failed to end session',
            ], 500);
        }
    }
}
