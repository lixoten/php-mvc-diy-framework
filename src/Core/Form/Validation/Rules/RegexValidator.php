<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

use App\Helpers\DebugRt;

/**
 * Regex validator
 */
class RegexValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, array $options = []): ?string
    {
        // Skip validation if value is empty
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        // DebugRt::j('1', '', $options);
        // // Skip validation if pattern is null or false (explicit disabling)
        // if (isset($options['pattern']) && ($options['pattern'] === null || $options['pattern'] === false)) {
        //     return null;
        // }

        // Ensure a pattern is provided
        if (empty($options['pattern'])) {
            throw new \InvalidArgumentException('RegexValidator requires a "pattern" option.');
        }

        // Validate the value against the regex pattern
        if (!preg_match($options['pattern'], (string)$value)) {
            return $this->getErrorMessage($options, 'This value is invalid.');
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'regex';
    }
}
