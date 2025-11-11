<?php

declare(strict_types=1);

namespace Core\Console\Generators;

use Core\Exceptions\SchemaDefinitionException;
use RuntimeException;

/**
 * Service responsible for generating feature-specific field configuration files.
 *
 * This generator reads an entity's schema definition and produces a
 * `{entityName}_fields.php` file within the feature's Config directory.
 * This file defines how each field should be rendered in forms and lists,
 * including labels, input types, attributes, formatters, and validators.
 *
 * @package   MVC LIXO Framework
 * @author    Your Name <your@email.com>
 * @copyright Copyright (c) 2025
 */
class ConfigFieldsGenerator
{
    private GeneratorOutputService $generatorOutputService;

    /**
     * param SchemaLoaderService $schemaLoaderService The service for loading schema definitions.
     * @param GeneratorOutputService $generatorOutputService The service for managing generator output.
     */
    public function __construct(
        GeneratorOutputService $generatorOutputService,
    ) {
        $this->generatorOutputService = $generatorOutputService;
    }

     /**
     * Generate the field configuration file for a given entity schema.
     *
     * @param array<string, mixed> $schema The entity schema definition.
     * @return string The absolute path to the generated field config file.
     * @throws SchemaDefinitionException If the schema is invalid or not found.
     * @throws \RuntimeException If the output directory cannot be created or file cannot be written.
     */
    public function generate(array $schema): string
    {
        if (empty($schema['entity']['name'])) {
            throw new SchemaDefinitionException('Invalid schema: missing entity name.');
        }
        $entityName = $schema['entity']['name'];
        $fields = $schema['fields'] ?? [];

        if (empty($fields)) {
            throw new SchemaDefinitionException("Schema for '{$entityName}' has no fields defined.");
        }

        // Get the output directory for the feature's config
        $outputDir = $this->generatorOutputService->getEntityOutputDir($entityName);

        // Ensure the directory exists
        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
                throw new RuntimeException("Failed to create output directory: {$outputDir}");
            }
        }

        $filePath = $outputDir . strtolower($entityName) . "_fields" . '.php';
        $fileContent = $this->generateFieldConfigFileContent($entityName, $fields);

        $success = file_put_contents($filePath, $fileContent);
        if ($success === false) {
            throw new RuntimeException("Failed to write field config file: {$filePath}");
        }

        return $filePath;
    }

    /**
     * Generate the PHP code for the field configuration file.
     *
     * @param string $entityName The name of the entity.
     * @param array<string, array<string, mixed>> $fields Schema field definitions.
     * @return string The PHP code for the field config file.
     */
    protected function generateFieldConfigFileContent(string $entityName, array $fields): string
    {
        $generatedTimestamp = $this->generatorOutputService->getGeneratedFileTimestamp();
        $entityNameLower = strtolower($entityName);
        $fieldDefinitions = [];

        foreach ($fields as $fieldName => $config) {
            // Skip primary key 'id' for form generation, as it's usually auto-incremented
            if (($config['primary'] ?? false) === true) {
                continue;
            }

            $formType = $this->mapDbTypeToFormType($config, $fieldName);
            $labelKey = "{$entityNameLower}.{$fieldName}";
            $placeholderKey = "{$entityNameLower}.{$fieldName}.placeholder";

            // ADDED: Build the 'list' section if defined in schema
            $listSection = '';
            if (isset($config['list']) && is_array($config['list'])) {
                $listConfig = $config['list'];
            } else {
                $listConfig = [
                    'sortable' => false,
                    'formatter' => null,
                ];
            }

            $sortable = isset($listConfig['sortable']) ? ($listConfig['sortable'] ? 'true' : 'false') : 'false';
            $formatter = isset($listConfig['formatter']) ? 'null' : 'null'; // Can be enhanced to support closures

            $listSection = <<<PHP
        'list' => [
            'sortable' => {$sortable},
            'formatter' => {$formatter},
        ],

PHP;
            //}

            // ADDED: Build the 'form' section as a variable for consistency
            $formSection = <<<PHP
        'form' => [
            'type'          => '{$formType}',
            'required'      => {$this->formatBool($config['nullable'] ?? false, true)}, // Required if not nullable
            'attributes'    => [
                'placeholder' => '{$placeholderKey}',
            ],
        ],
PHP;


            $fieldDefinitions[] = <<<PHP
    '{$fieldName}' => [
        'label' => '{$labelKey}',
{$listSection}{$formSection}
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
PHP;
        }

        $fieldDefinitionsString = implode("\n", $fieldDefinitions);

        $php = <<<PHP
<?php

/**
 * Generated File - Date: {$generatedTimestamp}
 * Field definitions for the {$entityName} entity.
 *
 * This file defines how each field should be rendered in forms and lists,
 * including labels, input types, attributes, formatters, and validators.
 */

declare(strict_types=1);

return [
{$fieldDefinitionsString}
];

PHP;

        return $php;
    }

    /**
     * Maps a database type from schema to an appropriate HTML form input type.
     *
     * @param array<string, mixed> $config The field configuration from the schema.
     * @param string $fieldName The name of the field.
     * @return string The HTML input type (e.g., 'text', 'number', 'date').
     */
    protected function mapDbTypeToFormType(array $config, string $fieldName): string
    {
        $dbType = $config['db_type'] ?? 'string';
        $length = $config['length'] ?? 0;

        return match ($dbType) {
            'bigIncrements', 'bigInteger', 'integer', 'foreignId' => 'number',
            'boolean' => 'checkbox',
            'decimal', 'float', 'double' => 'number',
            'date' => 'date',
            'dateTime' => 'datetime-local',
            'time' => 'time',
            'text' => 'textarea',
            'string', 'char' => match (true) {
                str_contains($fieldName, 'email') => 'email',
                str_contains($fieldName, 'password') => 'password',
                str_contains($fieldName, 'url') || str_contains($fieldName, 'address') => 'url',
                str_contains($fieldName, 'color') => 'color',
                $length > 100 => 'textarea', // Long strings might be better as textarea
                default => 'text',
            },
            default => 'text',
        };
    }

    /**
     * Formats a boolean value as 'true' or 'false' string for PHP code generation.
     *
     * @param bool $value The boolean value.
     * @param bool $invert If true, inverts the boolean before formatting.
     * @return string 'true' or 'false'.
     */
    protected function formatBool(bool $value, bool $invert = false): string
    {
        if ($invert) {
            $value = !$value;
        }
        return $value ? 'true' : 'false';
    }
}
