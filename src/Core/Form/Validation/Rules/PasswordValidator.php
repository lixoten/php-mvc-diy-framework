<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Password validator service
 *
 * Validates password, secret code, passcode, or any sensitive string field.
 */
class PasswordValidator extends AbstractValidator
{
    /** {@inheritdoc} */
    public function validate($value, array $options = []): ?string
    {
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        if (!is_string($value)) {
            $options['message'] ??= $options['invalid_message'] ?? null;
            return $this->getErrorMessage($options, 'Invalid password format.');
        }

        $length = mb_strlen($value);

        // Min length
        if (isset($options['minlength']) && $length < $options['minlength']) {
            isset($options['minlength_message'])
                ? $options['message'] = $this->formatCustomMessage(
                    (string)$options['minlength'],
                    $options['minlength_message']
                )
                : null;

            return $this->getErrorMessage($options, "Password must be at least {$options['minlength']} characters.");
        }

        // Max length
        if (isset($options['maxlength']) && $length > $options['maxlength']) {
            isset($options['maxlength_message'])
                ? $options['message'] = $this->formatCustomMessage(
                    (string)$options['maxlength'],
                    $options['maxlength_message']
                )
                : null;

            return $this->getErrorMessage($options, "Password must not exceed {$options['maxlength']} characters.");
        }

        // pattern length
        if (!empty($options['pattern']) && !preg_match($options['pattern'], $value)) {
            $options['message'] ??= $options['pattern_message'] ?? null;

            return $this->getErrorMessage($options, 'Password does not match the required pattern.');
        }

        // todo change to detect seq of characters. atm we compare if pw is a word
        // Forbidden values
        if (!empty($options['forbidden_words']) && in_array($value, $options['forbidden_words'], true)) {
            $options['message'] ??= $options['forbidden_words_message'] ?? null;

            return $this->getErrorMessage($options, 'This password is not allowed.');
        }

        // Complexity: require at least one digit
        if (!empty($options['require_digit']) && !preg_match('/\d/', $value)) {
            $options['message'] ??= $options['require_digit_message'] ?? null;

            return $this->getErrorMessage($options, 'Password must contain at least one digit.');
        }

        // Complexity: require at least one uppercase letter
        if (!empty($options['require_uppercase']) && !preg_match('/[A-Z]/', $value)) {
            $options['message'] ??= $options['require_uppercase_message'] ?? null;

            return $this->getErrorMessage($options, 'Password must contain at least one uppercase letter.');
        }

        // Complexity: require at least one lowercase letter
        if (!empty($options['require_lowercase']) && !preg_match('/[a-z]/', $value)) {
            $options['message'] ??= $options['require_lowercase_message'] ?? null;

            return $this->getErrorMessage($options, 'Password must contain at least one lowercase letter.');
        }

        // Complexity: require at least one special character
        if (!empty($options['require_special']) && !preg_match('/[^a-zA-Z0-9]/', $value)) {
            $options['message'] ??= $options['require_special_message'] ?? null;

            return $this->getErrorMessage($options, 'Password must contain at least one special character.');
        }

        return null;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'password';
    }

    /** {@inheritdoc} */
    protected function getDefaultOptions(): array
    {
        return [
            // 'required'          => null,
            // 'minlength'         => null,
            // 'maxlength'         => null,
            // 'pattern'           => null,

            // 'forbidden_words'   => ['1234', 'password', 'qwerty'],
            // 'require_digit'     => false,
            // 'require_uppercase' => false,
            // 'require_lowercase' => false,
            // 'require_special'   => false,

            // 'required_message'          => null,
            // 'minlength_message'         => null,
            // 'maxlength_message'         => null,
            // 'pattern_message'           => null,

            // 'forbidden_words_message'   => null,
            // 'require_digit_message'     => null,
            // 'require_uppercase_message' => null,
            // 'require_lowercase_message' => null,
            // 'invalid_message'           => null,
        ];
    }
}
