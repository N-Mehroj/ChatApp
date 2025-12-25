<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WidgetSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'merchant_id',
        'user_id',
        'visitor_id',
        'visitor_name',
        'visitor_email',
        'visitor_phone',
        'visitor_notes',
        'visitor_metadata',
        'visitor_ip',
        'visitor_user_agent',
        'is_active',
        'visitor_typing',
        'last_activity',
        'last_typing_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'visitor_typing' => 'boolean',
            'visitor_metadata' => 'array',
            'last_activity' => 'datetime',
            'last_typing_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /**
     * Get the merchant that owns the session
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    /**
     * Get the user that created the session (if logged in)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the chat for this session
     */
    public function chat(): HasMany
    {
        return $this->hasMany(Chat::class);
    }

    /**
     * Get messages for this session
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Check if session is still active
     */
    public function isActive(): bool
    {
        return $this->is_active &&
            $this->last_activity &&
            $this->last_activity->diffInMinutes(now()) < 30;
    }

    /**
     * Mark session as inactive after timeout
     */
    public function checkTimeout(): void
    {
        if ($this->last_activity && $this->last_activity->diffInMinutes(now()) > 30) {
            $this->update([
                'is_active' => false,
                'ended_at' => now(),
            ]);
        }
    }
}
