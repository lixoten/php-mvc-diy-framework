<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Exceptions\SchemaDefinitionException;
use Core\Interfaces\ConfigInterface;

/**
 * Service responsible for loading table schema definitions from configuration files.
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

    /**
     * Load schema definition for an table.
     *
     * @param string $featureName The name of the Feature (e.g., 'Post', 'User').
     * @param string $tableName The name of the Table (e.g., 'post', 'user').
     * @return array<string, mixed> The loaded schema definition.
     * @throws SchemaDefinitionException If the schema cannot be found or is invalid.
     */
    public function load(string $featureName, string $tableName): array
    {
        // Load from feature-specific config: src/App/Features/{featureName}/Config/{tableName}_schema.php
        $schema = $this->config->getFromFeature($featureName, $tableName . '_schema');

        // Check if config was loaded successfully
        if ($schema === null) {
            throw new SchemaDefinitionException(
                "Schema file not found for table: {$tableName}. " .
                "Expected: src/App/Features/{$featureName}/Config/{$tableName}_schema.php"
            );
        }

        // Validate that the loaded config is an array
        if (!is_array($schema)) {
            throw new SchemaDefinitionException(
                "Invalid schema definition for table: {$tableName}. Schema must be an array."
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
            $schema = $this->config->getFromFeature($featureName, $featureName . '_schema');

            // Only add if schema exists and is valid
            if ($schema !== null && is_array($schema)) {
                $entities[] = $featureName;
            }
        }

        sort($entities);

        return array_unique($entities);
    }
}
