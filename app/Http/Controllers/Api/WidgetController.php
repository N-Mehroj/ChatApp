<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Events\WidgetMessageSent;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\WidgetSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WidgetController extends Controller
{
    /**
     * Get current user session for widget
     */
    public function session(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $chat = null;
        $operator = null;

        if ($user) {
            // Get or create chat for authenticated user
            $chat = Chat::firstOrCreate([
                'user_id' => $user->id,
            ]);

            // Get random support operator info
            $operator = User::whereIn('role', ['support', 'admin'])
                ->first();
        }

        return response()->json([
            'user' => $user ? [
                'id' => $user->id,
                'display_name' => $user->display_name,
                'avatar_url' => $user->avatar_url,
                'role' => $user->role?->value ?? 'user',
                'is_online' => $user->is_online,
            ] : null,
            'chat_id' => $chat?->id,
            'operator' => $operator ? [
                'name' => $operator->display_name,
                'avatar' => $operator->avatar_url,
                'is_online' => $operator->is_online,
            ] : [
                'name' => 'Support Team',
                'avatar' => null,
                'is_online' => true,
            ],
            'config' => [
                'allow_guests' => false,
                'require_auth' => true,
                'ws_url' => config('broadcasting.connections.reverb.url', 'ws://localhost:8080'),
            ],
        ]);
    }

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

            // Check if user is authenticated or provided via metadata
            $authenticatedUser = auth()->user();
            $metadataUser = null;

            // Check if user metadata is provided (from widget frontend)
            if ($request->has('user_metadata') && is_array($request->user_metadata)) {
                $userMetadata = $request->user_metadata;
                if (isset($userMetadata['user_id']) && $userMetadata['user_id']) {
                    $metadataUser = User::find($userMetadata['user_id']);
                }
            }

            // Use authenticated user first, then metadata user
            $currentUser = $authenticatedUser ?: $metadataUser;

            $sessionData = [
                'session_id' => $sessionId,
                'merchant_id' => $merchant->id,
                'visitor_id' => $visitorId,
            ];

            $defaultData = [
                'visitor_ip' => $request->ip(),
                'visitor_user_agent' => $request->userAgent(),
                'is_active' => true,
            ];

            // If user is available (authenticated or from metadata), store user info
            if ($currentUser) {
                $sessionData['user_id'] = $currentUser->id;
                $defaultData['visitor_name'] = $currentUser->display_name;
                $defaultData['visitor_email'] = $currentUser->email;
            }

            $session = WidgetSession::firstOrCreate($sessionData, $defaultData);

            // Get or create chat - if session has user_id, find by merchant+visitor user, otherwise by session
            if ($session->user_id) {
                // For logged-in users: find or create chat by merchant and visitor user
                $chat = Chat::firstOrCreate([
                    'user_id' => $merchant->id,
                    'visitor_user_id' => $session->user_id,
                ], [
                    'widget_session_id' => $session->id, // Always set session ID when creating
                ]);

                \Log::info('Widget Session - Chat found/created', [
                    'chat_id' => $chat->id,
                    'merchant_id' => $merchant->id,
                    'visitor_user_id' => $session->user_id,
                    'was_recently_created' => $chat->wasRecentlyCreated
                ]);

                // Always ensure this chat is linked to the current session
                if ($chat->widget_session_id !== $session->id) {
                    $chat->update(['widget_session_id' => $session->id]);
                    \Log::info('Chat session link updated', [
                        'chat_id' => $chat->id,
                        'old_session_id' => $chat->widget_session_id,
                        'new_session_id' => $session->id
                    ]);
                }
            } else {
                // For anonymous visitors: find or create chat by session
                $chat = Chat::firstOrCreate([
                    'user_id' => $merchant->id,
                    'widget_session_id' => $session->id,
                ]);
            }

            // Get all messages (remove limit to show complete chat history)
            $messages = ChatMessage::where('chat_id', $chat->id)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($message) use ($merchant) {
                    return [
                        'id' => $message->id,
                        'message' => $message->message,
                        'from_operator' => $message->from_operator,
                        'is_from_user' => $message->user_id !== $merchant->id, // Keep for backward compatibility
                        'created_at' => $message->created_at->toISOString(),
                    ];
                });

            \Log::info('Widget Session - Messages loaded', [
                'chat_id' => $chat->id,
                'messages_count' => $messages->count(),
                'first_message' => $messages->first(),
                'last_message' => $messages->last()
            ]);

            // WebSocket/Reverb configuration
            $reverbHost = config('reverb.servers.reverb.host', '127.0.0.1');
            $reverbPort = config('reverb.servers.reverb.port', 8080);
            $reverbKey = config('broadcasting.connections.reverb.key') ?? env('REVERB_APP_KEY');
            $reverbScheme = env('REVERB_SCHEME', 'http');
            $wsProtocol = $reverbScheme === 'https' ? 'wss' : 'ws';

            return response()->json([
                'session_id' => $sessionId,
                'chat_id' => $chat->id,
                'messages' => $messages,
                'user' => $currentUser ? [
                    'id' => $currentUser->id,
                    'name' => $currentUser->display_name,
                    'email' => $currentUser->email,
                    'avatar' => $currentUser->avatar_url ?? null,
                ] : null,
                'agent' => [
                    'name' => $merchant->name,
                    'avatar' => $merchant->avatar ?? null,
                ],
                'config' => [
                    'ws_url' => sprintf('%s://%s:%s', $wsProtocol, $reverbHost, $reverbPort),
                    'app_key' => $reverbKey,
                    'chat_id' => $chat->id,
                    'session_id' => $sessionId,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Widget session creation failed: ' . $e->getMessage());

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
        \Log::info('SendMessage endpoint hit', ['data' => $request->all()]);

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

            // Find the chat using the same logic as createSession
            $chat = null;

            if ($session->user_id) {
                // For logged-in users: find by merchant and visitor user
                $chat = Chat::where('user_id', $merchant->id)
                    ->where('visitor_user_id', $session->user_id)
                    ->first();

                // If not found, try by widget_session_id as fallback
                if (!$chat) {
                    $chat = Chat::where('widget_session_id', $session->id)->first();
                }
            } else {
                // For anonymous visitors: find by session
                $chat = Chat::where('widget_session_id', $session->id)->first();
            }

            if (! $chat) {
                \Log::error('Chat not found for session', [
                    'session_id' => $session->id,
                    'session_user_id' => $session->user_id,
                    'merchant_id' => $merchant->id,
                    'request_session_id' => $request->session_id
                ]);
                return response()->json(['error' => 'Chat not found'], 404);
            }

            // If session is linked to an authenticated user, persist their user_id
            $message = ChatMessage::create([
                'chat_id' => $chat->id,
                'user_id' => $session->user_id, // null for anonymous visitors, user_id when identified
                'message' => $request->message,
                'from_operator' => false, // User messages are not from operator
                'widget_session_id' => $session->id,
            ]);

            // Update session activity
            $session->update(['last_activity' => now()]);

            // Update chat with latest message
            $chat->update([
                'latest_message_id' => $message->id,
                'updated_at' => now(),
            ]);

            // Broadcast widget message to support agents and other connected clients
            broadcast(new WidgetMessageSent($message, $session))->toOthers();

            // Also broadcast on the main chat channel for real-time updates
            broadcast(new \App\Events\MessageSent($message))->toOthers();

            return response()->json([
                'message' => [
                    'id' => $message->id,
                    'message' => $message->message,
                    'from_operator' => $message->from_operator,
                    'is_from_user' => true, // Keep for backward compatibility
                    'created_at' => $message->created_at->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Widget message send failed: ' . $e->getMessage());

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
                        'from_operator' => $message->from_operator,
                        'is_from_user' => $message->user_id !== $merchant->id, // Keep for backward compatibility
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
            \Log::error('Get widget messages failed: ' . $e->getMessage());

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
            \Log::error('Widget typing update failed: ' . $e->getMessage());

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
            \Log::error('Widget session end failed: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to end session',
            ], 500);
        }
    }

    /**
     * Get messages for Vue.js widget
     */
    public function getChatMessages(Request $request, Chat $chat): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        if ($chat->user_id !== $user->id && ! $user->isOperatorOrSupport()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = $chat->messages()
            ->with(['user:id,first_name,last_name,username,role,image'])
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get();

        return response()->json([
            'messages' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'chat_id' => $message->chat_id,
                    'user_id' => $message->user_id,
                    'message' => $message->message,
                    'from_operator' => $message->from_operator,
                    'created_at' => $message->created_at,
                    'read_at' => $message->read_at,
                    'user' => [
                        'id' => $message->user->id,
                        'display_name' => $message->user->display_name,
                        'avatar_url' => $message->user->avatar_url,
                        'role' => $message->user->role?->value ?? 'user',
                        'is_online' => $message->user->is_online,
                    ],
                ];
            }),
        ]);
    }

    /**
     * Send message via Vue.js widget
     */
    public function sendWidgetMessage(Request $request, Chat $chat): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        if ($chat->user_id !== $user->id && ! $user->isOperatorOrSupport()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $fromOperator = $user->isOperatorOrSupport();

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'message' => $request->input('message'),
            'from_operator' => $fromOperator,
        ]);

        $message->load('user');
        MessageSent::dispatch($message);

        return response()->json([
            'message' => [
                'id' => $message->id,
                'chat_id' => $message->chat_id,
                'user_id' => $message->user_id,
                'message' => $message->message,
                'from_operator' => $message->from_operator,
                'created_at' => $message->created_at,
                'user' => [
                    'id' => $message->user->id,
                    'display_name' => $message->user->display_name,
                    'avatar_url' => $message->user->avatar_url,
                    'role' => $message->user->role?->value ?? 'user',
                    'is_online' => $message->user->is_online,
                ],
            ],
        ]);
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Request $request, Chat $chat, ChatMessage $message): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        if ($chat->user_id !== $user->id && ! $user->isOperatorOrSupport()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($message->user_id !== $user->id) {
            $message->update([
                'read_at' => now(),
                'read_by' => $user->id,
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Identify user for widget session
     */
    public function identifyUser(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'api_key' => 'required|string',
            'session_id' => 'required|string',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
            'metadata' => 'nullable|array',
        ]);

        try {
            // Find merchant by API key
            $merchant = User::where('api_key', $request->api_key)
                ->where('role', 'merchant')
                ->first();

            if (! $merchant) {
                return response()->json(['error' => 'Invalid API key'], 401);
            }

            // Find widget session
            $session = WidgetSession::where('session_id', $request->session_id)
                ->where('merchant_id', $merchant->id)
                ->first();

            if (! $session) {
                return response()->json(['error' => 'Session not found'], 404);
            }

            $identifiedUserId = $request->user()?->id
                ?? ($request->metadata['user_id'] ?? null);

            // Update session with user details and attach user_id when available
            $session->update([
                'visitor_name' => $request->name,
                'visitor_email' => $request->email,
                'visitor_phone' => $request->phone,
                'visitor_notes' => $request->notes,
                'visitor_metadata' => $request->metadata,
                'user_id' => $identifiedUserId ?? $session->user_id,
                'last_activity' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User identified successfully',
                'session' => [
                    'id' => $session->session_id,
                    'visitor_name' => $session->visitor_name,
                    'visitor_email' => $session->visitor_email,
                    'visitor_phone' => $session->visitor_phone,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Widget user identification failed: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to identify user',
            ], 500);
        }
    }

    /**
     * Send reply to widget session (for support agents)
     */
    public function sendReply(Request $request, string $sessionId): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        try {
            $user = $request->user();

            if (! $user || ! $user->isSupport()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Find widget session
            $session = WidgetSession::where('session_id', $sessionId)->first();

            if (! $session) {
                return response()->json(['error' => 'Session not found'], 404);
            }

            // Find the chat
            $chat = Chat::where('widget_session_id', $session->id)->first();

            if (! $chat) {
                return response()->json(['error' => 'Chat not found'], 404);
            }

            // Create message from support agent
            $message = ChatMessage::create([
                'chat_id' => $chat->id,
                'user_id' => $user->id, // Support agent
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

            // Broadcast to widget
            broadcast(new WidgetMessageSent($message, $session))->toOthers();

            return response()->json([
                'message' => [
                    'id' => $message->id,
                    'message' => $message->message,
                    'is_from_user' => false,
                    'created_at' => $message->created_at->toISOString(),
                    'user' => [
                        'name' => $user->display_name,
                        'avatar' => $user->avatar_url,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Widget reply send failed: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to send reply',
            ], 500);
        }
    }

    /**
     * Get widget config
     */
    public function config(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'allow_guests' => false,
            'require_auth' => true,
            'primary_color' => '#3B82F6',
            'position' => 'bottom-right',
            'theme' => 'modern',
            'ws_url' => config('broadcasting.connections.reverb.url', 'ws://localhost:8080'),
            'app_key' => config('broadcasting.connections.reverb.app_key'),
        ]);
    }

    /**
     * Get support operators
     */
    public function operators(): \Illuminate\Http\JsonResponse
    {
        $operators = User::whereIn('role', ['support', 'admin'])
            ->select(['id', 'first_name', 'last_name', 'username', 'image', 'last_activity'])
            ->get();

        return response()->json([
            'operators' => $operators->map(function ($operator) {
                return [
                    'id' => $operator->id,
                    'name' => $operator->display_name,
                    'avatar' => $operator->avatar_url,
                    'is_online' => $operator->is_online,
                    'role' => $operator->role?->value ?? 'support',
                ];
            }),
        ]);
    }
}
