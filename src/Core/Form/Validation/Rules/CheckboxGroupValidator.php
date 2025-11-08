<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Validator for checkbox group fields
 */
class CheckboxGroupValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, array $options = []): ?string
    {
        $minChoices = $options['min_choices'] ?? 0;
        $maxChoices = $options['max_choices'] ?? null;

        if (!is_array($value)) {
            return $options['message'] ?? 'Invalid checkbox group value.';
        }

        $count = count($value);

        if ($minChoices > 0 && $count < $minChoices) {
            return $options['message'] ?? "Please select at least {$minChoices} option(s).";
        }

        if ($maxChoices !== null && $count > $maxChoices) {
            return $options['message'] ?? "Please select no more than {$maxChoices} option(s).";
        }

        return null;
    }

        /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'checkbox_group';
    }

    protected function getDefaultOptions(): array
    {
        return [
        ];
    }
}
