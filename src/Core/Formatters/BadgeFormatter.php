<?php

declare(strict_types=1);

namespace Core\Formatters;

use Core\I18n\I18nTranslator;
use Core\Services\ThemeServiceInterface;

/**
 * Badge formatter for rendering status/category badges
 *
 * Uses ThemeService to get appropriate badge classes for the current theme.
 */
class BadgeFormatter extends AbstractFormatter
{
    public function __construct(
        private ThemeServiceInterface $themeService,
        private I18nTranslator $translator,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'badge';
    }

    /**
     * {@inheritdoc}
     */
    public function supports(mixed $value): bool
    {
        // Supports any value that can be converted to string
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function transform(mixed $value, array $options = [], mixed $originalValue = null): string
    {
        $options = $this->mergeOptions($options);

        if ($value === null || $value === '') {
            return '';
        }

        // ✅ Initialize label and variant
        $label = null;
        $variant = null;

        // ✅ STEP 1: Handle boolean_badges (BEFORE enum_class)
        if (isset($options['boolean_badges'])) {
            // Normalize value to boolean for array key lookup
            $boolValue = filter_var($originalValue, FILTER_VALIDATE_BOOLEAN);
            $badgeKey = $boolValue ? 'true' : 'false';

            if (isset($options['boolean_badges'][$badgeKey])) {
                $badgeConfig = $options['boolean_badges'][$badgeKey];

                $label = $badgeConfig['code'] ?? (string)$value;
                $variant = $badgeConfig['variant'] ?? 'secondary';
            }
        }

        // ✅ STEP 2: Handle enum_class (if boolean_badges didn't set label/variant)
        if ($label === null && isset($options['enum_class'])) {
            $enumClass = $options['enum_class'];
            if (enum_exists($enumClass)) {
                $enum = $enumClass::tryFrom((string)$originalValue);
                //label = $enum?->code() ?? ucfirst((string)$originalValue);
                $label = (string)$value;
                $variant = $this->getEnumVariant($enum) ?? $options['variant'] ?? 'secondary';
            }
        }

        // ✅ STEP 3: Fallback to raw value and options
        if ($label === null) {
            $label = $options['label'] ?? (string)$value;
        }

        // ✅ STEP 4: Get variant from options WITH proper fallback handling
        if ($variant === null) {
            // ✅ Distinguish between explicit null (no badge) vs missing key (default badge)
            $variant = array_key_exists('variant', $options)
                ? $options['variant']  // Preserve explicit null
                : 'secondary';          // Default for missing key
        }

        // ✅ STEP 5: Check for null/none variant BEFORE applying fallback
        if ($variant === null || $variant === 'none') {
            // ✅ Translate label if needed
            if (is_string($label) && str_contains($label, '.')) {
                $label = $this->translator->get($label, pageName: $options['page_name'] ?? null);
            }
            // ✅ Return plain text (no badge)
            return $label;
        }


        // ✅ STEP 6: Translate label if it looks like a translation key
        if (is_string($label) && str_contains($label, '.')) {
            $label = $this->translator->get($label, pageName: $options['page_name'] ?? null);
        }

        // ✅ Step 7: Get theme-specific badge classes
        $badgeClass = $this->themeService->getBadgeClass($variant);

        // ✅ Step 8: Return raw HTML (AbstractFormatter::format() will handle sanitization)
        return sprintf(
            '<span class="%s">%s</span>',
            htmlspecialchars($badgeClass),
            htmlspecialchars($label)
        );
    }

    private function getEnumVariant(mixed $enum): ?string
    {
        // ✅ Check if enum has a variant() method
        if (method_exists($enum, 'badgeVariant')) {
            return $enum->badgeVariant();
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function isSafeHtml(): bool
    {
        // This formatter produces safe HTML with proper escaping
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultOptions(): array
    {
        return [
            'label' => null,    // Human-readable label for the badge
            'variant' => 'secondary', // Badge variant (success, danger, warning, etc.)
            'is_html_label' => false,       // If true, label is treated as safe HTML
        ];
    }
}
