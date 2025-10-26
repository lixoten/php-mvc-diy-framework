<?php

declare(strict_types=1);

namespace Core\Formatters;

/**
 * Truncate string to 5 characters, appending "..." if longer.
 */
class Truncate5Formatter extends AbstractFormatter
{
    public function supports(mixed $value): bool
    {
        return is_string($value) || is_numeric($value) || $value === null;
    }

    public function transform(mixed $value, array $options = []): string
    {
        $text = (string)($value ?? '');
        return mb_strimwidth($text, 0, 5, '...');
    }

    public function getName(): string
    {
        return 'truncate5';
    }

    // public function sanitize(mixed $value): string
    // {
    //     return trim((string)($value ?? ''));
    // }
}
