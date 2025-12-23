<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
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

        $chats = Chat::with(['user', 'lastMessage'])
            ->where('user_id', $user->id)
            ->orWhereHas('messages', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest('updated_at')
            ->get();

        return Inertia::render('Chat/Index', [
            'chats' => $chats,
            'user' => $user
        ]);
    }

    public function show(Chat $chat): Response
    {
        $user = Auth::user();

        if (!$this->userCanAccessChat($user, $chat)) {
            abort(403);
        }

        $messages = ChatMessage::with('user')
            ->where('chat_id', $chat->id)
            ->orderBy('created_at', 'asc')
            ->get();

        $chat->update(['is_new' => false]);

        $users = User::where('id', '!=', $user->id)->get();

        return Inertia::render('Chat/Show', [
            'chat' => $chat->load(['user', 'lastMessage']),
            'messages' => $messages,
            'currentUser' => $user,
            'users' => $users
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        $recipientId = $request->recipient_id;

        if ($user->id === (int) $recipientId) {
            return response()->json(['error' => 'Cannot create chat with yourself'], 400);
        }

        $chat = Chat::where(function ($query) use ($user, $recipientId) {
            $query->where('user_id', $user->id)
                ->where('recipient_id', $recipientId);
        })->orWhere(function ($query) use ($user, $recipientId) {
            $query->where('user_id', $recipientId)
                ->where('recipient_id', $user->id);
        })->first();

        if (!$chat) {
            $chat = Chat::create([
                'user_id' => $user->id,
                'recipient_id' => $recipientId,
                'is_new' => true
            ]);
        }

        return response()->json([
            'chat' => $chat->load(['user', 'recipient', 'lastMessage'])
        ]);
    }

    public function sendMessage(Request $request, Chat $chat): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        if (!$this->userCanAccessChat($user, $chat)) {
            abort(403);
        }

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'from_operator' => false,
            'message' => $request->message,
        ]);

        $chat->touch();

        $broadcastEvent = new MessageSent($message->load('user'));
        broadcast($broadcastEvent);

        Log::info('Message broadcast sent', [
            'chat_id' => $chat->id,
            'message_id' => $message->id,
            'channel' => 'chat.' . $chat->id,
            'user_id' => $user->id
        ]);

        return response()->json([
            'message' => $message->load('user')
        ]);
    }

    public function getMessages(Chat $chat): JsonResponse
    {
        $user = Auth::user();

        if (!$this->userCanAccessChat($user, $chat)) {
            abort(403);
        }

        $messages = ChatMessage::with('user')
            ->where('chat_id', $chat->id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark unread messages as read by current user
        ChatMessage::where('chat_id', $chat->id)
            ->where('user_id', '!=', $user->id) // Not sent by current user
            ->whereNull('read_at') // Not read yet
            ->update([
                'read_at' => now(),
                'read_by' => $user->id
            ]);

        return response()->json([
            'messages' => $messages
        ]);
    }

    public function getUserChats(): JsonResponse
    {
        $user = Auth::user();

        $chats = Chat::with(['user', 'recipient', 'lastMessage'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('recipient_id', $user->id);
            })
            ->latest('updated_at')
            ->get();

        return response()->json([
            'chats' => $chats
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
            'users' => $users
        ]);
    }

    public function markAsRead(Chat $chat): JsonResponse
    {
        $user = Auth::user();

        if (!$this->userCanAccessChat($user, $chat)) {
            abort(403);
        }

        // Mark all unread messages in this chat as read by current user
        ChatMessage::where('chat_id', $chat->id)
            ->where('user_id', '!=', $user->id) // Not sent by current user
            ->whereNull('read_at') // Not read yet
            ->update([
                'read_at' => now(),
                'read_by' => $user->id
            ]);

        return response()->json([
            'success' => true
        ]);
    }

    private function userCanAccessChat(User $user, Chat $chat): bool
    {
        return $chat->isParticipant($user) ||
            $chat->messages()->where('user_id', $user->id)->exists();
    }
}
