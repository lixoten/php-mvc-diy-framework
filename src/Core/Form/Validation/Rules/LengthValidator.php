<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Length validator
 */
class LengthValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, array $options = []): ?string
    {
        // Skip validation if value is empty
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        $length = is_array($value) ? count($value) : mb_strlen((string)$value);

        // Check min length
        if (isset($options['min']) && $length < $options['min']) {
            return $this->getErrorMessage(
                $options,
                $options['minMessage'] ?? "This value should be at least {$options['min']} characters long."
            );
        }

        // Check max length
        if (isset($options['max']) && $length > $options['max']) {
            return $this->getErrorMessage(
                $options,
                $options['maxMessage'] ?? "This value should not exceed {$options['max']} characters."
            );
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
