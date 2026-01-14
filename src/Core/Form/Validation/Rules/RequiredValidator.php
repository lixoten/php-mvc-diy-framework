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
        $defaults = $this->getDefaultOptions();

        $options = array_merge($defaults, $options);

        // Check if value is empty - NOTE: We don't use shouldSkipValidation here
        // since empty check is the actual validation logic for this validator
        if ($value === null || $value === '' || (is_array($value) && count($value) === 0)) {
            $options['message'] ??= $options['required_message'] ?? null;
            return $this->getErrorMessage($options, 'validation.required');
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

    protected function getDefaultOptions(): array
    {
        return [
            'required' => null,
        ];
    }
}
