<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Search input validator service
 *
 * Validates search input fields for length, pattern, and basic sanitization.
 * Keeps logic simple for search, but enforces security and consistency.
 */
class SearchValidator extends AbstractValidator
{
    /** {@inheritdoc} */
    public function validate($value, array $options = []): ?string
    {
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        $length = mb_strlen((string)$value);

        // Max length
        if ($error = $this->validateMaxLength($length, $options)) {
            return $error;
        }

        // fixme - not sure this makes sense. do we sanize before this?
        // Basic sanitization: block control characters and dangerous input
        if (preg_match('/[\x00-\x1F\x7F]/', $value)) {
            return $this->getErrorMessage($options, 'Search term contains invalid characters.');
        }

        return null;
    }

    public function getName(): string
    {
        return 'search';
    }

    protected function getDefaultOptions(): array
    {
        return [
        ];
    }
}
