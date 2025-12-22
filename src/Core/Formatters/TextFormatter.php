<?php

declare(strict_types=1);

namespace Core\Formatters;

use Core\I18n\I18nTranslator;

/**
 * Basic text formatter with truncation and sanitization
 */
class TextFormatter extends AbstractFormatter
{
    public function __construct(
        private I18nTranslator $translator,
    ) {
    }

    public function getName(): string
    {
        return 'text';
    }

    public function supports(mixed $value): bool
    {
        return is_string($value) || is_numeric($value) || $value === null;
    }

    public function transform(mixed $value, array $options = [], mixed $originalValue = null): string
    {
        $options = $this->mergeOptions($options);

        // ✅ STEP 1: Handle null values first as an early exit
        // This is crucial for 'null with custom value' and 'null value, suffix' tests.
        if ($value === null) {
            $baseText = (string) ($options['null_value'] ?? ''); // ✅ Ensure null_value is retrieved
            // If a suffix is present AND the original value was null, apply it here.
            if (isset($options['suffix'])) {
                $baseText .= (string) $options['suffix'];
            }
            return $baseText; // Return early after null handling and suffix application
        }

        // ✅ STEP 2: Determine base text based on priority: label, then translation_prefix, then raw value
        if (isset($options['label'])) {
            $text = (string) $options['label'];
            if (str_contains($text, '.')) {
                $text = $this->translator->get($text, pageName: $options['page_name'] ?? null);
            }
        } elseif (isset($options['translation_prefix'])) {
            $translationKey = $options['translation_prefix'] . '.' . $value;
            $text = $this->translator->get($translationKey, pageName: $options['page_name'] ?? null);
        } else {
            // Fallback: Use the raw value, cast to string
            $text = (string) $value;
        }

        // ✅ STEP 3: Apply text transformations (optional)
        if (isset($options['transform'])) {
            switch ($options['transform']) { // ✅ Using switch for clarity and future expansion
                case 'uppercase':
                    $text = mb_strtoupper($text, 'UTF-8'); // ✅ Multi-byte safe
                    break;
                case 'lowercase':
                    $text = mb_strtolower($text, 'UTF-8'); // ✅ Multi-byte safe
                    break;
                case 'capitalize':
                    $text = $this->capitalize($text);
                    break;
                case 'title':
                    $text = $this->titleCase($text);
                    break;
                case 'trim':
                    $text = trim($text);
                    break;
                case 'last2char_upper':
                    $text = $this->last2CharUpper($text);
                    break;
                default:
                    // Unknown transform, do nothing
                    break;
            }
        }

        // ✅ STEP 4: Apply truncation (max_length and truncate_suffix)
        $maxLength = $options['max_length'] ?? null;
        $truncateSuffix = (string) ($options['truncate_suffix'] ?? ''); // Ensure suffix is string

        if ($maxLength !== null && mb_strlen($text, 'UTF-8') > $maxLength) {
            $stringSegmentLength = $maxLength - mb_strlen($truncateSuffix, 'UTF-8');

            if ($stringSegmentLength <= 0) {
                // If the suffix itself is longer than or equal to max_length, just return the suffix truncated.
                $text = mb_substr($truncateSuffix, 0, $maxLength, 'UTF-8');
            } else {
                $text = mb_substr($text, 0, $stringSegmentLength, 'UTF-8') . $truncateSuffix;
            }
        }


        // ✅ STEP 5: Append custom suffix (applied after truncation and transformations)
        if (isset($options['suffix'])) {
            $text .= $options['suffix'];
        }

        return $text;
    }

    /**
     * Converts the first character of the string to uppercase and the rest to lowercase.
     *
     * @param string $text The input string.
     * @return string The capitalized string.
     */
    private function capitalize(string $text): string
    {
        if (empty($text)) {
            return '';
        }
        return mb_strtoupper(mb_substr($text, 0, 1, 'UTF-8'), 'UTF-8') . mb_strtolower(mb_substr($text, 1, null, 'UTF-8'), 'UTF-8');
    }

    /**
     * Converts the first character of each word in the string to uppercase.
     *
     * @param string $text The input string.
     * @return string The title-cased string.
     */
    private function titleCase(string $text): string
    {
        return mb_convert_case($text, MB_CASE_TITLE, 'UTF-8');
    }



    /**
     * Uppercase the last 2 characters of a string.
     *
     * @param string $text
     * @return string
     */
    private function last2CharUpper(string $text): string
    {
        $len = mb_strlen($text, 'UTF-8');
        if ($len < 2) {
            return $text;
        }
        $start = mb_substr($text, 0, $len - 2, 'UTF-8');
        $last2 = mb_strtoupper(mb_substr($text, -2, null, 'UTF-8'), 'UTF-8');
        return $start . $last2;
    }

    protected function getDefaultOptions(): array
    {
        return [
            'null_value'         => '',
            'max_length'         => null,
            'truncate_suffix'    => '...',
            'transform'          => null,
            'suffix'             => null,
            'label'              => null, // For explicit label override
            'translation_prefix' => null, // For simple translation pattern
            'page_name'          => null, // For context-aware translation
        ];
    }
}
