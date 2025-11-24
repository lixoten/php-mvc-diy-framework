<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Interfaces\ConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * Service responsible for loading and merging list configurations
 * from various sources (default, feature-specific, page-specific)
 *
 * Configuration hierarchy (highest to lowest priority):
 * 1. Page-specific: src/App/Features/{Feature}/Config/{page}_view.php
 * 2. Entity-specific: src/App/Features/{Feature}/Config/{entity}_view.php
 * 3. Base/default: src/Config/list.php
 */
class ListConfigurationService
{
    public function __construct(
        protected ConfigInterface $configService,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * Load and merge list configuration for a specific page/entity context
     *
     * @param string $pageKey Page identifier (e.g., 'testy_list')
     * @param string $pageFeature Feature name (e.g., 'Testy')
     * @param string $pageEntity Entity name (e.g., 'testy')
     * @return array<string, mixed> Merged configuration array
     */
    public function loadConfiguration(
        string $pageKey,
        string $pageFeature,
        string $pageEntity,
    ): array {
        // 1. Load base/default configuration
        // Findloc Load Config for base and page 
        $baseConfig = $this->loadBaseConfiguration();

        // 2. Load page-specific configuration
        $pageConfig = $this->loadPageConfiguration($pageFeature, $pageKey);

        // 3. Merge configurations (page > base)
        $mergedOptions = $this->deepMerge(
            $baseConfig['options'] ?? [],
            $pageConfig['options'] ?? []
        );

        $mergedPagination = $this->deepMerge(
            $baseConfig['pagination'] ?? [],
            $pageConfig['pagination'] ?? []
        );

        $mergedRenderOptions = $this->deepMerge(
            $baseConfig['render_options'] ?? [],
            $pageConfig['render_options'] ?? []
        );

        // List fields: page-specific takes precedence, then entity, then base
        $listFields = $pageConfig['list_fields']
            ?? $baseConfig['list_fields']
            ?? [];

        // $this->logger->debug('ListConfigurationService: Configuration loaded', [
        //     'pageKey' => $pageKey,
        //     'pageFeature' => $pageFeature,
        //     'pageEntity' => $pageEntity,
        //     'mergedOptions' => $mergedOptions,
        //     'mergedPagination' => $mergedPagination,
        //     'mergedRenderOptions' => $mergedRenderOptions,
        //     'listFields' => $listFields,
        // ]);

        return [
            'options' => $mergedOptions,
            'pagination' => $mergedPagination,
            'render_options' => $mergedRenderOptions,
            'list_fields' => $listFields,
        ];
    }

    /**
     * Load base/global list configuration
     *
     * @return array<string, mixed>
     */
    protected function loadBaseConfiguration(): array
    {
        try {
            $config = $this->configService->get('view.list') ?? [];
            // $this->logger->debug('ListConfigurationService: Base config loaded', [
            //     'config' => $config
            // ]);
            return $config;
        } catch (\Exception $e) {
            $this->logger->warning('ListConfigurationService: Failed to load base configuration', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Load page-specific list configuration
     *
     * Example: src/App/Features/Testy/Config/testy_list_view.php
     *
     * @param string $pageFeature Feature name (e.g., 'Testy')
     * @param string $pageKey Page name (e.g., 'testy_list')
     * @return array<string, mixed>
     */
    protected function loadPageConfiguration(string $pageFeature, string $pageKey): array
    {
        try {                               // root explode
            $useEntity = explode('_', $pageKey)[0];

            $configKey = "{$useEntity}_view_list"; // findLoc config file testy_view_list
            $config = $this->configService->getFromFeature($pageFeature, $configKey) ?? [];

            // $this->logger->debug('ListConfigurationService: Page config loaded', [
            //     'feature' => $pageFeature,
            //     'page' => $pageKey,
            //     'configKey' => $configKey,
            //     'config' => $config
            // ]);

            return $config;
        } catch (\Exception $e) {
            // $this->logger->debug('ListConfigurationService: No page-specific configuration found', [
            //     'feature' => $pageFeature,
            //     'page' => $pageKey,
            //     'error' => $e->getMessage()
            // ]);
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
