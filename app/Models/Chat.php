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
 * @property int|null $recipient_id
 * @property bool $is_new
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property User $user
 * @property User|null $recipient
 * @property ChatMessage $lastMessage
 * @property Collection<int, ChatMessage> $messages
 */
class Chat extends Model
{
    protected $fillable = [
        'user_id',
        'recipient_id',
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

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class)->latest('id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Get the other participant in this chat (not the current user)
     */
    public function getOtherParticipant(User $currentUser): ?User
    {
        if ($this->user_id === $currentUser->id) {
            return $this->recipient;
        } elseif ($this->recipient_id === $currentUser->id) {
            return $this->user;
        }

        return null;
    }

    /**
     * Check if user is participant in this chat
     */
    public function isParticipant(User $user): bool
    {
        return $this->user_id === $user->id || $this->recipient_id === $user->id;
    }
}
