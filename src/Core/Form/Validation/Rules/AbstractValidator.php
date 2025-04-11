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
}
