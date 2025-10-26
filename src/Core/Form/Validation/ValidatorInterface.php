<?php

declare(strict_types=1);

namespace Core\Form\Validation;

/**
 * Interface for validators
 */
interface ValidatorInterface
{
    /**
     * Validate a value
     *
     * @param mixed $value Value to validate
     * @param array $options Validation options
     * @return string|null Error message if validation fails, null if valid
     */
    public function validate($value, array $options = []): ?string;

    /**
     * Get validator name.
     *
     * @return string
     */
    public function getName(): string;
}
