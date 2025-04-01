<?php

declare(strict_types=1);

namespace App\Enums;

enum UserStatus: string
{
    case PENDING = 'P';
    case ACTIVE = 'A';
    case SUSPENDED = 'S';
    case BANNED = 'B';
    case DELETED = 'D';

    /**
     * Get human-readable label for this status
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
            self::BANNED => 'Banned',
            self::DELETED => 'Deleted',
        };
    }

    /**
     * Get all statuses as key-value pairs
     *
     * @return array<string, string> Status code => Label pairs
     */
    public static function getAll(): array
    {
        return [
            self::PENDING->value => self::PENDING->label(),
            self::ACTIVE->value => self::ACTIVE->label(),
            self::SUSPENDED->value => self::SUSPENDED->label(),
            self::BANNED->value => self::BANNED->label(),
            self::DELETED->value => self::DELETED->label(),
        ];
    }

    /**
     * Check if status is active
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}
