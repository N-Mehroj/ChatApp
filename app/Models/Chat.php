<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property bool $is_new
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User $user
 * @property ChatMessage $lastMessage
 * @property Collection<int, ChatMessage> $messages
 */
class Chat extends Model
{
    protected $fillable = [
        'user_id',
        'widget_session_id',
        'visitor_user_id',
        'is_new',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Chat $chat): void {
            $chat->messages()->delete();
        });
        
        // Clear cache when chat is created or updated
        static::saved(function (Chat $chat): void {
            // Clear cache for chat owner and all support users
            cache()->forget("user_chats_{$chat->user_id}_user");
            cache()->forget("user_chats_{$chat->user_id}_merchant");
            cache()->forget("user_chats_{$chat->user_id}_support");
            cache()->forget("user_chats_{$chat->user_id}_admin");
            
            // Clear cache for all support users (they can see all chats)
            $supportUsers = \App\Models\User::whereIn('role', ['support', 'admin'])->pluck('id');
            foreach ($supportUsers as $userId) {
                cache()->forget("user_chats_{$userId}_support");
                cache()->forget("user_chats_{$userId}_admin");
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class)->latest('id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function widgetSession(): BelongsTo
    {
        return $this->belongsTo(WidgetSession::class, 'widget_session_id');
    }

    /**
     * Get the customer for this support ticket
     */
    public function getCustomer(): User
    {
        return $this->user;
    }

    /**
     * Check if user can access this chat (customer or support)
     */
    public function isParticipant(User $user): bool
    {
        // Customer can access their own chat
        if ($this->user_id === $user->id) {
            return true;
        }

        // Support users can access any chat
        if ($user->isSupport()) {
            return true;
        }

        return false;
    }

    /**
     * Get unread messages count for a specific user
     */
    public function getUnreadCount(User $user): int
    {
        return $this->messages()
            ->where('user_id', '!=', $user->id) // Messages not from the user
            ->whereNull('read_at') // Not read yet
            ->count();
    }
}
