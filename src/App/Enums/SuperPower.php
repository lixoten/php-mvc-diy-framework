<?php

declare(strict_types=1);

namespace App\Enums;

enum SuperPower: string
{
    case FLIGHT       = 'flight';
    case STRENGTH     = 'strength';
    case INVISIBILITY = 'invisibility';
    case TELEPATHY    = 'telepathy';
    case SPEED        = 'speed';
    case TELEKINESIS  = 'telekinesis';
    case OPTIONA      = 'optiona';
    case OPTIONB      = 'optionb';
    case OPTIONC      = 'optionc';

    public function label(): string
    {
        return match ($this) {
            self::FLIGHT       => 'Flight',
            self::STRENGTH     => 'Super Strength',
            self::INVISIBILITY => 'Invisibility',
            self::TELEPATHY    => 'Telepathy',
            self::SPEED        => 'Super Speed',
            self::TELEKINESIS  => 'Telekinesis',
            self::OPTIONA      => 'OPTION A',
            self::OPTIONB      => 'OPTION B',
            self::OPTIONC      => 'OPTION CCC',
        };
    }

    public function code(): string
    {
        return match ($this) {
            self::FLIGHT       => 'flight',
            self::STRENGTH     => 'strength',
            self::INVISIBILITY => 'invisibility',
            self::TELEPATHY    => 'telepathy',
            self::SPEED        => 'speed',
            self::TELEKINESIS  => 'telekinesis',
            self::OPTIONA      => 'optiona',
            self::OPTIONB      => 'optionb',
            self::OPTIONC      => 'optionc',
        };
    }

    /**
     * Get translation key for i18n
     */
    public function translationKey(): string
    {
        return match ($this) {
            self::FLIGHT       => 'code.super_powers.flight',
            self::STRENGTH     => 'code.super_powers.strength',
            self::INVISIBILITY => 'code.super_powers.invisibility',
            self::TELEPATHY    => 'code.super_powers.telepathy',
            self::SPEED        => 'code.super_powers.speed',
            self::TELEKINESIS  => 'code.super_powers.telekinesis',
            self::OPTIONA      => 'code.super_powers.optiona',
            self::OPTIONB      => 'code.super_powers.optionb',
            self::OPTIONC      => 'code.super_powers.optionc',
        };
    }


    /**
     * Get choices for checkbox_group (form display)
     * @return array<string, string>
     */
    public static function toSelectArray(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            // $choices[$case->value] = $case->label();
            $choices[$case->value] = $case->translationKey();
        }
        return $choices;
    }

    // /**
    //  * Get formatter options for list display
    //  * @param string|null $value
    //  * @return array{label: string, variant: string}
    //  */
    // public static function getFormatterOptions(string|null $value): array
    public static function getFormatterOptions(string|null $value, array $context = []): array
    {
        // ✅ Guard against null (defensive programming)
        if ($value === null) {
            return [
                'label' => 'code.unknown',
                'variant' => 'secondary',
            ];
        }

        // $enum = self::tryFrom((string)$value);
        $enum = self::tryFrom($value);

        return [
            'label' => $enum?->translationKey() ?? 'code.unknown',
            'variant' => match ($enum) {
                self::FLIGHT => 'info',
                self::STRENGTH => 'danger',
                self::INVISIBILITY => 'secondary',
                self::TELEPATHY => 'warning',
                self::SPEED => 'success',
                self::TELEKINESIS => 'primary',
                self::OPTIONA => 'primary',
                self::OPTIONB => 'primary',
                self::OPTIONC => 'primary',
                default => 'secondary',
            },
        ];
    }
}
