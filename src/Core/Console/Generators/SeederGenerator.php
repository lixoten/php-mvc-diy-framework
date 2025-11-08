<?php

declare(strict_types=1);

namespace Core\Console\Generators;

use Core\Exceptions\SchemaDefinitionException;
use Core\Services\PathResolverService;
use RuntimeException;

/**
 * Generates Seeder files from entity schema definitions.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class SeederGenerator
{
    private GeneratorOutputService $generatorOutputService;
    private PathResolverService $pathResolverService;

    /**
     * @param GeneratorOutputService $generatorOutputService The service for managing output directories.
     * @param PathResolverService $pathResolverService The service for resolving application paths.
     */
    public function __construct(
        GeneratorOutputService $generatorOutputService,
        PathResolverService $pathResolverService
    ) {
        $this->generatorOutputService = $generatorOutputService;
        $this->pathResolverService = $pathResolverService;
    }

    /**
     * Generate a seeder file for the given entity schema.
     *
     * @param array<string, mixed> $schema The entity schema definition.
     * @return string The generated file path.
     * @throws SchemaDefinitionException If the schema is invalid or missing required parts.
     * @throws RuntimeException If the output directory cannot be created or file cannot be written.
     */
    public function generate(array $schema): string
    {
        if (empty($schema['entity']['name']) || empty($schema['fields'])) {
            throw new SchemaDefinitionException('Invalid schema: missing entity name or fields.');
        }

        $entityName = $schema['entity']['name'];
        $tableName = $schema['entity']['table'] ?? strtolower($entityName);

        $className = $entityName . 'Seeder';
        // Use PathResolverService to get the seeder path
        // $outputDir = $this->pathResolverService->getDatabaseSeedersPath();
        $outputDir = $this->pathResolverService->getGeneratedEntityPath($entityName);

        // Ensure the directory exists
        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
                throw new RuntimeException("Failed to create output directory: {$outputDir}");
            }
        }

        // Generate a unique filename with timestamp
        $fileName = date('Ymd_His') . "_{$className}.php";
        $filePath = $outputDir . '/' . $fileName;

        if (file_exists($filePath)) {
            throw new RuntimeException("Seeder file '{$filePath}' already exists.");
        }

        // Get the generated timestamp from the service
        $generatedTimestamp = $this->generatorOutputService->getGeneratedFileTimestamp();

        $fields = $schema['fields'];

        // NEW CODE START: Determine unique fields from schema for createIfNotExists
        $uniqueFields = [];
        if (isset($schema['fields']) && is_array($schema['fields'])) {
            foreach ($schema['fields'] as $fieldName => $fieldDefinition) {
                if (isset($fieldDefinition['unique']) && $fieldDefinition['unique'] === true) {
                    $uniqueFields[] = $fieldName;
                }
            }
        }
        // If no unique fields are explicitly defined, default to an empty array
        $uniqueCheckString = empty($uniqueFields) ? '[]' : "['" . implode("', '", $uniqueFields) . "']";
        // NEW CODE END



        $sampleDataArray = $schema['sample_data'] ?? null;
        if ($sampleDataArray && is_array($sampleDataArray)) {
            $sampleData = $this->formatSampleData($sampleDataArray);
        } else {
            $sampleData = $this->generateSampleData($fields);
        }

        $relatedIdCode = '';
        if (array_key_exists('user_id', $fields)) {
            $relatedIdCode .= <<<PHP
        \$userId = null;
        \$users = \$this->db->query("SELECT id FROM user LIMIT 1"); // Assuming 'user' table and 'id' column
        if (!empty(\$users)) {
            \$userId = \$users[0]['id'];
        } else {
            throw new \RuntimeException("No user found in 'user' table. Please seed users first.");
        }

PHP;
        }
        if (array_key_exists('store_id', $fields)) {
            $relatedIdCode .= <<<PHP
        \$storeId = null;
        \$stores = \$this->db->query("SELECT id FROM store LIMIT 1"); // Assuming 'store' table and 'id' column
        if (!empty(\$stores)) {
            \$storeId = \$stores[0]['id'];
        } else {
            throw new \RuntimeException("No store found in 'store' table. Please seed stores first.");
        }

PHP;
        }


        $php = <<<PHP
<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\Seeders\Seeder;
use Core\Database\ConnectionInterface;

/**
 * Generated File - Date: {$generatedTimestamp}
 * Seeder for '{$tableName}' table.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class {$className} extends Seeder
{
    /**
     * @param ConnectionInterface \$db The database connection.
     */
    public function __construct(ConnectionInterface \$db)
    {
        parent::__construct(\$db);
    }

    /**
     * Seed the '{$tableName}' table with sample data.
     *
     * @return void
     */
    public function run(): void
    {
        \$this->requireTable('{$tableName}');

{$relatedIdCode}
        \$records = [
{$sampleData}
        ];

        \$inserted = 0;
        foreach (\$records as \$record) {
            // Assuming 'slug' is the unique field for createIfNotExists
            //if (\$this->createIfNotExists('{$tableName}', \$record, ['slug'])) {
            if (\$this->createIfNotExists('{$tableName}', \$record, {$uniqueCheckString})) {
                \$inserted++;
            }
        }
        \$countTried = count(\$records);
        echo "Seeded {\$inserted} {$tableName} records successfully out of {\$countTried} records.\\n";
    }
}

PHP;

        $success = file_put_contents($filePath, $php);
        if ($success === false) {
            throw new RuntimeException("Failed to write seeder file: {$filePath}");
        }

        return $filePath;
    }

    /**
     * Generate sample data array for seeder.
     *
     * @param array<string, array<string, mixed>> $fields Schema field definitions.
     * @return string
     */
    protected function generateSampleData(array $fields): string
    {
        $sample = [];
        foreach ($fields as $field => $config) {
            if (!empty($config['auto_increment']) || !empty($config['primary'])) {
                continue;
            }

            if (in_array($field, ['user_id', 'store_id', 'store_user_id'], true)) {
                $value = '$' . lcfirst(str_replace('_id', 'Id', $field));
            } elseif (isset($config['nullable']) && $config['nullable']) {
                $value = 'null';
            } elseif (isset($config['default'])) {
                $value = var_export($config['default'], true);
            } elseif (($config['db_type'] ?? '') === 'boolean') {
                $value = 'false';
            } elseif (($config['db_type'] ?? '') === 'integer' || ($config['db_type'] ?? '') === 'bigInteger') {
                $value = '0';
            } elseif (
                      ($config['db_type'] ?? '') === 'decimal' ||
                      ($config['db_type'] ?? '') === 'float' ||
                      ($config['db_type'] ?? '') === 'double'
            ) {
                $value = '0.0';
            } elseif (($config['db_type'] ?? '') === 'date') {
                $value = "'1970-01-01'";
            } elseif (($config['db_type'] ?? '') === 'time') {
                $value = "'00:00:00'";
            } elseif (($config['db_type'] ?? '') === 'dateTime') {
                $value = "'1970-01-01 00:00:00'";
            } else {
                $value = "'sample_{$field}'";
            }
            $sample[] = "                '{$field}' => {$value},";
        }
        // Add created_at/updated_at if needed
        if (isset($fields['created_at'])) {
            $sample[] = "                'created_at' => date('Y-m-d H:i:s'),";
        }
        if (isset($fields['updated_at'])) {
            $sample[] = "                'updated_at' => date('Y-m-d H:i:s'),";
        }
        $sampleBlock = "            [\n" . implode("\n", $sample) . "\n            ]";
        return $sampleBlock;
    }

    /**
     * Format sample data for seeder output.
     *
     * @param array<int, array<string, mixed>> $records An array of records, where each record is an associative
     *                                         array of field => value.
     * @return string
     */
    protected function formatSampleData(array $records): string
    {
        $lines = [];
        foreach ($records as $record) {
            $fields = [];
            foreach ($record as $key => $value) {
                // All string literals should be quoted.
                $exportedValue = match (true) {
                    $value === null => 'null',
                    is_bool($value) => $value ? 'true' : 'false',
                    is_string($value) => $this->formatStringValue($value, $key), // All strings go through here
                    default => var_export($value, true),
                };
                $fields[] = "                '" . addslashes($key) . "' => " . $exportedValue . ",";
            }
            $lines[] = "            [\n" . implode("\n", $fields) . "\n            ]";
        }
        return implode(",\n", $lines);
    }


    /**
     * Formats a string value, splitting it into multiple lines if it exceeds the line length limit.
     *
     * @param string $value The string value to format.
     * @param string $key The key associated with the value, used for indentation calculation.
     * @return string The formatted string.
     */
    protected function formatStringValue(string $value, string $key): string
    {
        // Calculate the current line prefix length for this specific key
        // "                '" . addslashes($key) . "' => "
        $prefixLength = 17 + strlen(addslashes($key)) + 5; // 17 for "                '", 5 for "' => "

        // Remaining characters for the value on the first line, considering the trailing comma and potential quotes
        $availableLength = 110 - $prefixLength - 1; // -1 for the trailing comma

        // If the string value itself (plus its quotes from var_export) fits, just export it
        if (strlen($value) + 2 <= $availableLength) { // +2 for the quotes added by var_export
            return var_export($value, true);
        }

        // If it's too long, split it
        $segments = [];
        $currentPos = 0;
        $stringLength = strlen($value);

        // The first segment can be longer as it starts on the same line as the key
        // Ensure firstSegmentLength is at least 1 to avoid substr issues with 0 length
        $firstSegmentLength = max(1, $availableLength - 2); // -2 for the quotes

        // Handle the first segment
        if ($firstSegmentLength > 0 && $currentPos < $stringLength) {
            $segment = substr($value, $currentPos, $firstSegmentLength);
            $segments[] = var_export($segment, true);
            $currentPos += $firstSegmentLength;
        }

        // For subsequent segments, they will be on new lines with indentation
        // Align with the start of the value on the first line, plus ' . '
        $indentation = $prefixLength + 3;
        // Max length for subsequent segments, considering indentation and quotes
        $segmentLength = max(1, 110 - $indentation - 2); // -2 for quotes

        while ($currentPos < $stringLength) {
            $segment = substr($value, $currentPos, $segmentLength);
            $segments[] = var_export($segment, true);
            $currentPos += $segmentLength;
        }

        // If for some reason only one segment was created (e.g., very short string but still triggered split logic)
        if (count($segments) <= 1) {
            return var_export($value, true);
        }

        $indentString = str_repeat(' ', $indentation);
        return implode(" .\n" . $indentString, $segments);
    }
}
