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

            // Translate if label looks like a translation key
            if (is_string($label) && str_contains($label, '.')) {
                $label = $this->translator->get($label, pageName: $options['page_name'] ?? null);
            }

            return htmlspecialchars($label);
        }

        return htmlspecialchars((string)$value);
    }

    protected function getDefaultOptions(): array
    {
        return [
            'label' => null,                    // Resolved by FormatterService (from options_provider)
            'page_name' => null,                // For context-aware translation
        ];;
    }
}
