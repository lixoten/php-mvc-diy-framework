<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

use Core\Form\Validation\ValidatorInterface;

/**
 * Required field validator
 */
class RequiredValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, array $options = []): ?string
    {
        // Check if value is empty
        if ($value === null || $value === '' || (is_array($value) && count($value) === 0)) {
            return $options['message'] ?? 'This field is required.';
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'required';
    }
}
