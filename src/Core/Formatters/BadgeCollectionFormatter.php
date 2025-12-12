<?php
// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\Core\Formatters\BadgeCollectionFormatter.php

declare(strict_types=1);

namespace Core\Formatters;

use Core\I18n\I18nTranslator;
use Core\Services\ThemeServiceInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Badge Collection Formatter
 *
 * This formatter is responsible for rendering an array of values as
 * individual badges. It resolves options for each item independently
 * and generates badge HTML directly, avoiding delegation to BadgeFormatter
 * to prevent circular dependencies with FormatterService for option resolution.
 *
 * This design duplicates some badge HTML generation logic but allows for
 * a clean separation of concerns for handling collections (arrays).
 */
class BadgeCollectionFormatter extends AbstractFormatter
{
    public function __construct(
        private ThemeServiceInterface $themeService,
        private I18nTranslator $translator,
        private ?ContainerInterface $container = null, // Needed for service-based options_providers
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function getName(): string
    {
        return 'badge_collection';
    }

    public function supports(mixed $value): bool
    {
        // This formatter explicitly supports arrays.
        // If a single value is somehow passed, it will be treated as a single-item array.
        return is_array($value) || is_string($value) || is_bool($value) || is_int($value);
    }

    protected function transform(mixed $value, array $options = [], mixed $originalValue = null): string
    {
        $options = $this->mergeOptions($options);



        // ✅ SOLID FIX: BadgeCollectionFormatter is self-aware.
        // If 'value' has been transformed to a scalar by a prior formatter in the chain,
        // but 'originalValue' is still an array, prioritize 'originalValue' for iteration.
        // This ensures the formatter always gets the collection it's designed for.
        $inputForCollection = $value;
        if (is_array($originalValue) && !is_array($value) && $value !== null) {
            $inputForCollection = $originalValue;
        } elseif ($value === null && is_array($originalValue)) {
            // Handle case where $value became null but original was an array
            $inputForCollection = $originalValue;
        }

        // Normalize the chosen input value to an array for consistent iteration
        $valuesToProcess = is_array($inputForCollection) ? $inputForCollection : [$inputForCollection];




        // // Normalize value to an array for consistent processing
        // $valuesToProcess = is_array($value) ? $value : [$value];

        if (empty($valuesToProcess)) {
            return $options['empty_text'] ?? '';
        }

        $badges = [];

        foreach ($valuesToProcess as $item) {
            // ✅ Step 1: Resolve options_provider for EACH individual item (string, not array)
            $itemOptions = $this->resolveItemOptions($item, $options);

            // ✅ Step 2: Generate badge HTML directly (no delegation to BadgeFormatter)
            $badgeHtml = $this->generateBadgeHtml($item, $itemOptions);

            if ($badgeHtml !== '') {
                $badges[] = $badgeHtml;
            }
        }

        return implode($options['separator'] ?? ' ', $badges);
    }

    /**
     * Resolves the 'options_provider' for a SINGLE item.
     * This logic is similar to FormatterService::resolveOptionsProvider but
     * is self-contained within this formatter to avoid FormatterService dependencies.
     *
     * @param mixed $item The single value to resolve options for.
     * @param array $configOptions The options from the field configuration.
     * @return array The resolved options for the item, including 'label' and 'variant'.
     */
    private function resolveItemOptions(mixed $item, array $configOptions): array
    {
        // If no options_provider, return the original config options (they might have 'label'/'variant' directly)
        if (!isset($configOptions['options_provider'])) {
            return $configOptions;
        }

        $provider = $configOptions['options_provider'];

        if (!is_array($provider) || count($provider) !== 2) {
            $this->logger?->warning("Invalid options_provider format in BadgeCollectionFormatter", [
                'provider' => $provider,
                'item' => $item,
            ]);
            return $configOptions;
        }

        [$class, $method] = $provider;
        $resolved = [];

        // Try to get service from container (e.g., CodeLookupServiceInterface)
        if ($this->container !== null && $this->container->has($class)) {
            try {
                $service = $this->container->get($class);
                if (method_exists($service, $method)) {
                    // Pass item and context (e.g., lookup_type)
                    $resolved = $service->$method($item, $configOptions);
                }
            } catch (\Throwable $e) {
                $this->logger?->error("Service-based options_provider failed in BadgeCollectionFormatter", [
                    'service' => $class,
                    'method' => $method,
                    'item' => $item,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        // Fallback to static method call (e.g., SuperPower::getFormatterOptions)
        elseif (is_string($class) && is_string($method) && method_exists($class, $method)) {
            try {
                // ✅ Call static method with the SINGLE item (string|null), not an array!
                $resolved = $class::$method($item, $configOptions);
            } catch (\Throwable $e) {
                $this->logger?->error("Static options_provider failed in BadgeCollectionFormatter", [
                    'class' => $class,
                    'method' => $method,
                    'item' => $item,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            $this->logger?->warning("options_provider not callable in BadgeCollectionFormatter", [
                'provider' => $provider,
                'item' => $item,
            ]);
        }

        // Normalize 'translation_key' to 'label' and translate if needed
        if (isset($resolved['translation_key']) && !isset($resolved['label'])) {
            $resolved['label'] = $this->translator->get($resolved['translation_key'], pageName: $configOptions['page_name'] ?? null);
            unset($resolved['translation_key']);
        }
        // If label is still a translation key, translate it here (e.g., if set directly by provider as a key)
        elseif (isset($resolved['label']) && is_string($resolved['label']) && str_contains($resolved['label'], '.')) {
             $resolved['label'] = $this->translator->get($resolved['label'], pageName: $configOptions['page_name'] ?? null);
        }

        // Merge resolved options with original config options (resolved takes precedence)
        return array_merge($configOptions, $resolved);
    }

    /**
     * Generates the HTML for a single badge.
     * This logic is duplicated from BadgeFormatter but is necessary for this standalone approach.
     *
     * @param mixed $value The raw value (used as fallback label)
     * @param array $options Resolved options containing 'label' and 'variant'
     * @return string The HTML for the badge
     */
    private function generateBadgeHtml(mixed $value, array $options): string
    {
        $label = $options['label'] ?? (string)$value;
        $variant = $options['variant'] ?? 'secondary';

        // Handle 'null' or 'none' variant explicitly (return plain text)
        if ($variant === null || $variant === 'none') {
            return htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        }

        $badgeClass = $this->themeService->getBadgeClass($variant);

        return sprintf(
            '<span class="%s">%s</span>',
            htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
        );
    }

    protected function isSafeHtml(): bool
    {
        // This formatter produces safe HTML with proper escaping
        return true;
    }

    protected function getDefaultOptions(): array
    {
        return [
            'separator' => ' ',    // Space between badges
            'empty_text' => '',    // What to show for empty arrays
        ];
    }
}
