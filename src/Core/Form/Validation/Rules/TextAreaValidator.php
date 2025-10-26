<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * TextArea validator service
 *
 * Validates general text fields (e.g., names, comments, etc.).
 */
class TextAreaValidator extends AbstractValidator
{
    /** {@inheritdoc} */
    public function validate($value, array $options = []): ?string
    {
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        // Type validation
        if ($error = $this->validateType($value, 'string', $options)) {
            return $error;
        }

        $length = mb_strlen($value);

        // Min length
        if ($error = $this->validateMinLength($length, $options)) {
            return $error;
        }

        // Max length
        if ($error = $this->validateMaxLength($length, $options)) {
            return $error;
        }

        // Pattern
        if ($error = $this->validatePattern($value, $options)) {
            return $error;
        }

        // Allowed values
        if ($error = $this->validateAllowedValues($value, $options)) {
            return $error;
        }

        // Forbidden values
        if ($error = $this->validateForbiddenValues($value, $options)) {
            return $error;
        }


        return null;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'textarea';
    }

    /** {@inheritdoc} */
    protected function getDefaultOptions(): array
    {
        return [];
    }
}
