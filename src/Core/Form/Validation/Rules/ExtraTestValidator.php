<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * ExtraTest validator service
 *
 * Validates integer or decimal numbers based on options.
 * Supports min/max, allowed/forbidden values, positive/negative checks, zero allowed, and step/increment.
 *
 * Options:
 * - forbidden
 *  - forbidden_message
 *
 * @param mixed $value
 * @param array<string, mixed> $options
 * @return string|null
 */
class ExtraTestValidator extends AbstractValidator
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

        // Forbidden values
        if (!empty($options['forbidden'])) {
            $forbiddenArray = array_map('strval', $options['forbidden']);
            if (in_array($value, $forbiddenArray, true)) {
                $options['message'] ??= $options['forbidden_message'] ?? null;

                return $this->getErrorMessage($options, 'This ExtraTest Value is not allowed.');
            }
        }

        return null;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'extratest';
    }

    /** {@inheritdoc} */
    protected function getDefaultOptions(): array
    {
        return [];
    }
}
