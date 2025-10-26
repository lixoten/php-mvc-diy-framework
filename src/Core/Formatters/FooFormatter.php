<?php

declare(strict_types=1);

namespace Core\Formatters;

/**
 * Simple proof-of-concept formatter that appends "-foo" to any value
 * Used to demonstrate the Strategy Pattern implementation
 */
class FooFormatter extends AbstractFormatter
{
    public function getName(): string
    {
        return 'foo';
    }

    public function supports(mixed $value): bool
    {
        // Supports any value type for simplicity
        return true;
    }

    public function transform(mixed $value, array $options = []): string
    {
        $options = $this->mergeOptions($options);

        // Handle null values
        if ($value === null) {
            return $options['null_value'];
        }

        // Convert value to string and sanitize
        $text = $this->sanitize($value);

        // Append the suffix
        return $text . $options['suffix'];
    }

    protected function getDefaultOptions(): array
    {
        return [
            'suffix' => '-foo',
            'null_value' => 'null-foo'
        ];
    }
}
