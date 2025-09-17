<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Date field validator
 */
class DateValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, array $options = []): ?string
    {
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        $format = $options['format'] ?? 'Y-m-d';
        $dt = \DateTime::createFromFormat($format, (string)$value);

        if (!$dt || $dt->format($format) !== $value) {
            return $this->getErrorMessage($options, 'Please enter a valid date.');
        }

        if (isset($options['min']) && $dt < new \DateTime($options['min'])) {
            return $this->getErrorMessage($options, 'Date must not be before ' . $options['min'] . '.');
        }

        if (isset($options['max']) && $dt > new \DateTime($options['max'])) {
            return $this->getErrorMessage($options, 'Date must not be after ' . $options['max'] . '.');
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'date';
    }
}
