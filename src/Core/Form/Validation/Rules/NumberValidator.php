<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Number validator service
 *
 * Validates integer or decimal numbers based on options.
 * Supports min/max, allowed/forbidden values, positive/negative checks, zero allowed, and step/increment.
 *
 * Options:
 * - value_kind: 'integer'|'decimal' (default: 'integer')
 * - min, max, step
 * - positive_only, negative_only, zero_not_allowed
 * - enforce_step
 * - Custom error messages for each rule
 *  - min_message
 *  - max_message
 *  - invalid_message
 *  - positive_only_message
 *  - negative_only_message
 *  - zero_not_allowed_message
 *  - enforce_step_message
 *
 * @param mixed $value
 * @param array<string, mixed> $options
 * @return string|null
 */
class NumberValidator extends AbstractValidator
{
    /** {@inheritdoc} */
    public function validate($value, array $options = []): ?string
    {
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        // Ensure value is a string or number and trimmed
        if (is_string($value)) {
            $value = trim($value);
        }

        // Validate number value_kind
        if (!is_numeric($value) || (int)$value != $value) {
            $options['message'] ??= $options['invalid_message'] ?? null;
            return $this->getErrorMessage($options, 'Please enter a whole number (no decimals).');
        }
        $num = (int)$value;


        // Beg of Defensive----------------------------------------------------
        // Defensive: zero_not_allowed
        if (isset($options['min']) && $options['min'] === 0) {
            $options['zero_not_allowed'] = false;
        }

        // Defensive: If both positive_only and negative_only are true, disable both
        if (!empty($options['positive_only']) && !empty($options['negative_only'])) {
            $options['positive_only'] = false;
            $options['negative_only'] = false;
        }
        // End of Defensive----------------------------------------------------


        // Positive only
        if (!empty($options['positive_only']) && $num < 0) {
            $options['message'] ??= $options['positive_only_message'] ?? null;
            return $this->getErrorMessage($options, 'Only positive numbers are allowed.');
        }

        // Negative only
        if (!empty($options['negative_only']) && $num > 0) {
            $options['message'] ??= $options['negative_only_message'] ?? null;
            return $this->getErrorMessage($options, 'Only negative numbers are allowed.');
        }

        // Zero allowed  // use loose == comparison over strict ===
        if (isset($options['zero_not_allowed']) && $options['zero_not_allowed'] && $num == 0) {
            $options['message'] ??= $options['zero_not_allowed_message'] ?? null;
            return $this->getErrorMessage($options, 'Zero is not allowed.');
        }



        // Min check
        if ($error = $this->validateMinNumeric($num, $options)) {
            return $error;
        }

        // Max check
        if ($error = $this->validateMaxNumeric($num, $options)) {
            return $error;
        }


        // Defensive: min/max checks
        if (isset($options['min'])) {
            if ($options['min'] > 0) {
                $options['positive_only'] = true;
                $options['negative_only'] = false;
            } else {
                $options['positive_only'] = false;
                $options['negative_only'] = true;
            }
        }


        // Allowed values
        if ($error = $this->validateAllowedValues($value, $options, 'int')) {
            return $error;
        }

        // Forbidden values
        if ($error = $this->validateForbiddenValues($value, $options, 'int')) {
            return $error;
        }



        // Step/increment validation
        if (
            !empty($options['enforce_step']) &&
            isset($options['step']) &&
            is_numeric($options['step']) &&
            $options['step'] > 0
        ) {
            // FIX START: Ensure step is a standard decimal string for BCMath
            // We cast to float first and then use sprintf to ensure no scientific notation
            $float_step = (float)$options['step'];
            // Use a high precision (e.g., 20) to capture all necessary decimal places
            $step = sprintf('%.20F', $float_step);
            $step = rtrim($step, '0'); // Remove unnecessary trailing zeros
            $step = rtrim($step, '.');  // Remove decimal point if it's now an integer
            $step = $step === '' ? '0' : $step; // Handle case where step was 0 (though checked > 0 above, good practice)
            // FIX END

            //$step = (string)$options['step'];
            $numStr = (string)$num;


            $div = bcdiv($numStr, $step, 10);
            if (strpos($div, '.') !== false) {
                $fraction = rtrim(substr($div, strpos($div, '.') + 1), '0');
                if ($fraction !== '' && (int)$fraction !== 0) {
                    if (isset($options['enforce_step_message'])) {
                        // $options['message'] = $this->formatCustomMessage(
                        //     (string)$options['step'],
                        //     $options['enforce_step_message']
                        // );
                        $options['message'] ??= $options['enforce_step_message'] ?? null;
                    }

                    return $this->getErrorMessage($options, "Number must be a multiple of {$step}.");
                }
            }
        }

        // // No leading zeros for integer value_kind
        // if ($value_kind === 'integer' && is_string($value) && preg_match('/^0[0-9]+$/', $value)) {
        //     $options['message'] ??= $options['leading_zeros_message'] ?? null;
        //     return $this->getErrorMessage($options, 'Leading zeros are not allowed.');
        // }

        return null;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'number';
    }

    /** {@inheritdoc} */
    protected function getDefaultOptions(): array
    {
        return [];
    }
}
