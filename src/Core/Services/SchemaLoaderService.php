<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Exceptions\SchemaDefinitionException;
use Core\Interfaces\ConfigInterface;

/**
 * Service responsible for loading entity schema definitions from configuration files.
 *
 * This service supports loading schemas from a feature-specific directory first,
 * then falling back to a global schema directory.
 */
class SchemaLoaderService
{
    private ConfigInterface $config;

    /**
     * @param ConfigInterface $config The application configuration service.
     */
    public function __construct(
        ConfigInterface $config,
    ) {
        $this->config = $config;
    }

    // /**
    //  * Load schema definition for an entity.
    //  *
    //  * @param string $entityName The name of the entity (e.g., 'Post', 'User').
    //  * @return array<string, mixed> The loaded schema definition.
    //  * @throws SchemaDefinitionException If the schema file cannot be found or is invalid.
    //  */
    // public function load(string $entityName): array
    // {
    //     // Strictly load from feature-specific config: src/App/Features/{EntityName}/Config/schema_{entityName}.php
    //     $featureSchemaPath = $this->p......athResolverService->getAppFeatureConfigSchemaFilePath($entityName);

    //     if (file_exists($featureSchemaPath)) {
    //         $schema = require $featureSchemaPath;
    //         if (is_array($schema)) {
    //             return $schema;
    //         }
    //         throw new SchemaDefinitionException("Invalid schema definition in feature file: {$featureSchemaPath}");
    //     }

    //     throw new SchemaDefinitionException("Schema file not found for entity: {$entityName}");
    // }

    /**
     * Load schema definition for an entity.
     *
     * @param string $entityName The name of the entity (e.g., 'Post', 'User').
     * @return array<string, mixed> The loaded schema definition.
     * @throws SchemaDefinitionException If the schema cannot be found or is invalid.
     */
    public function load(string $entityName): array
    {
        // Load from feature-specific config: src/App/Features/{EntityName}/Config/schema_{entityName}.php
        $schema = $this->config->getFromFeature($entityName, 'schema_' . $entityName);

        // Check if config was loaded successfully
        if ($schema === null) {
            throw new SchemaDefinitionException(
                "Schema file not found for entity: {$entityName}. " .
                "Expected: src/App/Features/{$entityName}/Config/schema_{$entityName}.php"
            );
        }

        // Validate that the loaded config is an array
        if (!is_array($schema)) {
            throw new SchemaDefinitionException(
                "Invalid schema definition for entity: {$entityName}. Schema must be an array."
            );
        }

        return $schema;
    }


    /**
     * Get all available schema files.
     *
     * @return array<string> A list of entity names (e.g., ['Post', 'User']).
     */
    public function getAvailableEntities(): array
    {
        $entities = [];

        // Get features path using ConfigService's baseSrcPath
        $baseSrcPath = dirname($this->config->getConfigPath());
        $featuresPath = $baseSrcPath . '/App/Features/';

        if (!is_dir($featuresPath)) {
            return [];
        }

        $featureDirs = array_filter(scandir($featuresPath), function ($dir) use ($featuresPath) {
            return $dir !== '.' && $dir !== '..' && is_dir($featuresPath . $dir);
        });

        foreach ($featureDirs as $featureName) {
            // Try to load schema using ConfigService
            $schema = $this->config->getFromFeature($featureName, 'schema_' . $featureName);

            // Only add if schema exists and is valid
            if ($schema !== null && is_array($schema)) {
                $entities[] = $featureName;
            }
        }

        sort($entities);

        return array_unique($entities);
    }


    // /**
    //  * Get all available schema files.
    //  *
    //  * @return array<string> A list of entity names (e.g., ['Post', 'User']).
    //  */
    // public function getAvailableEntities(): array
    // {
    //     $entities = [];

    //     // Scan feature-specific config directories
    //     $featuresPath = $this->p.....athResolverService->getAppPath() . 'Features/';
    //     if (is_dir($featuresPath)) {
    //         $featureDirs = array_filter(scandir($featuresPath), function ($dir) use ($featuresPath) {
    //             return $dir !== '.' && $dir !== '..' && is_dir($featuresPath . $dir);
    //         });

    //         foreach ($featureDirs as $featureName) {
    //             $schemaFilePath = $this->p....athResolverService->getAppFeatureConfigSchemaFilePath($featureName);
    //             if (file_exists($schemaFilePath)) {
    //                 $entities[] = $featureName;
    //             }
    //         }
    //     }

    //     sort($entities);

    //     return array_unique($entities); // Ensure unique entity names
    // }
}
