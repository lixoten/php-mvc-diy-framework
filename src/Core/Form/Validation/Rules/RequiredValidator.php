<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Required field validator
 */
class RequiredValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, array $options = []): ?string
    {
        // Check if value is empty - NOTE: We don't use shouldSkipValidation here
        // since empty check is the actual validation logic for this validator
        if ($value === null || $value === '' || (is_array($value) && count($value) === 0)) {
            return $this->getErrorMessage($options, 'This field is required.');
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
