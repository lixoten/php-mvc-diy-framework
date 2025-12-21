<?php

declare(strict_types=1);

namespace Core\Console\Generators;

use Core\Exceptions\SchemaDefinitionException;
use Core\Console\Generators\GeneratorOutputService;

/**
 * Generates migration files from entity schema definitions.
 */
class MigrationGenerator
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
     * Generate a migration file for the given entity schema.
     *
     * @param array<string, mixed> $schema
     * @return string The generated file path
     * @throws SchemaDefinitionException
     * @throws \RuntimeException If the output directory cannot be created or file cannot be written.
     */
    public function generate(array $schema): string
    {
        if (empty($schema['entity']['name']) || empty($schema['fields'])) {
            throw new SchemaDefinitionException('Invalid schema: missing entity name or fields.');
        }

        $entityName = $schema['entity']['name'];
        $tableName = $schema['entity']['table'] ?? strtolower($entityName);

        $className = 'Create' . ucfirst($entityName) . 'Table';
        $fileName = date('Ymd_His') . "_{$className}.php";

        // output directory
        $outputDir = $this->generatorOutputService->getEntityOutputDir($entityName);
        $filePath = $outputDir . $fileName;

        // Get the generated timestamp from the service
        $generatedTimestamp = $this->generatorOutputService->getGeneratedFileTimestamp();

        // Build the migration class content
        $fieldsCode = $this->generateFieldsCode($schema['fields'], $schema);

        $php = <<<PHP
<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;
use Core\Database\Schema\Blueprint;

/**
 * Generated File - Date: {$generatedTimestamp}
 * Migration for creating the '{$tableName}' table.
 */
class {$className} extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up(): void
    {
        // Idempotent guard: skip if table already exists
        if (\$this->schema->hasTable('{$tableName}')) {
            return;
        }

        \$this->create('{$tableName}', function (Blueprint \$table) {
{$fieldsCode}
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        \$this->drop('{$tableName}');
    }
}

PHP;


        $success = file_put_contents($filePath, $php);
        if ($success === false) {
            throw new \RuntimeException("Failed to write migration file: $filePath");
        }

        return $filePath;
    }

    /**
     * Generate PHP code for table fields.
     *
     * @param array<string, array<string, mixed>> $fields
     * @param array<string, mixed> $schema Complete schema including indexes
     * @return string
     */
    protected function generateFieldsCode(array $fields, array $schema = []): string
    {
        $lines = [];

        foreach ($fields as $fieldName => $field) {
            $line = "            \$table->" . $this->buildFieldDefinition($fieldName, $field);
            $lines[] = $line;
        }

        // Add timestamps if specified in entity metadata
        if (($schema['entity']['timestamps'] ?? false) === true) {
            $lines[] = "            \$table->timestamps();";
        }

        // Add CHECK constraints from field definitions
        //$checkConstraints = $this->generateCheckConstraints($fields);
        $checkConstraints = $this->generateCheckConstraints($fields, $schema['entity']['table'] ?? '');
        if (!empty($checkConstraints)) {
            $lines[] = "";
            $lines[] = "            // CHECK Constraints";
            $lines = array_merge($lines, $checkConstraints);
        }

        // Add foreign keys after field definitions
        $foreignKeys = $this->generateForeignKeys($fields);
        if (!empty($foreignKeys)) {
            $lines[] = "";  // Empty line before foreign keys
            $lines[] = "            // Foreign Keys";
            $lines = array_merge($lines, $foreignKeys);
        }

        // Add all indexes (field-level unique, foreign key, and schema-level)
        $allIndexes = $this->generateSchemaIndexes($schema['indexes'] ?? [], $fields);
        if (!empty($allIndexes)) {
            $lines[] = "";  // Empty line before indexes
            $lines[] = "            // Indexes";
            $lines = array_merge($lines, $allIndexes);
        }

        return implode("\n", $lines);
    }

    /**
     * Build field definition with proper db_type and constraints.
     *
     * @param string $fieldName
     * @param array<string, mixed> $field
     * @return string
     */
    protected function buildFieldDefinition(string $fieldName, array $field): string
    {
        $dbType = $field['db_type'] ?? 'string';

        // Map 'array' db_type to 'json' Blueprint method
        if ($dbType === 'array') {
            $dbType = 'json';
        }

        // Map 'enum' db_type to 'char' Blueprint method with length from schema
        // Per instructions: "the framework's schema definition system *may* use `db_type: 'enum'`
        // as an abstraction to represent a field that will be persisted as a portable `CHAR` or `VARCHAR`
        // type with a `CHECK` constraint."
        if ($dbType === 'enum') {
            $dbType = 'char';
            // If length is not explicitly provided for enum, default to 1 (common for single-char status codes)
            if (!isset($field['length'])) {
                $field['length'] = 1;
            }
        }


        if (in_array($dbType, ['bigIncrements', 'increments'])) {
            return "{$dbType}('{$fieldName}');";  // No additional constraints needed
        }

        // Handle timestamps() method
        if ($dbType === 'timestamps') {
            return "timestamps();";  // No field name needed for timestamps()
        }

        $args = ["'{$fieldName}'"];

        // Add length for string types
        if (isset($field['length']) && in_array($dbType, ['string', 'char'])) {
            $args[] = (int)$field['length'];
        }


        // Add precision/scale for decimal types
        if ($dbType === 'decimal' && isset($field['precision'], $field['scale'])) {
            $args[] = (int)$field['precision'];
            $args[] = (int)$field['scale'];
        }

        $definition = "{$dbType}(" . implode(', ', $args) . ")";

        // Add method chaining for constraints
        $constraints = [];

        if (isset($field['nullable']) && $field['nullable'] === false) {
            // Don't add ->nullable(false) for primary keys or auto-increment fields
            if (!($field['primary'] ?? false) && !($field['auto_increment'] ?? false)) {
                $constraints[] = "nullable(false)";
            }
        } elseif (isset($field['nullable']) && $field['nullable'] === true) {
            $constraints[] = "nullable()";
        }

        if (isset($field['unsigned']) && $field['unsigned']) {
            $constraints[] = "unsigned()";
        }

        if (isset($field['auto_increment']) && $field['auto_increment']) {
            $constraints[] = "autoIncrement()";
        }

        if (isset($field['primary']) && $field['primary']) {
            $constraints[] = "primary()";
        }

        if (isset($field['default'])) {
            $default = $field['default'];
            if (is_string($default)) {
                $constraints[] = "default('{$default}')";
            } else {
                $constraints[] = "default(" . var_export($default, true) . ")";
            }
        }

        if (isset($field['comment'])) {
            $constraints[] = "comment('" . addslashes($field['comment']) . "')";
        }

        if (!empty($constraints)) {
            $definition .= "\n                    ->" . implode("\n                    ->", $constraints);
        }

        return $definition . ";";
    }

    /**
     * Generate foreign key constraints.
     *
     * @param array<string, array<string, mixed>> $fields
     * @return array<string>
     */
    protected function generateForeignKeys(array $fields): array
    {
        $foreignKeys = [];

        foreach ($fields as $fieldName => $field) {
            if (isset($field['foreign_key'])) {
                $fk = $field['foreign_key'];
                $table = $fk['table'];
                $column = $fk['column'] ?? 'id';
                $constraintName = $fk['name'] ?? "fk_{$table}_{$fieldName}";
                $onDelete = $fk['on_delete'] ?? 'RESTRICT';
                $onUpdate = $fk['on_update'] ?? 'RESTRICT'; // Added onUpdate

                $foreignKeys[] = "            \$table->foreign('{$fieldName}', '{$table}', " .
                                "'{$column}', '{$constraintName}')";
                $foreignKeys[] = "                ->onDelete('{$onDelete}')";
                if ($onUpdate !== 'RESTRICT') { // Only add onUpdate if it's not the default RESTRICT
                    $foreignKeys[] = "                ->onUpdate('{$onUpdate}');";
                } else {
                    $foreignKeys[] = "                ;";
                }
            }
        }

        return $foreignKeys;
    }

    /**
     * Generate all index definitions (unique from fields, foreign key defaults, and schema-level).
     * This method is the single source for all index generation.
     *
     * @param array<string, mixed> $schemaIndexes
     * @param array<string, array<string, mixed>> $fields
     * @return array<string>
     */
    protected function generateSchemaIndexes(array $schemaIndexes, array $fields): array
    {
        $indexes = [];
        // Use a map to track columns that already have an index generated, to prevent duplicates
        // Key: column name (string), Value: true (bool)
        $indexedColumns = [];

        // --- Phase 1: Process implicit indexes from field definitions (unique, foreign keys) ---
        foreach ($fields as $fieldName => $fieldDefinition) {
            // Primary keys are implicitly indexed by PRIMARY KEY constraint, no need to add another index line
            if (($fieldDefinition['primary'] ?? false) === true) {
                $indexedColumns[$fieldName] = true;
                continue;
            }

            // Handle unique fields (e.g., 'slug' => ['unique' => true])
            if (isset($fieldDefinition['unique']) && $fieldDefinition['unique'] === true) {
                // Check if an explicit unique index for this field already exists in schemaIndexes
                $explicitUniqueExists = false;
                foreach ($schemaIndexes as $sIndex) {
                    if (
                        (isset($sIndex['type']) && $sIndex['type'] === 'unique') &&
                        (
                            isset($sIndex['columns']) &&
                            is_array($sIndex['columns']) &&
                            count($sIndex['columns']) === 1 &&
                            $sIndex['columns'][0] === $fieldName
                        )
                    ) {
                        $explicitUniqueExists = true;
                        break;
                    }
                }

                // If no explicit unique index exists for this single column, add a simple unique index
                if (!$explicitUniqueExists && !isset($indexedColumns[$fieldName])) {
                    $indexes[] = "            \$table->unique('{$fieldName}');";
                    $indexedColumns[$fieldName] = true;
                }
            }

            // Handle foreign keys (e.g., 'user_id' => ['foreign_key' => [...]])
            if (isset($fieldDefinition['foreign_key'])) {
                // Check if an explicit index for this foreign key column already exists in schemaIndexes
                $explicitFkIndexExists = false;
                foreach ($schemaIndexes as $sIndex) {
                    if (
                        (isset($sIndex['type']) && $sIndex['type'] === 'index') &&
                        (
                            isset($sIndex['columns']) &&
                            is_array($sIndex['columns']) &&
                            count($sIndex['columns']) === 1 &&
                            $sIndex['columns'][0] === $fieldName
                        )
                    ) {
                        $explicitFkIndexExists = true;
                        break;
                    }
                }
                // If no explicit index exists for this foreign key column, add a simple index
                if (!$explicitFkIndexExists && !isset($indexedColumns[$fieldName])) {
                    $indexes[] = "            \$table->index('{$fieldName}');";
                    $indexedColumns[$fieldName] = true;
                }
            }
        }

        // --- Phase 2: Process explicit indexes from the 'indexes' array in the schema ---
        // These take precedence and are always added if defined.
        foreach ($schemaIndexes as $index) {
            if (isset($index['name']) && isset($index['columns'])) {
                $indexName = $index['name'];
                $columns = $index['columns'];
                $type = $index['type'] ?? 'index'; // 'index', 'unique'

                if (is_array($columns)) {
                    $columnList = "'" . implode("', '", $columns) . "'";
                    $indexLine = '';
                    if ($type === 'unique') {
                        $indexLine = "            \$table->unique([{$columnList}], '{$indexName}');";
                    } else {
                        $indexLine = "            \$table->index([{$columnList}], '{$indexName}');";
                    }
                    $indexes[] = $indexLine;

                    // Mark columns as indexed by this explicit definition
                    foreach ($columns as $col) {
                        $indexedColumns[$col] = true;
                    }
                }
            }
        }

        return $indexes;
    }

    /**
     * Generate CHECK constraints from field definitions.
     *
     * @param array<string, array<string, mixed>> $fields
     * @param string $tableName The name of the table for which constraints are being generated.
     * @return array<string>
     */
    protected function generateCheckConstraints(array $fields, string $tableName = ''): array
    {
        $constraints = [];

        foreach ($fields as $fieldName => $field) {
            if (isset($field['check'])) {
                // Use the provided constraint name if available, otherwise generate a default one
                // Default to chk_{tableName}_{fieldName} if tableName is available, else chk_{fieldName}
                $constraintName = $field['check_name'] ?? (
                    $tableName ? "chk_{$tableName}_{$fieldName}" : "chk_{$fieldName}"
                );
                $expression = addslashes($field['check']);
                $constraints[] = "            \$table->check('{$expression}', '{$constraintName}');";
            }
        }

        return $constraints;
    }
}
