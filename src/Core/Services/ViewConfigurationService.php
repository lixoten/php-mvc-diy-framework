<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Interfaces\ConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * Service responsible for loading and merging view configurations
 * from various sources (default, feature-specific, page-specific)
 *
 * Configuration hierarchy (highest to lowest priority):
 * 1. Page-specific: src/App/Features/{Feature}/Config/{page}_view.php
 * 2. Entity-specific: src/App/Features/{Feature}/Config/{entity}_view.php (if needed)
 * 3. Base/default: src/Config/view.view.php
 */
class ViewConfigurationService
{
    public function __construct(
        protected ConfigInterface $configService,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * Load and merge view configuration for a specific page/entity context
     *
     * @param string $pageKey Page identifier (e.g., 'testy_edit', 'user_login')
     * @param string $pageName Page name (e.g., 'testy', 'user')
     * @param string $pageFeature Feature name (e.g., 'Testy', 'Auth')
     * @param string $pageEntity Entity name (e.g., 'testy', 'user')
     * @return array<string, mixed> Merged configuration array
     */
    public function loadConfiguration(
        string $pageKey,
        string $pageName,
        string $pageAction,
        string $pageFeature,
        string $pageEntity,
    ): array {
        // 1. Load base/default configuration
        // Finloc loadBaseConfiguration
        $baseConfig = $this->loadBaseConfiguration();

        // 2. Load page-specific configuration
        $pageConfig = $this->loadPageConfiguration($pageFeature, $pageKey, $pageName, $pageAction);

        // 3. Merge configurations (page > base)
        $mergedRenderOptions = $this->deepMerge(
            $baseConfig['render_options'] ?? [],
            $pageConfig['render_options'] ?? []
        );

        // Layout: page-specific takes precedence
        $layout = $pageConfig['view_layout'] ?? [];

        // Hidden fields: page-specific takes precedence
        $hiddenFields = $pageConfig['view_hidden_fields'] ?? [];

        return [
            'render_options' => $mergedRenderOptions,
            'layout' => $layout,
            'hidden_fields' => $hiddenFields,
        ];
    }

    /**
     * Load base/global view configuration
     *
     * @return array<string, mixed>
     */
    protected function loadBaseConfiguration(): array
    {
        try {
            // Loads from src/Config/view.view.php
            $config = $this->configService->get('view.view') ?? [];
            return [
                'render_options' => $config['render_options'] ?? []
            ];
        } catch (\Exception $e) {
            $this->logger->warning('ViewConfigurationService: Failed to load base configuration', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Load page-specific view configuration
     *
     * Example: src/App/Features/Testy/Config/testy_view_edit.php
     *
     * @param string $pageFeature Feature name (e.g., 'Testy')
     * @param string $pageKey Page name (e.g., 'testy_edit')
     * @return array<string, mixed>
     */
    protected function loadPageConfiguration(
        string $pageFeature,
        string $pageKey,
        string $pageName,
        string $pageAction
    ): array {
        try {
            // Extract entity name from page name (e.g., 'testy_edit' -> 'testy')
            // $useEntity = explode('_', $pageKey)[0];
            // $action = explode('_', $pageKey)[1] ?? 'view';

            // Loads from src/Config/view.view.php
            // Build config key: testy_view_edit
            $configKey = "{$pageName}_view_{$pageAction}";
            $config = $this->configService->getFromFeature($pageFeature, $configKey) ?? [];

            return $config;
        } catch (\Exception $e) {
            $this->logger->debug('ViewConfigurationService: No page-specific configuration found', [
                'feature' => $pageFeature,
                'page' => $pageKey,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Deep merge multiple arrays, with later arrays taking precedence
     *
     * @param array<string, mixed> ...$arrays
     * @return array<string, mixed>
     */
    protected function deepMerge(array ...$arrays): array
    {
        $result = [];

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
                    $result[$key] = $this->deepMerge($result[$key], $value);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }
}
