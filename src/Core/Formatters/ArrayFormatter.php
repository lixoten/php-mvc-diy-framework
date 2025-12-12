<?php

declare(strict_types=1);

namespace Core\Formatters;

use Core\I18n\I18nTranslator;

/**
 * Formatter for array values (multi-select fields, checkbox groups, etc.)
 *
 * Responsibilities:
 * - Convert arrays to comma-separated strings
 * - Apply label transformations via enum classes
 * - Handle empty arrays gracefully
 *
 * Does NOT handle:
 * - HTML generation (that's BadgeFormatter's job)
 * - Data transformation (that's DataTransformerService's job)
 */
class ArrayFormatter extends AbstractFormatter
{
    public function __construct(
        private I18nTranslator $translator,
    ) {
    }

    public function getName(): string
    {
        return 'array';
    }

    public function supports(mixed $value): bool
    {
        return is_array($value); // ✅ Only accepts arrays
    }

    /**
     * Transform array to comma-separated string with optional enum label lookup
     *
     * @param mixed $value The array to format
     * @param array{
     *     enum_class?: class-string|null,  // ✅ Added |null for clarity
     *     separator?: string,               // Default: ', '
     *     empty_text?: string               // Default: ''
     * } $options Formatting options
     * @return string Formatted comma-separated string
     */
    public function transform(mixed $value, array $options = [], mixed $originalValue = null): string
    {
        $options = $this->mergeOptions($options);

        // Handle empty arrays
        if (empty($value)) {
            return $options['empty_text'];
        }

        $enumClass = $options['enum_class'];
        $separator = $options['separator'];

        // If enum class provided, convert values to labels
        if ($enumClass !== null && enum_exists($enumClass)) {
            $labels = array_map(function ($val) use ($enumClass) {
                $enum = $enumClass::tryFrom($val);

                if (!isset($enum)) {
                    $value = $this->translator->get('code.unknown', pageName: $options['page_name'] ?? null);
                } elseif (method_exists($enum, 'translationKey')) {
                    // $value = $enum?->label() ?? ($options['unknown_label'] ?? 'Unknown');
                    $translationKey = $enum?->translationKey() ?? ($options['unknown_label'] ?? 'Unknown');

                    // If a translator is available AND the label looks like a translation key, translate it.
                    if (str_contains($translationKey, '.')) {
                        $value = $this->translator->get($translationKey, pageName: $options['page_name'] ?? null);
                    } else {
                        $value = $translationKey;
                    }
                }

                return $value;
            }, $value);

            return implode($separator, $labels);
        }

        // No enum class - just join the raw values with ucfirst
        return implode($separator, array_map(fn($v) => ucfirst((string)$v), $value));
    }

    protected function getDefaultOptions(): array
    {
        return [
            'enum_class' => null,
            'separator' => ', ',
            'empty_text' => '',
        ];
    }
}
