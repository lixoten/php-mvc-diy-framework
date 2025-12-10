<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Validator for datetime-local input fields (Y-m-d\TH:i or Y-m-d\TH:i:s)
 */
class DateTimeValidator extends AbstractValidator
{
    /** {@inheritdoc} */
    public function validate($value, array $options = []): ?string
    {
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        // HTML5 datetime-local: Y-m-d\TH:i or Y-m-d\TH:i:s
        $formats = ['Y-m-d\TH:i', 'Y-m-d\TH:i:s'];
        $valid = false;
        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat($format, (string)$value);
            if ($dt && $dt->format($format) === $value) {
                $valid = true;
                break;
            }
        }

        // Validate Format
        if (!$valid) {
            $options['message'] ??= $options['invalid_message'] ?? null;

            return $this->getErrorMessage(
                $options,
                'Please enter a valid date and time (YYYY-MM-DDTHH:MM or YYYY-MM-DDTHH:MM:SS).'
            );
        }

        // Min
        if (isset($options['min']) && $value < $options['min']) {
            return $this->getErrorMessage($options, 'validation.min');
        }

        // Max
        if (isset($options['max']) && $value > $options['max']) {
            return $this->getErrorMessage($options, 'validation.max');
        }

        return null;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'datetime';
    }

    /** {@inheritdoc} */
    protected function getDefaultOptions(): array
    {
        return [
            'required' => null,
            'min'      => null,
            'max'      => null,
        ];
    }
}
