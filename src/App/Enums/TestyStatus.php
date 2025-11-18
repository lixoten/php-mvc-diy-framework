<?php

declare(strict_types=1);

namespace App\Enums;

enum TestyStatus: string
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
     * ✅ NEW: Get the semantic badge variant for this status.
     * This is theme-agnostic and represents the meaning of the status.
     *
     * @return string Semantic variant (success, danger, warning, info, etc.)
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::ACTIVE    => 'success',   // Green (positive)
            self::PENDING   => 'warning',   // Yellow/orange (caution)
            self::SUSPENDED => 'info',      // Blue (informational)
            self::BANNED    => 'danger',    // Red (negative)
            self::DELETED   => 'secondary', // Grey (neutral/inactive)
        };
    }

    /**
     *  Provides a resolved options array for a formatter.
     * This keeps all logic out of config files and makes it reusable and testable.
     *
     * @param mixed $value The raw value from the database (e.g., 'A', 'P')
     * @return array<string, string>
     */
    public static function getFormatterOptions(mixed $value): array
    {
        $statusEnum = self::tryFrom((string)$value);

        if ($statusEnum === null) {
            // Fallback for an unknown or invalid status value
            return [
                'label'   => htmlspecialchars((string)$value),
                'variant' => 'danger', // Use 'danger' to highlight invalid data
            ];
        }

        // Return the correct label and variant for the BadgeFormatter
        return [
            'label'   => $statusEnum->label(),
            'variant' => $statusEnum->badgeVariant(),
        ];
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

    /**
     * Returns an associative array suitable for populating an HTML <select> element.
     * The keys are the enum values, and the values are their human-readable labels.
     *
     * @return array<string, string>
     */
    public static function toSelectArray(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }

    /**
     * Returns an array of all enum values.
     * Useful for validation (e.g., 'in_array' rule).
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
