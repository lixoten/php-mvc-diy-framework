<?php

declare(strict_types=1);

namespace Core\Services;

/**
 * Service to wrap and execute formatter closures with enforced sanitization.
 */
class ClosureFormatterService
{
    /**
     * Format using a closure, always escaping output.
     *
     * @param callable $closure
     * @param mixed $value
     * @param array<string, mixed> $options
     * @return string
     */
    public function format(callable $closure, mixed $value, array $options = []): string
    {
        $result = $closure($value, $options);
        // Always escape output for safety
        return $this->sanitize($result);
    }


    /**
     * Sanitize a value for safe output
     *
     * @param mixed $value The value to sanitize
     * @return string The sanitized value
     */
    final public function sanitize(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
