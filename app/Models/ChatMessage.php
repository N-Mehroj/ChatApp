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
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
