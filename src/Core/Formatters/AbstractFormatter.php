<?php

declare(strict_types=1);

namespace Core\Formatters;

/**
 * Abstract base class for formatters providing common functionality
 */
abstract class AbstractFormatter implements FormatterInterface
{

    /**
     * Format a value for output (always sanitizes as last step).
     *
     * @param mixed $value The value to format
     * @param array<string, mixed> $options Formatting options
     * @return string The sanitized, formatted value
     */
    public function format(mixed $value, array $options = []): string
    {
        $transformed = $this->transform($value, $options);

        // // If formatter declares it produces safe HTML (and handles attribute escaping),
        // // return raw HTML. Otherwise perform default sanitization.
        // if ($this->isSafeHtml()) {
        //     return (string) $raw;
        // }

        return $this->sanitize($transformed);
    }


    /**
     * Override in formatters that intentionally return safe HTML.
     *
     * @return bool
     */
    protected function isSafeHtml(): bool
    {
        return false;
    }


    /**
     * Child classes must implement transformation logic only.
     *
     * @param mixed $value
     * @param array<string, mixed> $options
     * @return string
     */
    abstract protected function transform(mixed $value, array $options = []): string;


    /**
     * Get default options for this formatter
     *
     * @return array<string, mixed> Default options
     */
    protected function getDefaultOptions(): array
    {
        return [];
    }

    /**
     * Merge provided options with defaults
     *
     * @param array<string, mixed> $options Provided options
     * @return array<string, mixed> Merged options
     */
    protected function mergeOptions(array $options): array
    {
        return array_merge($this->getDefaultOptions(), $options);
    }

    /**
     * Sanitize a value for safe output
     *
     * @param mixed $value The value to sanitize
     * @return string The sanitized value
     */
    private function sanitize(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
