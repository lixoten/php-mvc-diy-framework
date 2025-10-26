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

    /**
     * ???
     */
    // protected function formatCustomMessage(string $message, string $value = null): string
    protected function formatCustomMessage(string $value, string $message): string
    {
        return str_replace('___', (string) $value, $message);
    }





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
            $options['message'] ??= $options['invalid_message'] ?? null;
            return $this->getErrorMessage($options, "Invalid {$expectedType} format.");
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
            if (isset($options['minlength_message'])) {
                $options['message'] = $this->formatCustomMessage(
                    (string)$options['minlength'],
                    $options['minlength_message']
                );
            }
            return $this->getErrorMessage($options, "Must be at least {$options['minlength']} characters.");
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
            if (isset($options['maxlength_message'])) {
                $options['message'] = $this->formatCustomMessage(
                    (string)$options['maxlength'],
                    $options['maxlength_message']
                );
            }
            return $this->getErrorMessage($options, "Must not exceed {$options['maxlength']} characters.");
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
            $options['message'] ??= $options['pattern_message'] ?? null;
            return $this->getErrorMessage($options, 'Does not match the required pattern.');
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

        if ($type === 'string'){
            // Convert all forbidden values to strings first
            $options['allowed'] = array_map('strval', $options['allowed']);

            // 1. Convert the input to lowercase.
            $value = strtolower($value);

            // 2. Convert ALL allowed to lowercase for case-insensitive checking.
            $options['allowed'] = array_map('strtolower', $options['allowed']);
        } elseif ($type === 'int') {
            // Convert all allowed values to ints
            $options['allowed'] = array_map('intval', $options['allowed']);

            // 1. Convert the input to int.
            $value = (int)($value);
        } else {
            // Convert all allowed values to floats
            $options['allowed'] = array_map('floatval', $options['allowed']);

            // 1. Convert the input to lowercase.
            $value = (float)($value);
        }


        if (!empty($options['allowed']) && !in_array($value, $options['allowed'], true)) {
            $options['message'] ??= $options['allowed_message'] ?? null;

            return $this->getErrorMessage($options, 'Please select a valid value.');
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

        if ($type === 'string'){
            // Convert all forbidden values to strings first
            $options['forbidden'] = array_map('strval', $options['forbidden']);

            // 1. Convert the input to lowercase.
            $value = strtolower($value);

            // 2. Convert ALL allowed to lowercase for case-insensitive checking.
            $options['forbidden'] = array_map('strtolower', $options['forbidden']);
        } elseif ($type === 'int') {
            // Convert all forbidden values to ints
            $options['forbidden'] = array_map('intval', $options['forbidden']);

            // 1. Convert the input to int.
            $value = (int)($value);
        } else {
            // Convert all forbidden values to floats
            $options['forbidden'] = array_map('floatval', $options['forbidden']);

            // 1. Convert the input to lowercase.
            $value = (float)($value);
        }



        if (!empty($options['forbidden']) && in_array($value, $options['forbidden'], true)) {
            $options['message'] ??= $options['forbidden_message'] ?? null;

            return $this->getErrorMessage($options, 'This value is not allowed.');
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
            if (isset($options['min_message'])) {
                $options['message'] = $this->formatCustomMessage((string)$options['min'], $options['min_message']);
            }
            return $this->getErrorMessage($options, "Value must be at least {$options['min']}.");
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
            if (isset($options['max_message'])) {
                $options['message'] = $this->formatCustomMessage((string)$options['max'], $options['max_message']);
            }
            return $this->getErrorMessage($options, "Value must not exceed {$options['max']}.");
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
            if (isset($options['min_message'])) {
                $options['message'] = $this->formatCustomMessage($options['min'], $options['min_message']);
            }
            return $this->getErrorMessage($options, 'Date must not be before ' . $options['min'] . '.');
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
            if (isset($options['max_message'])) {
                $options['message'] = $this->formatCustomMessage($options['max'], $options['max_message']);
            }
            return $this->getErrorMessage($options, 'Date must not be after ' . $options['max'] . '.');
        }
        return null;
    }



    protected function validateMinString(string $value, array $options): ?string
    {

        if (isset($options['min']) && $value < $options['min']) {
            if (isset($options['min_message'])) {
                $options['message'] = $this->formatCustomMessage($options['min'], $options['min_message']);
            }

            return $this->getErrorMessage($options, "Value must be at least {$options['min']}.");
        }

        return null;
    }


    protected function validateMaxString(string $value, array $options): ?string
    {
        if (isset($options['max']) && $value > $options['max']) {
            if (isset($options['max_message'])) {
                $options['message'] = $this->formatCustomMessage($options['max'], $options['max_message']);
            }

            return $this->getErrorMessage($options, "Value must not exceed {$options['max']}.");
        }
        return null;
    }
}
