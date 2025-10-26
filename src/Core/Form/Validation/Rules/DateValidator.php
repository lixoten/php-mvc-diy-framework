<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Date field validator
 */
class DateValidator extends AbstractValidator
{
    /** {@inheritdoc} */
    public function validate($value, array $options = []): ?string
    {
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        $format = $options['format'] ?? 'Y-m-d';
        $dt = \DateTime::createFromFormat($format, (string)$value);

        // Validate Format
        if (!$dt || $dt->format($format) !== $value) {
            $options['message'] ??= $options['invalid_message'] ?? null;

            return $this->getErrorMessage($options, 'Please enter a valid date.');
        }

        // Normalize all dates to midnight to ignore time
        $dt->setTime(0, 0, 0);

        // Min
        if ($error = $this->validateMinDate($dt, $options)) {
            return $error;
        }

        // Max
        if ($error = $this->validateMaxDate($dt, $options)) {
            return $error;
        }

        // // Min
        // if (isset($options['min']) && $dt < new \DateTime($options['min'])) {
        //     if (isset($options['min_message'])) {
        //         $options['message'] = $this->formatCustomMessage($options['min'], $options['min_message']);
        //     }

        //     return $this->getErrorMessage($options, 'Date must not be before ' . $options['min'] . '.');
        // }

        // // Max
        // if (isset($options['max']) && $dt > new \DateTime($options['max'])) {
        //     if (isset($options['max_message'])) {
        //         $options['message'] = $this->formatCustomMessage($options['max'], $options['max_message']);
        //     }

        //     return $this->getErrorMessage($options, 'Date must not be after ' . $options['max'] . '.');
        // }

        return null;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'date';
    }

    /** {@inheritdoc} */
    protected function getDefaultOptions(): array
    {
        return [
        ];
    }
}
