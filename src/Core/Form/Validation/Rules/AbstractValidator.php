<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

use Core\Form\Validation\ValidatorInterface;

/**
 * Base validator with common functionality for all validators
 */
abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * Check if validation should be skipped for empty values
     * (typically used for non-required fields)
     */
    protected function shouldSkipValidation($value): bool
    {
        return $value === null || $value === '' || (is_array($value) && count($value) === 0);
    }

    /**
     * Get error message from options or use default
     */
    protected function getErrorMessage(array $options, string $defaultMessage): string
    {
        return $options['message'] ?? $defaultMessage;
    }

    // /**
    //  * ???
    //  */
    // // protected function formatCustomMessage(string $message, string $value = null): string
    // protected function formatCustomMessage(string $value, string $message): string
    // {
    //     return str_replace('___', (string) $value, $message);
    // }





    /**
     * Warn if unknown options are passed to the validator.
     *
     * @param array<string, mixed> $options
     * @param array<string, mixed> $defaults
     * @param string $validatorName
     * @return void
     */
    protected function warnUnknownOptions(array $options, array $defaults, string $validatorName): void
    {
        $unknown = [];
        foreach ($options as $key => $val) {
            if (!array_key_exists($key, $defaults)) {
                $unknown[] = $key;
            }
        }
        if (!empty($unknown)) {
            if ($_ENV['APP_ENV'] === 'development') {
                $msg = "[{$validatorName}] Unknown validator options: " . implode(', ', $unknown);
                // trigger_error($msg, E_USER_WARNING);
                trigger_error($msg, E_USER_ERROR);
            }

            error_log("[{$validatorName}] Unknown validator options: " . implode(', ', $unknown));
        }
    }

    /**
     * Merge and deduplicate forbidden/allowed lists from defaults and options.
     *
     * @param array<string, mixed> $defaults
     * @param array<string, mixed> $options
     * @param string $key
     * @return void
     */
    protected function mergeUniqueList(array $defaults, array $options, string $key): array
    {
        $base  = $defaults[$key] ?? [];
        $extra = (isset($options[$key]) && is_array($options[$key])) ? $options[$key] : [];
        return array_values(array_unique(array_merge($base, $extra)));

        // if (isset($options[$key]) && is_array($options[$key])) {
        //     $defaults[$key] = array_values(array_unique(array_merge($defaults[$key] ?? [], $options[$key])));
        // }

        // return $defaults[$key];
    }

    /**
     * Each validator must implement this.
     *
     * @param mixed $value
     * @param array<string, mixed> $options
     * @return string|null
     */
    abstract public function validate($value, array $options = []): ?string;

    /** {@inheritdoc} */
    abstract public function getName(): string;


    // Todo Remove  getDefaultOptions from all Child Classes
    /**
     * Each validator should provide its default options.
     *
     * @return array<string, mixed>
     */
    abstract protected function getDefaultOptions(): array;

    ////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////

    /**
     * Validates if the value is of the expected type.
     *
     * @param mixed $value The value to validate
     * @param string $expectedType The expected type (e.g., 'string', 'integer')
     * @param array<string, mixed> $options Validation options
     * @return string|null Error message if invalid, null if valid
     */
    protected function validateType(mixed $value, string $expectedType, array $options): ?string
    {
        if (gettype($value) !== $expectedType) {
            $options['message'] = $options['invalid_message'] ?? null;
            return $this->getErrorMessage($options, "validation.invalid");
            // return $this->getErrorMessage($options, "Invalid {$expectedType} format.");
        }

        return null;
    }


    /**
     * Validates minimum length.
     *
     * @param int $length The current length of the value
     * @param array<string, mixed> $options Validation options
     * @return string|null Error message if invalid, null if valid
     */
    protected function validateMinLength(int $length, array $options): ?string
    {
        if (isset($options['minlength']) && $length < $options['minlength']) {
            $options['message'] = $options['minlength_message'] ?? null;
            return $this->getErrorMessage($options, 'validation.minlength');
        }
        return null;
    }

    /**
     * Validates maximum length.
     *
     * @param int $length The current length of the value
     * @param array<string, mixed> $options Validation options
     * @return string|null Error message if invalid, null if valid
     */
    protected function validateMaxLength(int $length, array $options): ?string
    {
        if (isset($options['maxlength']) && $length > $options['maxlength']) {
            $options['message'] = $options['maxlength_message'] ?? null;
            return $this->getErrorMessage($options, 'validation.maxlength');
        }
        return null;
    }


    /**
     * Validates if the value matches the required pattern.
     *
     * @param mixed $value The value to validate
     * @param array<string, mixed> $options Validation options
     * @return string|null Error message if invalid, null if valid
     */
    protected function validatePattern(mixed $value, array $options): ?string
    {
        if (!empty($options['pattern']) && !preg_match($options['pattern'], $value)) {
            $options['message'] = $options['pattern_message'] ?? null;
            return $this->getErrorMessage($options, 'validation.pattern');
        }
        return null;
    }


    /**
     * Validates if the value is in the allowed list.
     *
     * @param mixed $value The value to validate
     * @param array<string, mixed> $options Validation options
     * @param string $type The value type as in string, int or dec
     * @return string|null Error message if invalid, null if valid or ignored
     */
    protected function validateAllowedValues(mixed $value, array $options, string $type = 'string'): ?string
    {
        if (!empty($options['ignore_allowed'])) {
            return null; // Skip validation if flag is set
        }


        // ✅ FIX: Ensure 'allowed' key exists and is an array before processing
        $allowedValues = (isset($options['allowed']) && is_array($options['allowed'])) ? $options['allowed'] : [];

    if ($type === 'string') {
            // Convert all allowed values to strings first
            $allowedValues = array_map('strval', $allowedValues); // ✅ USE $allowedValues
            // 1. Convert the input to lowercase.
            $value = strtolower($value);
            // 2. Convert ALL allowed to lowercase for case-insensitive checking.
            $allowedValues = array_map('strtolower', $allowedValues); // ✅ USE $allowedValues
        } elseif ($type === 'int') {
            // Convert all allowed values to ints
            $allowedValues = array_map('intval', $allowedValues); // ✅ USE $allowedValues
            // 1. Convert the input to int.
            $value = (int)($value);
        } else { // 'dec' or 'float'
            // Convert all allowed values to floats
            $allowedValues = array_map('floatval', $allowedValues); // ✅ USE $allowedValues
            // 1. Convert the input to float.
            $value = (float)($value);
        }


        if (!empty($allowedValues) && !in_array($value, $allowedValues, true)) {
            $options['message'] = $options['allowed_message'] ?? null;
            return $this->getErrorMessage($options, 'validation.allowed');
        }
        return null;
    }

    /**
     * Validates if the value is not in the forbidden list.
     *
     * @param mixed $value The value to validate
     * @param array<string, mixed> $options Validation options
     * @param string $type The value type as in string, int or dec
     * @return string|null Error message if invalid, null if valid or ignored
     */
    protected function validateForbiddenValues(mixed $value, array $options, string $type = 'string'): ?string
    {
        if (!empty($options['ignore_forbidden'])) {
            return null; // Skip validation if flag is set
        }

        // ✅ FIX: Ensure 'forbidden' key exists and is an array before processing
        $forbiddenValues = (isset($options['forbidden']) && is_array($options['forbidden'])) ? $options['forbidden'] : [];


        if ($type === 'string'){
            // Convert all forbidden values to strings first
            $forbiddenValues = array_map('strval', $forbiddenValues); // ✅ USE $forbiddenValues
            // 1. Convert the input to lowercase.
            $value = strtolower($value);
            // 2. Convert ALL forbidden to lowercase for case-insensitive checking.
            $forbiddenValues = array_map('strtolower', $forbiddenValues); // ✅ USE $forbiddenValues
        } elseif ($type === 'int') {
            // Convert all forbidden values to ints
            $forbiddenValues = array_map('intval', $forbiddenValues); // ✅ USE $forbiddenValues
            // 1. Convert the input to int.
            $value = (int)($value);
        } else { // 'dec' or 'float'
            // Convert all forbidden values to floats
            $forbiddenValues = array_map('floatval', $forbiddenValues); // ✅ USE $forbiddenValues
            // 1. Convert the input to float.
            $value = (float)($value);
        }

        // ✅ CORRECTED FIX: Use $forbiddenValues for the check
        if (!empty($forbiddenValues) && in_array($value, $forbiddenValues, true)) {
            $options['message'] = $options['forbidden_message'] ?? null;
            return $this->getErrorMessage($options, 'validation.forbidden');
        }
        return null;

    }


    /**
     * Validates minimum numeric value.
     *
     * @param int|float $value The numeric value to validate
     * @param array<string, mixed> $options Validation options
     * @return string|null Error message if invalid, null if valid
     */
    protected function validateMinNumeric(int|float $value, array $options): ?string
    {
        if (isset($options['min']) && $value < $options['min']) {
            $options['message'] = $options['min_message'] ?? null;
            return $this->getErrorMessage($options, 'validation.min');
        }
        return null;
    }

    /**
     * Validates maximum numeric value.
     *
     * @param int|float $value The numeric value to validate
     * @param array<string, mixed> $options Validation options
     * @return string|null Error message if invalid, null if valid
     */
    protected function validateMaxNumeric(int|float $value, array $options): ?string
    {
        if (isset($options['max']) && $value > $options['max']) {
            $options['message'] = $options['max_message'] ?? null;
            return $this->getErrorMessage($options, 'validation.max');
        }
        return null;
    }

    /**
     * Validates minimum date value.
     *
     * @param \DateTime $value The date value to validate
     * @param array<string, mixed> $options Validation options
     * @return string|null Error message if invalid, null if valid
     */
    protected function validateMinDate(\DateTime $value, array $options): ?string
    {
        if (isset($options['min']) && $value < new \DateTime($options['min'])) {
            $options['message'] = $options['min_message'] ?? null;
            return $this->getErrorMessage($options, 'validation.min');
        }
        return null;
    }

    /**
     * Validates maximum date value.
     *
     * @param \DateTime $value The date value to validate
     * @param array<string, mixed> $options Validation options
     * @return string|null Error message if invalid, null if valid
     */
    protected function validateMaxDate(\DateTime $value, array $options): ?string
    {
        if (isset($options['max']) && $value > new \DateTime($options['max'])) {
            $options['message'] = $options['max_message'] ?? null;
            return $this->getErrorMessage($options, 'validation.max');
        }
        return null;
    }



    protected function validateMinString(string $value, array $options): ?string
    {

        if (isset($options['min']) && $value < $options['min']) {
            $options['message'] = $options['min_message'] ?? null;
            return $this->getErrorMessage($options, 'validation.min');
        }

        return null;
    }


    protected function validateMaxString(string $value, array $options): ?string
    {
        if (isset($options['max']) && $value > $options['max']) {
            $options['message'] = $options['max_message'] ?? null;
            return $this->getErrorMessage($options, 'validation.max');
        }
        return null;
    }


    /**
     * Validates if the submitted value exists as a key in the provided choices array.
     * This is typically used for select, radio_group, and checkbox_group fields.
     *
     * @param mixed $value The value to validate.
     * @param array<string, mixed> $options Validation options, expected to contain 'choices' array.
     * @return string|null Error message if invalid, null if valid.
     */
    protected function validateAgainstChoices(mixed $value, array $options): ?string
    {
        $choices = $options['choices'] ?? [];

        // If no choices are provided, and no value is submitted, it's valid (or required validator will catch it).
        // If choices are expected but not provided, this might indicate a configuration error,
        // but for validation, we'll only check if the submitted value is in the (potentially empty) choices.
        if (empty($choices) && ($value === null || $value === '')) {
            return null;
        }

        // Ensure value is scalar for array_key_exists.
        if (!is_scalar($value)) {
            $options['message'] = $options['invalid_message'] ?? null;
            return $this->getErrorMessage($options, 'validation.invalid');
        }

        if (!array_key_exists((string) $value, $choices)) {
            $options['message'] = $options['invalid_message'] ?? null;
            return $this->getErrorMessage($options, 'validation.invalid');
        }

        return null;
    }
}
