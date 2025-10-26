<?php

declare(strict_types=1);

namespace Core\Formatters;

/**
 * Contract for all formatter strategies
 *
 * This interface defines the common contract that all formatter implementations
 * must follow, enabling the Strategy pattern for field formatting.
 */
interface FormatterInterface
{
    /**
     * Format the given value according to the formatter's strategy
     *
     * @param mixed $value The value to format
     * @param array<string, mixed> $options Additional formatting options
     * @return string The formatted value
     */
    public function format(mixed $value, array $options = []): string;

    /**
     * Sanitizes a value according to the formatter's rules, preparing it for storage or validation.
     *
     * @param mixed $value The value to sanitize.
     * @return string The sanitized value.
     */
    public function sanitize(mixed $value): string;


    /**
     * Get the unique name identifier for this formatter
     *
     * @return string The formatter name
     */
    public function getName(): string;

    /**
     * Check if this formatter supports the given value type
     *
     * @param mixed $value The value to check
     * @return bool True if the formatter can handle this value
     */
    public function supports(mixed $value): bool;
}
