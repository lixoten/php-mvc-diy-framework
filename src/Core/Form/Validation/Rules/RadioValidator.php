<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Radio field validator
 */
class RadioValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, array $options = []): ?string
    {
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        // // Validate hex color format (#RRGGBB)
        // if (!is_string($value) || !preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
        //         $options['message'] ??= $options['invalid_message'] ?? null;
        //     return $this->getErrorMessage($options, 'Please select a valid color (e.g., #FF5733).');
        // }

        // // Allowed values
        // if ($error = $this->validateAllowedValues($value, $options)) {
        //     return $error;
        // }

        // // Forbidden values
        // if ($error = $this->validateForbiddenValues($value, $options)) {
        //     return $error;
        // }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'radio';
    }

    protected function getDefaultOptions(): array
    {
        return [
        ];
    }
}
