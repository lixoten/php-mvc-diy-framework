<?php

declare(strict_types=1);

namespace Core\Formatters;

/**
 * Formatter for decimal numbers, truncating trailing zeros and controlling decimal places.
 */
class DecimalFormatter extends AbstractFormatter
{
    public function getName(): string
    {
        return 'decimal';
    }

    public function supports(mixed $value): bool
    {
        return is_numeric($value) || $value === null;
    }

    /**
     * Format a decimal number, truncating trailing zeros.
     *
     * @param mixed $value
     * @param array<string, mixed> $options
     *      - 'decimals': int, number of decimal places to show (default: 5)
     *      - 'trim_zeros': bool, whether to trim trailing zeros (default: true)
     * @return string
     */
    public function transform(mixed $value, array $options = []): string
    {
        $options = $this->mergeOptions($options);

        if (!is_numeric($value)) {
            return '';
        }

        $valueStr = (string)$value;
        $decimalPos = strpos($valueStr, '.');
        // $decimalPos = $options['decimals'];
        $decimalPlaces = $decimalPos !== false ? strlen(substr($valueStr, $decimalPos + 1)) : 0;

        $isWholeNumber = floor((float)$value) == (float)$value;
        $decimals = $isWholeNumber ? 1 : $decimalPlaces;
        $trimZeros = $options['trim_zeros'] ?? true;


        if ($isWholeNumber) {
            // $decimals = 1;
            $trimZeros = false; // Ensure .0 is displayed
        }

        $formatted = number_format((float)$value, $decimals, '.', ',');

        if ($trimZeros) {
            $formatted = rtrim(rtrim($formatted, '0'), '.');
        }

        return $formatted;
    }

    protected function getDefaultOptions(): array
    {
        return [
            // 'decimals' => null,
            'trim_zeros' => true,
        ];
    }
}
