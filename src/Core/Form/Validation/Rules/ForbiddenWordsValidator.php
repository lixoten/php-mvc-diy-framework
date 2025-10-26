<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Validator to prevent forbidden words in a field value.
 */
class ForbiddenWordsValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, array $options = []): ?string
    {
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        $forbidden = $options['words'] ?? [];
        $valueStr = mb_strtolower(trim((string)$value));

        foreach ($forbidden as $word) {
            $word = mb_strtolower($word);
            if ($word !== '' && mb_strpos($valueStr, $word) !== false) {
                return $this->getErrorMessage(
                    $options,
                    $options['message'] ?? "This word '{$word}' is not allowed."
                );
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'forbidden_words';
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
