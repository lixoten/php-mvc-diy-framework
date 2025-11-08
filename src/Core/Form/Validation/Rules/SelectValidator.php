<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Select field validator ccccccccccccccccccccccc
 *
 * Validates single or multiple select fields against allowed and forbidden options.
 */
class SelectValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, array $options = []): ?string
    {
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        // Handle multiple select (array of values)
        if (is_array($value)) {
            foreach ($value as $singleValue) {
                if ($error = $this->validateSingleValue($singleValue, $options)) {
                    return $error;
                }
            }
            return null;
        }

        // Handle single select
        return $this->validateSingleValue($value, $options);
    }

    /**
     * Validate a single select value
     *
     * @param mixed $value
     * @param array<string, mixed> $options
     * @return string|null
     */
    protected function validateSingleValue($value, array $options): ?string
    {
        // Type validation - must be string or numeric
        if (!is_string($value) && !is_numeric($value)) {
            $options['message'] ??= $options['invalid_message'] ?? null;
            return $this->getErrorMessage($options, 'Please select a valid option.');
        }

        // Convert to string for comparison
        $valueStr = (string)$value;

        // Allowed values check
        if ($error = $this->validateAllowedValues($valueStr, $options, 'string')) {
            return $error;
        }

        // Forbidden values check
        if ($error = $this->validateForbiddenValues($valueStr, $options, 'string')) {
            return $error;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'select';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultOptions(): array
    {
        return [];
    }
}
