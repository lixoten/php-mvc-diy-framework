<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * URL validator service
 *
 * Validates URL input fields, including format, length, pattern, allowed/blocked domains.
 * Supports IP addresses and localhost with configurable restrictions.
 *
 * Configuration options:
 * - enforce_blocked_domains: bool - Enable blocked domains check (default: true)
 * - enforce_allowed_domains: bool - Enable allowed domains check (default: false)
 */
class UrlValidator extends AbstractValidator
{
    /** {@inheritdoc} */
    public function validate($value, array $options = []): ?string
    {
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

        // Extract host for validation
        $host = parse_url((string)$value, PHP_URL_HOST);

        // Validate URL format and host structure
        if (
            !filter_var($value, FILTER_VALIDATE_URL)
            || empty($host)
            || strpos($host, '.') === false
            || str_starts_with($host, '.')
            || str_ends_with($host, '.')
            || preg_match('/^\.+$/', $host)
        ) {
            $options['message'] ??= $options['invalid_message'] ?? null;
            return $this->getErrorMessage($options, 'Please enter a valid URL with a
                                                                              domain. (Make sure to include http...)');
        }

        // Custom pattern check (if provided)
        if ($error = $this->validatePattern($value, $options)) {
            return $error;
        }

        // Allowed domains/host
        if ($error = $this->validateAllowedValues($host, $options)) {
            return $error;
        }

        // Forbidden domains/host
        if ($error = $this->validateForbiddenValues($host, $options)) {
            return $error;
        }

        return null;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'url';
    }

    /** {@inheritdoc} */
    protected function getDefaultOptions(): array
    {
        return [
        ];
    }
}
