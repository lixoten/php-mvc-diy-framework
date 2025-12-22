<?php

declare(strict_types=1);

namespace Core\Formatters;

use Core\I18n\I18nTranslator;

/**
 * Formatter for boolean values
 */
class BooleanFormatter extends AbstractFormatter
{
     public function __construct(
        private I18nTranslator $translator,
    ) {
    }

    public function getName(): string
    {
        return 'boolean';
    }

    public function supports(mixed $value): bool
    {
        return is_bool($value) || $value === '0' || $value === '1' || $value === 0 || $value === 1;
    }

    public function transform(mixed $value, array $options = [], mixed $originalValue = null): string
    {
        $options = $this->mergeOptions($options);

        // ✅ STEP 1: Check if options_provider resolved a 'label' (HIGHEST PRIORITY)
        if (isset($options['label'])) {
            $label = $options['label'];
            $translatedLabel = $this->translateLabel($label, $options['page_name'] ?? null);
            return $translatedLabel;
        }

        // ✅ STEP 2: Handle arrays explicitly
        if (is_array($value)) {
            return ''; // Return empty string for arrays
        }

        // ✅ FIX: Explicitly convert booleans to '0' or '1' before htmlspecialchars
        if (is_bool($value)) {
            return (string)(int)$value; // Converts false to '0', true to '1'
        }

        return htmlspecialchars((string)$value);
    }

    private function translateLabel(string $labelKey, ?string $pageName = null): string
    {
        // Use the translator to get the label
        $translated = $this->translator->get($labelKey, [], $pageName);
        return $translated;
    }


    protected function getDefaultOptions(): array
    {
        return [
            'label' => null,                    // Resolved by FormatterService (from options_provider)
            'page_name' => null,                // For context-aware translation
        ];;
    }
}
