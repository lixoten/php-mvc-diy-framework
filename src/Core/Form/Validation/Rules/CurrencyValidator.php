<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Currency validator service
 *
 * Validates currency values (decimal, positive, within range, and optionally currency code).
 */
class CurrencyValidator extends AbstractValidator
{
    /**
     * @param mixed $value
     * @param array<string, mixed> $options
     * @return string|null
     */
    public function validate($value, array $options = []): ?string
    {
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        // Check for valid decimal
        if (!is_numeric($value)) {
            $options['message'] ??= $options['invalid_message'] ?? null;
            return $this->getErrorMessage($options, 'Please enter a valid currency amount.');
        }

        $amount = (float)$value;

        if (!empty($options['positive_only']) && $amount < 0) {
            $options['message'] ??= $options['positive_message'] ?? null;
            return $this->getErrorMessage($options, 'Currency amount must be zero or positive.');
        }

        // // Must be positive
        // if ($amount < 0) {
        //     return $this->getErrorMessage($options, 'Currency amount must be positive.');
        // }

        // Min/max checks
        if (isset($options['min']) && $amount < $options['min']) {
            $options['message'] ??= $options['min_message'] ?? null;
            return $this->getErrorMessage($options, "Amount must be at least {$options['min']}.");
        }
        if (isset($options['max']) && $amount > $options['max']) {
            $options['message'] ??= $options['max_message'] ?? null;
            return $this->getErrorMessage($options, "Amount must not exceed {$options['max']}.");
        }

        // Optional: allowed currencies
        if (isset($options['allowed_currencies']) && is_array($options['allowed_currencies'])) {
            $currency = $options['currency'] ?? null;
            if ($currency && !in_array($currency, $options['allowed_currencies'], true)) {
                $options['message'] ??= $options['currency_message'] ?? null;
                return $this->getErrorMessage($options, 'Currency type not allowed.');
            }
        }

        // Optional: enforce two decimal places
        if (isset($options['enforce_decimals']) && $options['enforce_decimals'] === true) {
            if (round($amount, 2) != $amount) {
                $options['message'] ??= $options['decimals_message'] ?? null;
                return $this->getErrorMessage($options, 'Amount must have at most two decimal places.');
            }
        }

        return null;
    }

    public function getName(): string
    {
        return 'currency';
    }

        protected function getDefaultOptions(): array
    {
        DebugRt:j('1', '', 'boom');
        return [
            'required'  => null,
            'minlength'         => null,
            'maxlength'         => null,
            'pattern'           => null,

            'forbidden_words'    => ['1234', 'password'],
            'require_digit'     => false,
            'require_uppercase' => false,
            'require_lowercase' => false,
            'require_special'   => false,
        ];
    }
}