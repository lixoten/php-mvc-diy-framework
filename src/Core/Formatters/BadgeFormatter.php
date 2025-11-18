<?php

declare(strict_types=1);

namespace Core\Formatters;

use Core\Services\ThemeServiceInterface;

/**
 * Badge formatter for rendering status/category badges
 *
 * Uses ThemeService to get appropriate badge classes for the current theme.
 */
class BadgeFormatter extends AbstractFormatter
{
    public function __construct(
        private ThemeServiceInterface $themeService
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
    protected function transform(mixed $value, array $options = []): string
    {
        $options = $this->mergeOptions($options);

        if ($value === null || $value === '') {
            return '';
        }

        // ✅ NEW: Handle array values to render a list of badges
        if (is_array($value)) {
            $badges = [];
            foreach ($value as $item) {
                // Recursively call this method for each item in the array
                $badges[] = $this->transform($item, $options);
            }
            return implode(' ', array_filter($badges));
        }

        // Get label from options or use value as-is
        $label = $options['label'] ?? (string)$value;

        // Get badge variant (e.g., 'success', 'danger', 'warning')
        // Can be passed explicitly or derived from a status enum
        $variant = $options['variant'] ?? 'secondary';

        // Get theme-specific badge classes
        $badgeClass = $this->themeService->getBadgeClass($variant);

        // Return raw HTML (will be sanitized by AbstractFormatter::format())
        return '<span class="' . htmlspecialchars($badgeClass) . '">'
             . htmlspecialchars($label)
             . '</span>';
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
