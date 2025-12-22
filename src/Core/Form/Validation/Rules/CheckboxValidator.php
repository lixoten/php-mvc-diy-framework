<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Checkbox field validator
 */
class CheckboxValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, array $options = []): ?string
    {
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'checkbox';
    }

    protected function getDefaultOptions(): array
    {
        return [
        ];
    }
}
