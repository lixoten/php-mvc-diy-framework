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
        private I18nTranslator $translator // ✅ NEW: Translator is a mandatory dependency for TextFormatter's translation logic
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

    public function transform(mixed $value, array $options = []): string
    {
        $options = $this->mergeOptions($options);

        // Check if a 'label' is provided (from options_provider like CodeLookupService)
        if (isset($options['label'])) {
            $text = (string) $options['label'];
            // If a translator is available AND the label looks like a translation key, translate it.
            if (str_contains($text, '.')) {
                $text = $this->translator->get($text, pageName: $options['page_name'] ?? null);
            }
        }
        // Check if translation_prefix is provided (simple pattern: 'gender.f')
        elseif (isset($options['translation_prefix']) && isset($options['translator'])) {
            $translationKey = $options['translation_prefix'] . '.' . $value;
            $text = $this->translator->get($translationKey, pageName: $options['page_name'] ?? null);
        }
        // Fallback: Use raw value
        elseif ($value === null) {
            return $options['null_value'] ?? '';
        } else {
            $text = (string) $value;
        }

        // Apply text transformation
        if (isset($options['transform'])) {
            $text = match ($options['transform']) {
                'uppercase' => strtoupper($text),
                'lowercase' => strtolower($text),
                'capitalize' => ucfirst(strtolower($text)),
                'title' => ucwords(strtolower($text)),
                'trim' => trim($text), // notes-: assuming we did not store clean data
                'last2char_upper' => $this->last2CharUpper($text),
                default => $text
            };
        }

        // Apply suffix if specified (alternative approach)
        if (isset($options['suffix'])) {
            $text .= $options['suffix'];
        }

        return $text;
    }


    /**
     * Uppercase the last 2 characters of a string.
     *
     * @param string $text
     * @return string
     */
    private function last2CharUpper(string $text): string
    {
        $len = mb_strlen($text);
        if ($len <= 2) {
            return mb_strtoupper($text);
        }
        $start = mb_substr($text, 0, $len - 2);
        $last2 = mb_substr($text, -2);
        return $start . mb_strtoupper($last2);
    }

    protected function getDefaultOptions(): array
    {
        return [
            'max_length' => null,
            'truncate_suffix' => '...',
            'null_value' => '',
            'transform'  => null,
            'suffix'     => null,
            'label'      => null, // For CodeLookupService pattern
            'translation_prefix' => null, // ✅ For simple translation pattern
            'page_name' => null,  // ✅ For context-aware translation
        ];
    }
}
