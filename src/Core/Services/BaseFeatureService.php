<?php

declare(strict_types=1);

namespace Core\Services;

/**
 * Provides common utility services to feature controllers,
 * such as data transformation.
 *
 * This service acts as a hub for generic, non-feature-specific operations
 * that might be needed across various CRUD controllers.
 *
 * @package Core\Services
 * @author  GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class BaseFeatureService
{
    /**
     * @param DataTransformerService $dataTransformerService The service for transforming data.
     */
    public function __construct(
        private DataTransformerService $dataTransformerService
    ) {
    }

    /**
     * Transforms data for display purposes.
     *
     * @param array<string, mixed> $data The data to transform.
     * @param string $pageKey The name of the page/context for transformation rules.
     * @param string $entityName The name of the entity (e.g., 'user', 'post').
     * @return array<string, mixed> The transformed data.
     */
    public function transformToDisplay(array $data, string $pageKey, string $entityName): array
    {
        return $this->dataTransformerService->toDisplay($data, $pageKey, $entityName);
    }


    /**
     * Transforms data for storage purposes.
     *
     * @param array<string, mixed> $data The data to transform.
     * @param string $pageKey The name of the page/context for transformation rules.
     * @param string $entityName The name of the entity (e.g., 'user', 'post').
     * @return array<string, mixed> The transformed data.
     */
    public function transformToStorage(array $data, string $pageKey, string $entityName): array
    {
        return $this->dataTransformerService->toStorage($data, $pageKey, $entityName);
    }

    // You can add other generic utility methods here as needed,
    // e.g., for common validation, formatting, etc.
}
