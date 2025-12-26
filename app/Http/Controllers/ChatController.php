<?php

namespace App\Http\Controllers;

use App\Events\ChatCreated;
use App\Events\ChatUpdated;
use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Events\UserOnlineStatus;
use App\Events\WidgetMessageSent;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        // Single optimized query with all necessary relationships
        $chatsQuery = Chat::with([
            'user:id,first_name,last_name,email,image,role,last_activity,department_id,organization_id,position,phone,username,is_app_installed,created_at',
            'user.department:id,name',
            'user.organization:id,name',
            'user.merchant:id,name,user_id',
            'lastMessage:id,chat_id,user_id,message,created_at,from_operator,read_at',
            'lastMessage.user:id,first_name,last_name',
            'widgetSession:id,session_id,visitor_name,visitor_email,visitor_phone,user_id',
            'widgetSession.user:id,first_name,last_name,email,department_id,organization_id,position,phone,username,is_app_installed,created_at',
            'widgetSession.user.department:id,name',
            'widgetSession.user.organization:id,name',
            'widgetSession.user.merchant:id,name,user_id'
        ])
            ->withCount(['messages', 'messages as unread_messages_count' => function ($query) use ($user) {
                $query->where('user_id', '!=', $user->id)->whereNull('read_at');
            }]);

        if ($user->isSupport()) {
            // Support users see all chats but limited to 50 most recent
            $chats = $chatsQuery->latest('updated_at')->limit(50)->get();
        } else {
            // Regular users see only their own chats
            $chats = $chatsQuery->where('user_id', $user->id)->latest('updated_at')->get();
        }

        // Transform data without additional queries
        $chats->each(function ($chat) {
            $chat->unread_count = $chat->unread_messages_count ?? 0;

            if ($chat->widgetSession) {
                $chat->is_widget_chat = true;
                $chat->visitor_name = $chat->widgetSession->visitor_name ?? 'Anonymous';
                $chat->visitor_email = $chat->widgetSession->visitor_email;
                $chat->visitor_phone = $chat->widgetSession->visitor_phone;
                $chat->widget_user = $chat->widgetSession->user;
            } else {
                $chat->is_widget_chat = false;
            }

            // Remove count attributes to clean up response
            unset($chat->messages_count, $chat->unread_messages_count);
        });

        return Inertia::render('Chat/Index', [
            'chats' => $chats,
            'user' => $user->only(['id', 'first_name', 'last_name', 'email', 'role', 'image']),
        ]);
    }

    public function show(Chat $chat): Response
    {
        $user = Auth::user();

        if (! $this->userCanAccessChat($user, $chat)) {
            abort(403);
        }

        // Single query to load chat with all relationships
        $chat->load([
            'user:id,first_name,last_name,email,image,role,last_activity,department_id,organization_id,position,phone,username,is_app_installed,created_at',
            'user.department:id,name',
            'user.organization:id,name',
            'user.merchant:id,name,user_id',
            'lastMessage:id,chat_id,user_id,message,created_at,from_operator',
            'widgetSession:id,session_id,visitor_name,visitor_email,visitor_phone,user_id',
            'widgetSession.user:id,first_name,last_name,email,department_id,organization_id,position,phone,username,is_app_installed,created_at',
            'widgetSession.user.department:id,name',
            'widgetSession.user.organization:id,name',
            'widgetSession.user.merchant:id,name,user_id'
        ]);

        // Single optimized query for messages with user data
        $messages = ChatMessage::with('user:id,first_name,last_name,email,image,role')
            ->where('chat_id', $chat->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        $chat->update(['is_new' => false]);

        // Only get users if needed for UI (support users or if chat has no participants)
        $users = collect();
        if ($user->isSupport()) {
            $users = User::with([
                'department:id,name',
                'organization:id,name',
                'merchant:id,name,user_id'
            ])
                ->select('id', 'first_name', 'last_name', 'email', 'image', 'role', 'last_activity', 'department_id', 'organization_id', 'position', 'phone', 'username', 'is_app_installed', 'created_at')
                ->where('id', '!=', $user->id)
                ->limit(20)
                ->get();
        }

        return Inertia::render('Chat/Show', [
            'chat' => $chat,
            'messages' => $messages,
            'currentUser' => $user->only(['id', 'first_name', 'last_name', 'email', 'role', 'image']),
            'users' => $users,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $currentUser = Auth::user();
        $userId = $request->user_id;

        // Only support users can create chats
        if (! $currentUser->isSupport()) {
            return response()->json(['error' => 'Only support users can create chats'], 403);
        }

        // Check if chat already exists for this user
        $chat = Chat::where('user_id', $userId)->first();

        if (! $chat) {
            $chat = Chat::create([
                'user_id' => $userId,
                'is_new' => true,
            ]);

            // Queue the broadcast to prevent blocking
            dispatch(function () use ($chat) {
                broadcast(new ChatCreated($chat));
            })->onQueue('broadcasts');

            Log::info('ChatCreated queued for broadcast', [
                'chat_id' => $chat->id,
                'user_id' => $userId,
            ]);
        }

        return response()->json([
            'chat' => $chat->load(['user', 'lastMessage']),
        ]);
    }

    public function sendMessage(Request $request, Chat $chat): JsonResponse
    {
        
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        if (! $this->userCanAccessChat($user, $chat)) {
            abort(403);
        }

        // Only support users can send messages
        if (! $user->isSupport()) {
            return response()->json(['error' => 'Only support users can send messages'], 403);
        }

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'from_operator' => $user->isSupport(),
            'message' => $request->message,
        ]);

        $chat->touch();

        // Load user for message
        $message->load('user');

        // Use queued broadcasting to prevent blocking
        try {
            // Broadcast to chat channels (for chat interface) - queued
            dispatch(function () use ($message) {
                broadcast(new MessageSent($message))->toOthers();
            })->onQueue('broadcasts');

            // If this is a widget chat, also broadcast to widget session channel - queued
            $widgetSession = $chat->widgetSession;
            if ($widgetSession) {
                dispatch(function () use ($message, $widgetSession) {
                    broadcast(new WidgetMessageSent($message, $widgetSession))->toOthers();
                })->onQueue('broadcasts');

                Log::info('WidgetMessageSent queued for broadcast', [
                    'chat_id' => $chat->id,
                    'message_id' => $message->id,
                    'session_id' => $widgetSession->session_id,
                ]);
            }

            // Broadcast chat update to update chat lists - queued
            dispatch(function () use ($chat, $message) {
                broadcast(new ChatUpdated($chat, $message));
            })->onQueue('broadcasts');
        } catch (\Exception $e) {
            Log::error('Broadcasting failed (non-blocking)', [
                'error' => $e->getMessage(),
                'chat_id' => $chat->id,
                'message_id' => $message->id,
            ]);
        }

        Log::info('Message broadcast sent', [
            'chat_id' => $chat->id,
            'message_id' => $message->id,
            'channel' => 'chat.' . $chat->id,
            'user_id' => $user->id,
            'from_operator' => $user->isSupport(),
        ]);

        Log::info('ChatUpdated event broadcasted', [
            'chat_id' => $chat->id,
            'message_id' => $message->id,
            'last_message' => $request->message,
        ]);

        return response()->json([
            'message' => $message->load('user'),
        ]);
    }

    public function getMessages(Chat $chat): JsonResponse
    {
        $user = Auth::user();

        if (! $this->userCanAccessChat($user, $chat)) {
            abort(403);
        }

        $messages = ChatMessage::with('user')
            ->where('chat_id', $chat->id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark unread messages as read by current user
        $unreadMessages = ChatMessage::where('chat_id', $chat->id)
            ->where('user_id', '!=', $user->id) // Not sent by current user
            ->whereNull('read_at') // Not read yet
            ->pluck('id')
            ->toArray();

        if (! empty($unreadMessages)) {
            ChatMessage::whereIn('id', $unreadMessages)
                ->update([
                    'read_at' => now(),
                    'read_by' => $user->id,
                ]);

            // Queue the broadcast to prevent blocking
            dispatch(function () use ($chat, $user, $unreadMessages) {
                broadcast(new MessageRead($chat, $user, $unreadMessages));
            })->onQueue('broadcasts');

            Log::info('MessageRead queued for broadcast', [
                'chat_id' => $chat->id,
                'reader_id' => $user->id,
                'message_count' => count($unreadMessages),
            ]);
        }

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function getUserChats(): JsonResponse
    {
        $user = Auth::user();

        // Cache key based on user and role
        $cacheKey = "user_chats_{$user->id}_{$user->role}";

        // Check cache first (cache for 1 minute to balance freshness vs performance)
        $chats = cache()->remember($cacheKey, 60, function () use ($user) {
            if ($user->isSupport()) {
                // Support users see all chats with optimized relationships
                return Chat::with([
                    'user:id,first_name,last_name,email,image,role,last_activity,department_id,organization_id,position,phone,username,is_app_installed,created_at',
                    'user.department:id,name',
                    'user.organization:id,name',
                    'user.merchant:id,name,user_id',
                    'lastMessage:id,chat_id,user_id,message,created_at,from_operator'
                ])
                    ->latest('updated_at')
                    ->limit(100) // Limit for performance
                    ->get();
            } else {
                // Regular users see only their own chats
                return Chat::with([
                    'user:id,first_name,last_name,email,image,role,last_activity,department_id,organization_id,position,phone,username,is_app_installed,created_at',
                    'user.department:id,name',
                    'user.organization:id,name',
                    'user.merchant:id,name,user_id',
                    'lastMessage:id,chat_id,user_id,message,created_at,from_operator'
                ])
                    ->where('user_id', $user->id)
                    ->latest('updated_at')
                    ->get();
            }
        });

        return response()->json([
            'chats' => $chats,
        ]);
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $request->get('q', '');

        if (empty(trim($query))) {
            // Return all users if no search query
            $users = User::with([
                'department:id,name',
                'organization:id,name',
                'merchant:id,name,user_id'
            ])
                ->select('id', 'first_name', 'last_name', 'email', 'image', 'role', 'last_activity', 'department_id', 'organization_id', 'position', 'phone', 'username', 'is_app_installed', 'created_at')
                ->where('id', '!=', $user->id)
                ->limit(50)
                ->get();
        } else {
            // Search users by name or email
            $users = User::with([
                'department:id,name',
                'organization:id,name',
                'merchant:id,name,user_id'
            ])
                ->select('id', 'first_name', 'last_name', 'email', 'image', 'role', 'last_activity', 'department_id', 'organization_id', 'position', 'phone', 'username', 'is_app_installed', 'created_at')
                ->where('id', '!=', $user->id)
                ->where(function ($q) use ($query) {
                    $q->where('first_name', 'LIKE', "%{$query}%")
                        ->orWhere('last_name', 'LIKE', "%{$query}%")
                        ->orWhere('email', 'LIKE', "%{$query}%")
                        ->orWhere('username', 'LIKE', "%{$query}%");
                })
                ->limit(20)
                ->get();
        }

        return response()->json([
            'users' => $users,
        ]);
    }

    public function markAsRead(Chat $chat): JsonResponse
    {
        $user = Auth::user();

        if (! $this->userCanAccessChat($user, $chat)) {
            abort(403);
        }

        // Mark all unread messages in this chat as read by current user
        $unreadMessages = ChatMessage::where('chat_id', $chat->id)
            ->where('user_id', '!=', $user->id) // Not sent by current user
            ->whereNull('read_at') // Not read yet
            ->pluck('id')
            ->toArray();

        if (! empty($unreadMessages)) {
            ChatMessage::whereIn('id', $unreadMessages)
                ->update([
                    'read_at' => now(),
                    'read_by' => $user->id,
                ]);

            // Queue the broadcast to prevent blocking
            dispatch(function () use ($chat, $user, $unreadMessages) {
                broadcast(new MessageRead($chat, $user, $unreadMessages));
            })->onQueue('broadcasts');

            Log::info('MessageRead queued for broadcast', [
                'chat_id' => $chat->id,
                'reader_id' => $user->id,
                'message_count' => count($unreadMessages),
            ]);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function updateOnlineStatus(): JsonResponse
    {
        $user = Auth::user();
        $cacheKey = "user_online_status_{$user->id}";

        // Check if we recently updated online status to avoid too frequent updates
        $lastUpdate = cache($cacheKey);
        if ($lastUpdate && now()->diffInMinutes($lastUpdate) < 2) {
            // If updated within last 2 minutes, return success without database update
            return response()->json([
                'success' => true,
                'status' => 'online',
                'cached' => true,
            ]);
        }

        // Update user's last activity only if enough time has passed
        $user->update(['last_activity' => now()]);

        // Cache the update time to prevent frequent database updates
        cache([$cacheKey => now()], now()->addMinutes(10));

        // Queue the broadcast to prevent blocking (only if actually updated)
        dispatch(function () use ($user) {
            broadcast(new UserOnlineStatus($user, true));
        })->onQueue('broadcasts');

        Log::info('User online status queued for broadcast', ['user_id' => $user->id, 'status' => 'online']);

        return response()->json([
            'success' => true,
            'status' => 'online',
        ]);
    }

    public function setOfflineStatus(): JsonResponse
    {
        $user = Auth::user();

        // Queue the broadcast to prevent blocking
        dispatch(function () use ($user) {
            broadcast(new UserOnlineStatus($user, false));
        })->onQueue('broadcasts');

        Log::info('User offline status queued for broadcast', ['user_id' => $user->id, 'status' => 'offline']);

        return response()->json([
            'success' => true,
            'status' => 'offline',
        ]);
    }

    private function userCanAccessChat(User $user, Chat $chat): bool
    {
        return $chat->isParticipant($user) || $user->isSupport() ||
            $chat->messages()->where('user_id', $user->id)->exists();
    }
}
