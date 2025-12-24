<?php

namespace App;

enum UserRole: string
{
    case Admin = 'admin';
    case User = 'user';
    case Operator = 'operator';
    case Support = 'support';
    case Merchant = 'merchant';

    /**
     * Get all role values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get role label
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::User => 'User',
            self::Operator => 'Operator',
            self::Support => 'Support',
            self::Merchant => 'Merchant',
        };
    }

    /**
     * Check if role is operator or support
     */
    public function isOperatorOrSupport(): bool
    {
        return $this === self::Operator || $this === self::Support;
    }
}
