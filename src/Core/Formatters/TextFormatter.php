<?php

declare(strict_types=1);

namespace Core\Formatters;

use App\Helpers\DebugRt;
use Core\I18n\I18nTranslator;
use Core\Interfaces\CodeLookupServiceInterface;

/**
 * Basic text formatter with truncation and sanitization
 */
class TextFormatter extends AbstractFormatter
{
    public function __construct(
        private I18nTranslator $translator,
        // private CodeLookupServiceInterface $codeLookupService
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

        // // ✅ STEP 1: Handle enum_class (HIGHEST PRIORITY)
        // $enumClass = $options['enum_class'] ?? null;
        // if ($enumClass !== null && enum_exists($enumClass)) {
        //     // $value = 'p';
        //     $enum = $enumClass::tryFrom((string)$value);

        //     if (!isset($enum)) {
        //         $value = $this->translator->get('code.unknown', pageName: $options['page_name'] ?? null);
        //     } elseif (method_exists($enum, 'translationKey')) {
        //         $translationKey = $enum?->translationKey() ?? ($options['unknown_label'] ?? 'Unknown');

        //         // If a translator is available AND the label looks like a translation key, translate it.
        //         if (str_contains($translationKey, '.')) {
        //             $value = $this->translator->get($translationKey, pageName: $options['page_name'] ?? null);
        //         } else {
        //             $value = $translationKey;
        //         }
        //     }

        //     return $value;
        // }
        //DebugRt::j('0', 'options: ', $options);


        // ✅ STEP 2: Handle explicit 'label' option (from options_provider)
        if (isset($options['label'])) {
            $text = (string) $options['label'];
            // If a translator is available AND the label looks like a translation key, translate it.
            if (str_contains($text, '.')) {
                $text = $this->translator->get($text, pageName: $options['page_name'] ?? null);
            }
            return $text;
        }

        // ✅ STEP 3: Handle translation_prefix pattern
        if (isset($options['translation_prefix'])) {
            // Check if translation_prefix is provided (simple pattern: 'gender.f')
            $translationKey = $options['translation_prefix'] . '.' . $value;
            $text = $this->translator->get($translationKey, pageName: $options['page_name'] ?? null);
            return $text;
        }

        // ✅ STEP 4: Handle null values
        if ($value === null) {
            // Fallback: Use raw value
            return $options['null_value'] ?? '';
        }

        // ✅ STEP 5: Use raw value as fallback
        $text = (string) $value;

        // ✅ STEP 6: Apply text transformations (optional)
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

        // ✅ STEP 7: Apply suffix (optional)
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
