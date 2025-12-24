<?php

namespace App\Http\Controllers;

use App\Events\ChatCreated;
use App\Events\ChatUpdated;
use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Events\UserOnlineStatus;
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

        // For support users, limit to recent chats to reduce data load
        $chatsQuery = Chat::with(['user', 'lastMessage']);

        if ($user->isSupport()) {
            // Support users see all chats but limited to 50 most recent
            $chatsQuery = $chatsQuery->latest('updated_at')->limit(50);
        } else {
            // Regular users see only their own chats
            $chatsQuery = $chatsQuery->where('user_id', $user->id)->latest('updated_at');
        }

        $chats = $chatsQuery->get();

        // Add unread count for each chat
        $chats->each(function ($chat) use ($user) {
            $chat->unread_count = $chat->getUnreadCount($user);
        });

        return Inertia::render('Chat/Index', [
            'chats' => $chats,
            'user' => $user,
        ]);
    }

    public function show(Chat $chat): Response
    {
        $user = Auth::user();

        if (! $this->userCanAccessChat($user, $chat)) {
            abort(403);
        }

        // Load only the latest 50 messages for performance
        $messages = ChatMessage::with('user')
            ->where('chat_id', $chat->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        $chat->update(['is_new' => false]);

        // Limit users to 50 for support users to prevent excessive data load
        $usersQuery = User::where('id', '!=', $user->id);
        if ($user->isSupport()) {
            $usersQuery = $usersQuery->limit(50);
        }
        $users = $usersQuery->get();

        return Inertia::render('Chat/Show', [
            'chat' => $chat->load(['user', 'lastMessage']),
            'messages' => $messages,
            'currentUser' => $user,
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

            // Broadcast chat creation to the user
            broadcast(new ChatCreated($chat));
            Log::info('ChatCreated event broadcasted', [
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

        $broadcastEvent = new MessageSent($message->load('user'));
        broadcast($broadcastEvent)->toOthers();

        // Broadcast chat update to update chat lists
        broadcast(new ChatUpdated($chat, $message));

        Log::info('Message broadcast sent', [
            'chat_id' => $chat->id,
            'message_id' => $message->id,
            'channel' => 'chat.'.$chat->id,
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

            // Broadcast message read event
            $event = new MessageRead($chat, $user, $unreadMessages);
            broadcast($event);
            Log::info('MessageRead event broadcasted (getMessages)', [
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

        if ($user->isSupport()) {
            // Support users see all chats
            $chats = Chat::with(['user', 'lastMessage'])
                ->latest('updated_at')
                ->get();
        } else {
            // Regular users see only their own chats
            $chats = Chat::with(['user', 'lastMessage'])
                ->where('user_id', $user->id)
                ->latest('updated_at')
                ->get();
        }

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
            $users = User::where('id', '!=', $user->id)
                ->select('id', 'first_name', 'last_name', 'email')
                ->limit(50)
                ->get();
        } else {
            // Search users by name or email
            $users = User::where('id', '!=', $user->id)
                ->where(function ($q) use ($query) {
                    $q->where('first_name', 'LIKE', "%{$query}%")
                        ->orWhere('last_name', 'LIKE', "%{$query}%")
                        ->orWhere('email', 'LIKE', "%{$query}%");
                })
                ->select('id', 'first_name', 'last_name', 'email')
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

            // Broadcast message read event
            $event = new MessageRead($chat, $user, $unreadMessages);
            broadcast($event);
            Log::info('MessageRead event broadcasted (markAsRead)', [
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

        // Update user's last activity
        $user->update(['last_activity' => now()]);

        // Broadcast online status
        $event = new UserOnlineStatus($user, true);
        broadcast($event);
        Log::info('User online status broadcasted', ['user_id' => $user->id, 'status' => 'online']);

        return response()->json([
            'success' => true,
            'status' => 'online',
        ]);
    }

    public function setOfflineStatus(): JsonResponse
    {
        $user = Auth::user();

        // Broadcast offline status
        $event = new UserOnlineStatus($user, false);
        broadcast($event);
        Log::info('User offline status broadcasted', ['user_id' => $user->id, 'status' => 'offline']);

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
