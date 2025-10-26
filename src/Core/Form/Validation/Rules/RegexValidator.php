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

        protected function getDefaultOptions(): array
    {
        DebugRt::j('1', '', 'boom');
        return [
            'required'  => null,
            'minlength'         => null,
            'maxlength'         => null,
            'pattern'           => null,

            'forbidden_words'    => ['1234', 'password'],
            'require_digit'     => false,
            'require_uppercase' => false,
            'require_lowercase' => false,
            'require_special'   => false,


            'required_message' => null,
            'minlength_message'         => null,
            'maxlength_message'         => null,
            'pattern_message'           => null,

            'forbidden_words_message'   => null,
            'require_digit_message'     => null,
            'require_uppercase_message' => null,
            'require_lowercase_message' => null,
            'invalid_message'           => null,
        ];
    }
}
