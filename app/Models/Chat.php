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
