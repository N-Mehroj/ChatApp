<?php

namespace App\Models;

use App\Enums\User\UserSourceEnum;
use App\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $middle_name
 * @property string $email
 * @property string $phone
 * @property int $status
 * @property string $last_visit
 * @property string $sms_code
 * @property string $code_expiry_date
 * @property int $code_sent_at Дата отправки сообщения (Unix Timestamp)
 * @property string $password
 * @property int $department_id
 * @property string $position
 * @property string $image
 * @property int $organization_id
 * @property int $telegram_id
 * @property string $telegram_token Токен для идентификации юзеров через Telegram (два и более аккаунта в ТГ === один аккаунт в БД)
 * @property string $telegram_auth_token Токен для авторизации через Telegram
 * @property array $additional_telegram_ids
 * @property UserSourceEnum $source
 * @property string $external_id
 * @property string $username
 * @property string $uuid
 * @property bool $is_app_installed
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Chat $chat
 * @property Merchant $merchant
 * @property Collection<int, ChatMessage> $messages
 * @property Department $department
 * @property Organization $organization
 * @property Collection<int, Story> $stories
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'phone',
        'status',
        'last_visit',
        'sms_code',
        'code_expiry_date',
        'code_sent_at',
        'password',
        'department_id',
        'position',
        'image',
        'organization_id',
        'telegram_id',
        'telegram_token',
        'telegram_auth_token',
        'additional_telegram_ids',
        'email_verified_at',
        'source',
        'external_id',
        'username',
        'uuid',
        'is_app_installed',
        'last_activity',
        'api_key',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'display_name',
        'name', // For compatibility with frontend
        'avatar_url',
        'is_online',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (User $user) {
            if (empty($user->password)) {
                $user->password = Hash::make(Str::random(12));
            }

            $user->phone = str_replace(['-', ' ', '+'], '', $user->phone);
            $user->telegram_token = bin2hex(random_bytes(24));
        });

        static::deleting(function (User $user): void {
            $user->messages()->delete();
            $user->chat()->delete();
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'telegram_id' => 'int',
            'additional_telegram_ids' => 'array',
            'role' => UserRole::class,
            'last_activity' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function chat(): HasOne
    {
        return $this->hasOne(Chat::class);
    }

    public function merchant(): HasOne
    {
        return $this->hasOne(Merchant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Merchant::class);
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Check if user is an operator
     */
    public function isOperator(): bool
    {
        return $this->role === UserRole::Operator;
    }

    /**
     * Check if user is support
     */
    public function isSupport(): bool
    {
        return $this->role === UserRole::Support;
    }

    /**
     * Get display name for user
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return trim($this->first_name.' '.$this->last_name);
        }

        if ($this->first_name) {
            return $this->first_name;
        }

        if ($this->username) {
            return $this->username;
        }

        return $this->email ?: 'Unknown User';
    }

    /**
     * Get name attribute (alias for display_name for compatibility)
     */
    public function getNameAttribute(): string
    {
        return $this->getDisplayNameAttribute();
    }

    /**
     * Get avatar URL for user
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/'.$this->image);
        }

        $name = urlencode($this->display_name);

        return "https://ui-avatars.com/api/?name={$name}&background=3b82f6&color=fff&size=32";
    }

    /**
     * Check if user is online (active in last 5 minutes)
     */
    public function getIsOnlineAttribute(): bool
    {
        if (! $this->last_activity) {
            return false;
        }

        // Ensure last_activity is a Carbon instance
        $lastActivity = $this->last_activity instanceof Carbon
            ? $this->last_activity
            : Carbon::parse($this->last_activity);

        return $lastActivity->gt(now()->subMinutes(5));
    }

    /**
     * Check if user is a merchant
     */
    public function isMerchant(): bool
    {
        return $this->role === UserRole::Merchant;
    }

    /**
     * Check if user is operator or support
     */
    public function isOperatorOrSupport(): bool
    {
        return $this->role && $this->role->isOperatorOrSupport();
    }

    /**
     * Scope query to only operators
     */
    public function scopeOperators(Builder $query): Builder
    {
        return $query->where('role', UserRole::Operator->value);
    }

    /**
     * Scope query to only support users
     */
    public function scopeSupport(Builder $query): Builder
    {
        return $query->where('role', UserRole::Support->value);
    }

    /**
     * Scope query to operators and support users
     */
    public function scopeOperatorsAndSupport(Builder $query): Builder
    {
        return $query->whereIn('role', [UserRole::Operator->value, UserRole::Support->value]);
    }
}
