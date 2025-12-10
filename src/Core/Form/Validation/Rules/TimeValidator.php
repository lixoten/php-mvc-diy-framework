<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Time field validator
 */
class TimeValidator extends AbstractValidator
{
    /** {@inheritdoc} */
    public function validate($value, array $options = []): ?string
    {
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        // HTML5 time format: HH:MM or HH:MM:SS
        $formats = ['H:i', 'H:i:s'];
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

            return $this->getErrorMessage($options, 'Please enter a valid time (HH:MM or HH:MM:SS).');
        }

        // // Min
        // if (isset($options['min']) && $value < $options['min']) {
        //     if (isset($options['min_message'])) {
        //         $options['message'] = $this->formatCustomMessage($options['min'], $options['min_message']);
        //     }

        //     return $this->getErrorMessage($options, 'Time must not be before ' . $options['min'] . '.');
        // }

        // // Max
        // if (isset($options['max']) && $value > $options['max']) {
        //     if (isset($options['max_message'])) {
        //         $options['message'] = $this->formatCustomMessage($options['max'], $options['max_message']);
        //     }

        //     return $this->getErrorMessage($options, 'Time must not be after ' . $options['max'] . '.');
        // }

        // Min
        if ($error = $this->validateMinString($value, $options)) {
            return $error;
        }

        // Max
        if ($error = $this->validateMaxString($value, $options)) {
            return $error;
        }

        return null;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'time';
    }

    /** {@inheritdoc} */
    protected function getDefaultOptions(): array
    {
        return [
        ];
    }
}
