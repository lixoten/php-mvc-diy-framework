<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

use Core\Form\Validation\ValidatorInterface;

/**
 * Email validator
 */
class EmailValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, array $options = []): ?string
    {
        // Skip validation if value is empty
        if ($value === null || $value === '') {
            return null;
        }

        // Validate email
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return $options['message'] ?? 'Please enter a valid email address.';
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'email';
    }
}
