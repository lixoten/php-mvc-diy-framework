<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

class EmailValidator extends AbstractValidator
{
    public function validate($value, array $options = []): ?string
    {
        // Use the inherited method
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        // Validate email
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return $this->getErrorMessage($options, 'Please enter a valid email address.');
        }

        return null;
    }

    public function getName(): string
    {
        return 'email';
    }
}
