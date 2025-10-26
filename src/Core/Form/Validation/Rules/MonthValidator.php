<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Month field validator
 */
class MonthValidator extends AbstractValidator
{
    /** {@inheritdoc} */
    public function validate($value, array $options = []): ?string
    {
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        $format = 'Y-m';
        $dt = \DateTime::createFromFormat($format, (string)$value);

        // Validate Format
        if (!$dt || $dt->format($format) !== $value) {
            $options['message'] ??= $options['invalid_message'] ?? null;

            return $this->getErrorMessage($options, 'Please enter a valid month (YYYY-MM).');
        }


        // Min
        if ($error = $this->validateMinString($value, $options)) {
            return $error;
        }

        // Max
        if ($error = $this->validateMaxString($value, $options)) {
            return $error;
        }

        // // Min
        // if (isset($options['min']) && $value < $options['min']) {
        //     if (isset($options['min_message'])) {
        //         $options['message'] = $this->formatCustomMessage($options['min'], $options['min_message']);
        //     }

        //     return $this->getErrorMessage($options, 'Month must not be before ' . $options['min'] . '.');
        // }

        // // Max
        // if (isset($options['max']) && $value > $options['max']) {
        //     if (isset($options['max_message'])) {
        //         $options['message'] = $this->formatCustomMessage($options['max'], $options['max_message']);
        //     }

        //     return $this->getErrorMessage($options, 'Month must not be after ' . $options['max'] . '.');
        // }

        return null;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'month';
    }

    /** {@inheritdoc} */
    protected function getDefaultOptions(): array
    {
        return [
        ];
    }
}
