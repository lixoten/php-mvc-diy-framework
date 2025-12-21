<?php

declare(strict_types=1);

namespace Core\Console\Generators;

use Core\Exceptions\SchemaDefinitionException;
use Core\Console\Generators\GeneratorOutputService;

/**
 * Generates Entity classes from schema definitions.
 */
class EntityGenerator
{
    private GeneratorOutputService $generatorOutputService;

    /**
     * @param GeneratorOutputService $generatorOutputService The service for managing output directories.
     */
    public function __construct(GeneratorOutputService $generatorOutputService)
    {
        $this->generatorOutputService = $generatorOutputService;
    }

    /**
     * Generate Entity class from schema.
     *
     * @param array<string, mixed> $schema Schema definition
     * @return string File path of generated entity
     * @throws SchemaDefinitionException
     * @throws \RuntimeException If the output directory cannot be created or file cannot be written.
     */
    public function generate(array $schema): string
    {
        if (empty($schema['entity']['name'])) {
            throw new SchemaDefinitionException('Invalid schema: missing entity name.');
        }

        $entityName = $schema['entity']['name'];
        $fields = $schema['fields'] ?? [];

        // Use the service to get the output directory
        $outputDir = $this->generatorOutputService->getEntityOutputDir($entityName);
        $filePath = $outputDir . $entityName . '.php'; // Entity files are named directly after the entity

        $entityContent = $this->generateEntityClass($entityName, $fields);

        // Write file directly
        $success = file_put_contents($filePath, $entityContent);
        if ($success === false) {
            throw new \RuntimeException("Failed to write entity file: $filePath");
        }

        return $filePath;
    }

    /**
     * Generate the PHP code for the Entity class.
     *
     * @param string $entityName
     * @param array<string, array<string, mixed>> $fields
     * @return string
     */
    protected function generateEntityClass(string $entityName, array $fields): string
    {
        $generatedTimestamp = $this->generatorOutputService->getGeneratedFileTimestamp();

        $properties = $this->generateProperties($entityName, $fields);
        $methods = $this->generateMethods($entityName, $fields);

        // --- START FIX: Conditionally add use statement for dynamically named Status Enum ---
        $usesStatusEnum = false;
        foreach ($fields as $fieldName => $config) {
            // if ($fieldName === 'status' && ($config['db_type'] ?? '') === 'enum' && ($config['length'] ?? 0) === 1) {
            if ($fieldName === 'status' && ($config['db_type'] ?? '') === 'enum') {
                $usesStatusEnum = true;
                break;
            }
        }

        $useStatements = [];
        if ($usesStatusEnum) {
            $useStatements[] = "use App\\Enums\\{$entityName}Status;";
        }
        $useStatementsString = !empty($useStatements) ? implode("\n", $useStatements) . "\n" : '';
        // --- END FIX ---

        $nameSpace = "App\Features\\{$entityName}";
        $php = <<<PHP
<?php

declare(strict_types=1);

namespace $nameSpace;

{$useStatementsString}
/**
 * Generated File - Date: {$generatedTimestamp}
 * Entity class for {$entityName}.
 *
 * @property-read array<string, mixed> \$fields
 */
class {$entityName}
{
{$properties}
{$methods}
}

PHP;

        return $php;
    }

    /**
     * Generate property declarations with PHPDoc.
     *
     * @param string $entityName
     * @param array<string, array<string, mixed>> $fields
     * @return string
     */
    protected function generateProperties(string $entityName, array $fields): string
    {
        $lines = [];
        foreach ($fields as $fieldName => $config) {
            // --- START FIX: Use enhanced phpType and derive defaults ---
            $phpType = $this->phpType($config, $fieldName, $entityName); // FIX: Pass $fieldName and $entityName
            $nullable = $config['nullable'] ?? false;

            $defaultValue = 'null'; // Default for nullable strings/objects
            if ($phpType === 'int') {
                $defaultValue = $nullable ? 'null' : '0';
            } elseif ($phpType === 'float') {
                $defaultValue = $nullable ? 'null' : '0.0';
            } elseif ($phpType === 'bool') {
                $defaultValue = $nullable ? 'null' : 'false';
            } elseif ($phpType === 'array') { // For 'roles'
                $defaultValue = '[]';
                $nullable = false; // PHP array properties should be initialized, not null
            } elseif ($phpType === 'string') {
                $defaultValue = $nullable ? 'null' : "''"; // Non-nullable strings default to empty string
            } elseif ($phpType === 'enum') { // For 'roles'
                $capsFieldName = $this->toCapitalizationCase($fieldName);
                //if (str_ends_with($capsFieldName, 'Status')) {
                    // Assuming a 'PENDING' case exists in all status enums

                    // $enumShortName = "App\\Enums\\{$entityName}$capsFieldName";
                    $enumShortName = "{$entityName}$capsFieldName";
                    //$phpType = $enumShortName;
                    $defaultValue = "{$enumShortName}::PENDING";
                    $nullable = false; // Enum property itself is not nullable, default value handles absence
                //}
            }

            // Determine DocBlock type
            $docBlockType = $phpType;
            if ($phpType === 'enum') {
                $docBlockType = $enumShortName;
            } elseif ($phpType === 'array') {
                $docBlockType = 'array<string>'; // Specific DocBlock for roles array
            }

            // Generate the property declaration
            if ($phpType === 'enum') {
                $lines[] = "    /**\n     * @var {$docBlockType}\n     */\n    private " . // fixme temp out
                // $lines[] = "    private " .
                        "{$enumShortName} \${$fieldName} = {$defaultValue};";
            } else {
                $lines[] = "    /**\n     * @var {$docBlockType}\n     */\n    private " . // fixme temp out
                // $lines[] = "    private " .
                            ($nullable && !str_ends_with($phpType, 'Status') && $phpType !== 'array' ? '?' : '') .
                            "{$phpType} \${$fieldName} = {$defaultValue};";
            }
        }
        return implode("\n\n", $lines);
    }

    /**
     *
     * @param string $inputString The string to convert.
     * @return string The Capitalized string.
     */
    protected function toCapitalizationCase(string $inputString): string
    {
        $lowercase = strtolower($inputString);
        return ucfirst($lowercase);
    }



    /**
     * Generate getter and setter methods for each property.
     *
     * @param string $entityName
     * @param array<string, array<string, mixed>> $fields
     * @return string
     */
    protected function generateMethods(string $entityName, array $fields): string
    {
        $methods = [];
        foreach ($fields as $fieldName => $config) {
            $type = $this->phpType($config);
            if ($type === 'enum') {
                $capsFieldName = $this->toCapitalizationCase($fieldName);
                $enumShortName = "{$entityName}$capsFieldName";
                $type = $enumShortName;
            }
            $nullable = $config['nullable'] ?? false;
            $ucField = str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)));
            $getterType = ($nullable ? '?' : '') . $type;
            $setterType = ($nullable ? '?' : '') . $type;

            // Getter
            $methods[] = <<<PHP
    /**
     * @return {$getterType}
     */
    public function get{$ucField}(): {$getterType}
    {
        return \$this->{$fieldName};
    }
PHP;

            // Setter
            $methods[] = <<<PHP
    /**
     * @param {$setterType} \${$fieldName}
     * @return self
     */
    public function set{$ucField}({$setterType} \${$fieldName}): self
    {
        \$this->{$fieldName} = \${$fieldName};
        return \$this;
    }
PHP;
        }
        return implode("\n\n", $methods);
    }

    /**
     * Map schema db_type to PHP type.
     *
     * @param array<string, mixed> $config
     * @return string
     */
    protected function phpType(array $config): string
    {
        $dbType = $config['db_type'] ?? 'string';

        //    if ($fieldName === 'status' && $dbType === 'char' && ($config['length'] ?? 0) === 1) {
        //         return "App\\Enums\\{$entityName}Status"; // Return the FQCN for the specific Status enum
        //     }
        $fieldName = 'status';
        $entityName = 'User';

        // if ($dbType === 'enum'){
        //     $capsFieldName = $this->toCapitalizationCase($fieldName);
        //     $enumShortName = "{$entityName}$capsFieldName";
        // }

        return match ($dbType) {
            'bigIncrements', 'bigInteger', 'integer', 'foreignId' => 'int',
            'boolean' => 'bool',
            'decimal', 'float', 'double' => 'float',
            'date', 'dateTime', 'time', 'string', 'char', 'text' => 'string',
            'array' => 'array',
            // 'enum' => $enumShortName,
            'enum' => 'enum',
            default => 'string',
        };
    }
}
