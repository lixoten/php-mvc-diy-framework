<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Numeric range validator
 */
class RangeValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
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
        if (!is_numeric($value)) {
            $options['message'] ??= $options['invalid_message'] ?? null;
            return $this->getErrorMessage($options, 'This value must be a number.');
        }
        // // Type validation
        // if ($error = $this->validateType($value, 'integer', $options)) {
        //     return $error;
        // }


        $num = (float)$value;

        // Min check
        if ($error = $this->validateMinNumeric($num, $options)) {
            return $error;
        }

        // Max check
        if ($error = $this->validateMaxNumeric($num, $options)) {
            return $error;
        }

        // $options['enforce_step'] = true;
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

                    return $this->getErrorMessage($options, 'validation.enforce_step_message');
                }
            }
        }



        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'range';
    }

        protected function getDefaultOptions(): array
    {
        return [
        ];
    }
}
