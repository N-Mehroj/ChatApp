<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $chat_id
 * @property int $user_id
 * @property bool $from_operator
 * @property string $message
 * @property int $message_id ID сообщения из Telegram
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Chat $chat
 * @property User $user
 */
class ChatMessage extends Model
{
    protected $fillable = [
        'chat_id',
        'user_id',
        'from_operator',
        'message',
        'message_id',
        'read_at',
        'read_by',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function readBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    public function isReadBy(User $user): bool
    {
        return $this->read_at !== null && $this->read_by === $user->id;
    }
}
