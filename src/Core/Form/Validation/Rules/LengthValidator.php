<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

use Core\Form\Validation\ValidatorInterface;

/**
 * Length validator
 */
class LengthValidator implements ValidatorInterface
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

        $length = is_array($value) ? count($value) : mb_strlen((string)$value);

        // Check min length
        if (isset($options['min']) && $length < $options['min']) {
            return $options['minMessage'] ??
                   $options['message'] ??
                   "This value should be at least {$options['min']} characters long.";
        }

        // Check max length
        if (isset($options['max']) && $length > $options['max']) {
            return $options['maxMessage'] ??
                   $options['message'] ??
                   "This value should not exceed {$options['max']} characters.";
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'length';
    }
}
