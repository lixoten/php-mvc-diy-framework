<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Email validator service
 */
class EmailValidator extends AbstractValidator
{
    /** {@inheritdoc} */
    public function validate($value, array $options = []): ?string
    {
        // Use the inherited method
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        $length = mb_strlen((string)$value);

        // Min length
        if ($error = $this->validateMinLength($length, $options)) {
            return $error;
        }

        // Max length
        if ($error = $this->validateMaxLength($length, $options)) {
            return $error;
        }

        // Custom pattern check (if provided)
        // Example: Suppose you want to allow only emails that start with "user" before the @ symbol.
        // user in config: 'pattern' => '/^user[a-z0-9._%+-]*@/'
        // Pattern
        if ($error = $this->validatePattern($value, $options)) {
            return $error;
        }

        // Validate email
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $options['message'] ??= $options['invalid_message'] ?? null;

            return $this->getErrorMessage($options, 'Please enter a valid email address.');
        }

        // Extract domain
        $domain = substr(strrchr((string)$value, "@"), 1);

        // Allowed domains
        if ($error = $this->validateAllowedValues($domain, $options)) {
            return $error;
        }

        // Forbidden domains
        if ($error = $this->validateForbiddenValues($domain, $options)) {
            return $error;
        }

        return null;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'email';
    }

    /** {@inheritdoc} */
    protected function getDefaultOptions(): array
    {
        return [
        ];
    }
}
