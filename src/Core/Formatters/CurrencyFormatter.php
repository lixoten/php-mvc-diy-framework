<?php

declare(strict_types=1);

namespace Core\Formatters;

/**
 * Currency formatter service
 *
 * Formats currency values for display (e.g., $1,234.56).
 */
class CurrencyFormatter extends AbstractFormatter
{
    public function getName(): string
    {
        return 'currency';
    }

    public function supports(mixed $value): bool
    {
        return is_numeric($value);
    }

    /**
     * @param mixed $value
     * @param array<string, mixed> $options
     * @return string
     */
    public function transform(mixed $value, array $options = [], mixed $originalValue = null): string
    {
        $currency = $options['currency'] ?? 'USD';
        $decimals = $options['decimals'] ?? 2;

        // Map currency codes to symbols
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            // Add more as needed
        ];

        $symbol = $symbols[$currency] ?? $currency; // Fallback to code if symbol not found

        // You could use NumberFormatter for i18n, but here's a simple version:
        $formatted = number_format((float)$value, $decimals, '.', ',');

        return "{$symbol}{$formatted}";
    }
}
