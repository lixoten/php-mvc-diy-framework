<?php
declare(strict_types=1);

namespace Core\Components;

use Core\Services\ConfigService;
use Core\Services\FieldRegistryService;

/**
 * Abstract base class for components.
 * Provides shared logic for config loading and rendering helpers.
 */
abstract class AbstractComponent implements ComponentInterface
{
    /**
     * Constructor.
     *
     * @param ConfigService $configService The config service for loading options.
     * @param FieldRegistryService $fieldRegistryService The field registry service for field resolution.
     */
    public function __construct(
        protected readonly ConfigService $configService,
        protected readonly FieldRegistryService $fieldRegistryService,
    ) {}

    /**
     * Loads component options with fallbacks.
     *
     * @param string $componentName The name of the component (e.g., 'form').
     * @param string|null $pageKey The page context for fallbacks.
     * @param string|null $entityName The entity context for fallbacks.
     * @return array<string, mixed> The resolved options.
     */
    protected function loadOptions(string $componentName, ?string $pageKey = null, ?string $entityName = null): array
    {
        $options = [];

        // Load base config
        $baseConfig = $this->configService->get("component_fields.base.{$componentName}", []);
        if (is_array($baseConfig)) {
            $options = array_merge($options, $baseConfig);
        }

        // Load entity config if provided
        if ($entityName !== null) {
            $entityConfig = $this->configService->get("component_fields.{$entityName}.{$componentName}", []);
            if (is_array($entityConfig)) {
                $options = array_merge($options, $entityConfig);
            }
        }

        // Load page config if provided
        if ($pageKey !== null) {
            $pageConfig = $this->configService->get("component_fields.{$pageKey}.{$componentName}", []);
            if (is_array($pageConfig)) {
                $options = array_merge($options, $pageConfig);
            }
        }

        return $options;
    }

    /**
     * Basic render method that can be overridden by subclasses.
     * Provides a default implementation for rendering.
     *
     * @param array<string, mixed> $options Additional rendering options.
     * @return string The rendered HTML string.
     */
    public function render(array $options = []): string
    {
        // Default implementation: return empty string; subclasses should override
        return '';
    }
}
