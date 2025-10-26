<?php

declare(strict_types=1);

namespace Core\Formatters;

/**
 * Basic text formatter with truncation and sanitization
 */
class TextFormatter extends AbstractFormatter
{
    public function getName(): string
    {
        return 'text';
    }

    public function supports(mixed $value): bool
    {
        return is_string($value) || is_numeric($value) || $value === null;
    }

    public function transform(mixed $value, array $options = []): string
    {
        $options = $this->mergeOptions($options);

        if ($value === null) {
            return $options['null_value'] ?? '';
        }

        $text = (string) $value;

        // Apply truncation if specified
        if (isset($options['max_length']) && strlen($text) > $options['max_length']) {
            $text = substr($text, 0, $options['max_length']) . ($options['truncate_suffix'] ?? '...');
        }

        // Apply text transformation
        if (isset($options['transform'])) {
            $text = match ($options['transform']) {
                'uppercase' => strtoupper($text),
                'lowercase' => strtolower($text),
                'capitalize' => ucfirst(strtolower($text)),
                'title' => ucwords(strtolower($text)),
                'last2char_upper' => $this->last2CharUpper($text),
                'feefee' => $text . '-feefee',
                default => $text
            };
        }

        // Apply suffix if specified (alternative approach)
        if (isset($options['suffix'])) {
            $text .= $options['suffix'];
        }

        return $text;
    }


    /**
     * Uppercase the last 2 characters of a string.
     *
     * @param string $text
     * @return string
     */
    private function last2CharUpper(string $text): string
    {
        $len = mb_strlen($text);
        if ($len <= 2) {
            return mb_strtoupper($text);
        }
        $start = mb_substr($text, 0, $len - 2);
        $last2 = mb_substr($text, -2);
        return $start . mb_strtoupper($last2);
    }

    protected function getDefaultOptions(): array
    {
        return [
            'max_length' => null,
            'truncate_suffix' => '...',
            'null_value' => '',
            'transform' => null,
            'suffix' => null
        ];
    }
}
