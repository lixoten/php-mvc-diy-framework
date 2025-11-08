<?php

declare(strict_types=1);

namespace Core\Console\Generators;

use Core\Exceptions\SchemaDefinitionException;
use Core\Console\Generators\GeneratorOutputService;

/**
 * Generates Repository Interface and Implementation from entity schema definitions.
 */
class RepositoryGenerator
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
     * Generate Repository Interface and Implementation from schema.
     *
     * @param array<string, mixed> $schema Schema definition
     * @return array<string, string> Array with 'interface' and 'implementation' file paths
     * @throws SchemaDefinitionException
     * @throws \RuntimeException If the output directory cannot be created or file cannot be written.
     */
    public function generate(array $schema): array
    {
        if (empty($schema['entity']['name'])) {
            throw new SchemaDefinitionException('Invalid schema: missing entity name.');
        }

        $entityName = $schema['entity']['name'];

        // Use the service to get the base output directory (e.g., src/Generated/Testy)
        $outputDir = $this->generatorOutputService->getEntityOutputDir($entityName);
        // $repositoryOutputDir = $outputDir . '/';
        $repositoryOutputDir = $outputDir;

        // // Ensure the specific repository output directory exists
        // if (!is_dir($repositoryOutputDir)) {
        //     if (!mkdir($repositoryOutputDir, 0777, true) && !is_dir($repositoryOutputDir)) {
        //         throw new \RuntimeException("Failed to create repository output directory: {$repositoryOutputDir}");
        //     }
        // }

        // Generate Interface
        $interfaceContent = $this->generateInterface($entityName, $schema);
        $interfaceFilePath = $repositoryOutputDir . $entityName . 'RepositoryInterface.php';

        $success = file_put_contents($interfaceFilePath, $interfaceContent);
        if ($success === false) {
            throw new \RuntimeException("Failed to write repository interface file: {$interfaceFilePath}");
        }
        $interfacePath = $interfaceFilePath; // Correctly assign the path

        // Generate Implementation
        $implementationContent = $this->generateImplementation($entityName, $schema);
        $implementationFilePath = $repositoryOutputDir . $entityName . 'Repository.php';

        $success = file_put_contents($implementationFilePath, $implementationContent);
        if ($success === false) {
            throw new \RuntimeException("Failed to write repository implementation file: {$implementationFilePath}");
        }
        $implementationPath = $implementationFilePath; // Correctly assign the path

        return [
            'interface' => $interfacePath,
            'implementation' => $implementationPath,
        ];
    }

    /**
     * Generate Repository Interface.
     *
     * @param string $entityName
     * @param array<string, mixed> $schema
     * @return string
     */
    protected function generateInterface(string $entityName, array $schema): string
    {
        $tableName = $schema['entity']['table'];

        $customMethods = $schema['repository']['custom_methods'] ?? [];
        $methodSignatures = $this->generateInterfaceMethodSignatures($entityName, $customMethods);

        // Get the generated timestamp from the service
        $generatedTimestamp = $this->generatorOutputService->getGeneratedFileTimestamp();

        $nameSpace = "App\Features\\{$entityName}";
        $php = <<<PHP
<?php

declare(strict_types=1);

namespace $nameSpace;

// use App\Entities\\{$entityName};
use Core\Repository\BaseRepositoryInterface;

/**
 * Generated File - Date: {$generatedTimestamp}
 * interface for {$entityName}.
 */
interface {$entityName}RepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find a {$entityName} by ID with full entity mapping.
     *
     * @param int \$id
     * @return {$entityName}|null
     */
    public function findById(int \$id): ?{$entityName};

    /**
     * Find a {$entityName} by ID, selecting only specified columns (raw data).
     *
     * @param int \$id
     * @param array<string> \$fields
     * @return array<string, mixed>|null
     */
    public function findByIdWithFields(int \$id, array \$fields): ?array;

    /**
     * Find {$entityName} records based on criteria with full entity mapping.
     *
     * @param array<string, mixed> \$criteria
     * @param array<string, string> \$orderBy
     * @param int|null \$limit
     * @param int|null \$offset
     * @return array<{$entityName}>
     */
    public function findBy(
        array \$criteria = [],
        array \$orderBy = [],
        ?int \$limit = null,
        ?int \$offset = null
    ): array;

    /**
     * Create a new {$entityName}.
     *
     * @param {$entityName} \${$tableName}
     * @return {$entityName} The created {$entityName} with ID
     */
    public function create({$entityName} \${$tableName}): {$entityName};

    /**
     * Update an existing {$entityName}.
     *
     * @param {$entityName} \${$tableName}
     * @return bool True if update was successful
     */
    public function update({$entityName} \${$tableName}): bool;

    /**
     * Update selected fields for a {$entityName} by its primary ID.
     *
     * @param int \$id The record ID.
     * @param array<string, mixed> \$fieldsToUpdate Associative array of fields to update.
     * @return bool True on success, false on failure.
     */
    public function updateFields(int \$id, array \$fieldsToUpdate): bool;

    /**
     * Delete a {$entityName} (hard delete).
     *
     * @param int \$id
     * @return bool True if deletion was successful
     */
    public function delete(int \$id): bool;

    /**
     * Count total {$entityName} records matching criteria.
     *
     * @param array<string, mixed> \$criteria Optional filtering criteria
     * @return int Total number of records matching criteria
     */
    public function countBy(array \$criteria = []): int;

    /**
     * Convert a {$entityName} entity to an array with selected fields.
     *
     * @param {$entityName} \${$tableName} The {$entityName} record to convert.
     * @param array<string> \$fields Optional list of specific fields to include.
     * @return array<string, mixed> Array representation of User record.
     */
    public function toArray({$entityName} \${$tableName}, array \$fields = []): array;
{$methodSignatures}
}

PHP;

        return $php;
    }

    /**
     * Generate method signatures for interface from custom methods.
     *
     * @param string $entityName
     * @param array<int, array<string, mixed>> $customMethods
     * @return string
     */
    protected function generateInterfaceMethodSignatures(string $entityName, array $customMethods): string
    {
        if (empty($customMethods)) {
            return '';
        }

        $signatures = [];
        foreach ($customMethods as $method) {
            $name = $method['name'];
            $description = $method['description'] ?? '';
            $params = $method['params'] ?? [];
            $return = $method['return'] ?? 'mixed';

            $paramStrings = [];
            $paramDocs = [];
            foreach ($params as $param) {
                $type = $param['type'];
                $paramName = $param['name'];
                $default = $param['default'] ?? null;

                $paramStr = "{$type} \${$paramName}";
                if ($default !== null) {
                    $paramStr .= " = {$default}";
                }
                $paramStrings[] = $paramStr;
                $paramDocs[] = "     * @param {$type} \${$paramName}";
            }

            $paramString = implode(', ', $paramStrings);
            $paramDocString = !empty($paramDocs) ? "\n" . implode("\n", $paramDocs) : '';

            $signatures[] = <<<PHP

    /**
     * {$description}
{$paramDocString}
     * @return {$return}
     */
    public function {$name}({$paramString}): {$return};
PHP;
        }

        return implode("\n", $signatures);
    }

    /**
     * Generate Repository Implementation.
     *
     * @param string $entityName
     * @param array<string, mixed> $schema
     * @return string
     */
    protected function generateImplementation(string $entityName, array $schema): string
    {
        $tableName = $schema['entity']['table'];
        $tableAlias = substr(strtolower($entityName), 0, 1);
        $repositoryExtends = $schema['repository']['extends'] ?? 'AbstractRepository';
        $implements = $schema['repository']['implements'] ?? ["{$entityName}RepositoryInterface"];
        $implementsList = implode(', ', $implements);
        $fields = $schema['fields'] ?? [];
        $joins = $schema['repository']['joins'] ?? [];
        $queryFindById2 = $schema['repository']['queries']['findById2'] ?? [];
        $queryFindById = $schema['repository']['queries']['findById'] ?? [];
        $queryFindBy   = $schema['repository']['queries']['findBy'] ?? [];
        $customMethods = $schema['repository']['custom_methods'] ?? [];

        // Get the generated timestamp from the service
        $generatedTimestamp = $this->generatorOutputService->getGeneratedFileTimestamp();

        // Generate property declarations
        $properties = $this->generateProperties($tableName, $tableAlias);

        // Generate constructor
        $constructor = $this->generateConstructor();

        // Generate findById with JOIN
        $findById = $this->generateFindById($queryFindById, $entityName, $tableName);

        // Generate findBy No Join
        // $findBy = $this->generateFindBy($queryFindBy, $entityName, $tableName, $tableAlias);
        $findBy = '';

        // Generate create method
        $create = $this->generateCreate($entityName, $tableName, $fields);
        // $create = '';

        // Generate update method
        $update = $this->generateUpdate($entityName, $tableName, $fields);
        // $update = '';

        // Generate mapToEntity
        $mapToEntity = $this->generateMapToEntity($entityName, $fields, $joins);
        // $mapToEntity = '';

        // Generate toArray
        $toArray = $this->generateToArray($entityName, $tableName, $fields);
        // $toArray = '';

        // Generate custom methods
        // $customMethodsCode = $this->generateCustomMethods($customMethods);
        $customMethodsCode = '';

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

// use App\Entities\\{$entityName};
{$useStatementsString}
use Core\Database\ConnectionInterface;
use Core\Repository\\{$repositoryExtends};
use Core\Repository\BaseRepositoryInterface;

/**
 * Generated File - Date: {$generatedTimestamp}
 * Repository implementation for {$entityName} entity.
 *
 * Handles all database operations for {$entityName} records including CRUD operations,
 * draft saving, and entity mapping with JOIN support for related user data.
 *
 * @implements {$entityName}RepositoryInterface
 * @implements BaseRepositoryInterface
 */
class {$entityName}Repository extends {$repositoryExtends} implements {$implementsList}
{
{$properties}
{$constructor}
{$findById}
{$findBy}
{$create}
{$update}
{$mapToEntity}
{$toArray}
}

PHP;
// {$customMethodsCode}
        return $php;
    }

    /**
     * Generate property declarations.
     *
     * @param string $tableName
     * @param string $tableAlias
     * @return string
     */
    protected function generateProperties(string $tableName, string $tableAlias): string
    {
        return <<<PHP
    // Notes-: this 3 are used by abstract class
    protected string \$tableName = '{$tableName}';
    protected string \$tableAlias = '{$tableAlias}';
    protected string \$primaryKey = 'id';
PHP;
    }

    /**
     * Generate constructor.
     *
     * @return string
     */
    protected function generateConstructor(): string
    {
        return <<<'PHP'

    /**
     * Initialize repository with database connection.
     *
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        parent::__construct($connection);
    }
PHP;
    }

    /**
     * Generate findById method with JOIN logic.
     *
     * @param string $entityName
     * @param string $tableAlias
     * @param array<string, array<string, mixed>> $joins
     * @return string
     */
    protected function generateFindById(string $methodName, string $entityName, string $tableName): string
    {
        // $joinClauses = $this->generateJoinClauses($tableAlias, $joins);
        // $selectColumns = $this->generateSelectColumns($tableAlias, $joins);
        $queryStatement = $this->generateQueryStatement($methodName);

        return <<<PHP

    /**
     * Find a {$entityName} by ID with full entity mapping.
     *
     * @param int \$id The {$entityName} ID
     * @return {$entityName}|null The {$entityName} entity or null if not found
     */
    public function findById(int \$id): ?{$entityName}
    {
        \$sql = "SELECT *
                FROM {$tableName}
                WHERE id = :id";

        \$stmt = \$this->connection->prepare(\$sql);
        \$stmt->bindValue(':id', \$id, \\PDO::PARAM_INT);
        \$stmt->execute();

        \$data = \$stmt->fetch(\\PDO::FETCH_ASSOC);

        if (!\$data) {
            return null;
        }

        return \$this->mapToEntity(\$data);
    }
PHP;
    }

    /**
     * Generate findBy method with JOIN logic.
     *
     * @param string $entityName
     * @param string $tableAlias
     * @param array<string, array<string, mixed>> $joins
     * @return string
     */
    protected function generateFindBy(
        string $methodName,
        string $entityName,
        string $tableName,
        string $tableAlias
    ): string {
        // $joinClauses = $this->generateJoinClauses($tableAlias, $joins);
        // $selectColumns = $this->generateSelectColumns($tableAlias, $joins);
        $queryStatement = $this->generateQueryStatement($methodName);

        return <<<PHP

    /**
     * Find {$entityName} records based on criteria with full entity mapping.
     *
     * @param array<string, mixed> \$criteria Filtering criteria (field => value pairs)
     * @param array<string, string> \$orderBy Sorting criteria (field => direction pairs)
     * @param int|null \$limit Maximum number of records to return
     * @param int|null \$offset Number of records to skip
     * @return array<{$entityName}> Array of {$entityName} entities matching criteria
     */
    public function findBy(
        array \$criteria = [],
        array \$orderBy = [],
        ?int \$limit = null,
        ?int \$offset = null
    ): array {
        \$sql = "SELECT t.*, u.username
                FROM {$tableName} t
                LEFT JOIN user u ON t.user_id = u.id";

        \$params = [];

        // Build WHERE clause
        if (!empty(\$criteria)) {
            \$whereClauses = [];
            foreach (\$criteria as \$field => \$value) {
                \$whereClauses[] = "{$tableAlias}.{\$field} = :{\$field}";
                \$params[":{\$field}"] = \$value;
            }
            \$sql .= ' WHERE ' . implode(' AND ', \$whereClauses);
        }

        // Build ORDER BY clause
        if (!empty(\$orderBy)) {
            \$orderClauses = [];
            foreach (\$orderBy as \$field => \$direction) {
                \$dir = strtoupper(\$direction) === 'DESC' ? 'DESC' : 'ASC';
                \$orderClauses[] = "{$tableAlias}.{\$field} {\$dir}";
            }
            \$sql .= ' ORDER BY ' . implode(', ', \$orderClauses);
        } else {
            \$sql .= ' ORDER BY {$tableAlias}.created_at DESC';
        }

        // Add LIMIT and OFFSET
        if (\$limit !== null) {
            \$sql .= ' LIMIT :limit';
            if (\$offset !== null) {
                \$sql .= ' OFFSET :offset';
            }
        }

        \$stmt = \$this->connection->prepare(\$sql);

        // Bind parameters
        foreach (\$params as \$param => \$value) {
            \$stmt->bindValue(\$param, \$value, \$this->getPdoType(\$value));
        }

        if (\$limit !== null) {
            \$stmt->bindValue(':limit', \$limit, \\PDO::PARAM_INT);
        }
        if (\$offset !== null) {
            \$stmt->bindValue(':offset', \$offset, \\PDO::PARAM_INT);
        }

        \$stmt->execute();

        \$results = [];
        while (\$row = \$stmt->fetch(\\PDO::FETCH_ASSOC)) {
            \$results[] = \$this->mapToEntity(\$row);
        }

        return \$results;
    }
PHP;
    }

    /**
     * General.....configuration.
     *
     *
     */
    protected function generateQueryStatement(string $methodName): string
    {
        // if (empty($joins)) {
        //     return '';
        // }

        $query = '';
        $query = $methodName;


        return $query;
        //return implode("\n", $query);
    }

    /**
     * Generate JOIN clauses from schema configuration.
     *
     * @param string $tableAlias
     * @param array<string, array<string, mixed>> $joins
     * @return string
     */
    protected function generateJoinClauses(string $tableAlias, array $joins): string
    {
        if (empty($joins)) {
            return '';
        }

        $clauses = [];
        foreach ($joins as $table => $config) {
            $type = strtoupper($config['type'] ?? 'LEFT');
            $alias = $config['alias'] ?? substr($table, 0, 1);
            $on = $config['on'];
            $clauses[] = "                {$type} JOIN {$table} {$alias} ON {$on}";
        }

        return implode("\n", $clauses);
    }

    /**
     * Generate SELECT columns including JOIN columns.
     *
     * @param string $tableAlias
     * @param array<string, array<string, mixed>> $joins
     * @return string
     */
    protected function generateSelectColumns(string $tableAlias, array $joins): string
    {
        $columns = ["{$tableAlias}.*"];

        foreach ($joins as $table => $config) {
            $alias = $config['alias'] ?? substr($table, 0, 1);
            $select = $config['select'] ?? [];
            foreach ($select as $column) {
                $columns[] = "{$alias}.{$column}";
            }
        }

        return implode(', ', $columns);
    }

    /**
     * Generate create method.
     *
     * @param string $entityName
     * @param string $tableName
     * @param array<string, array<string, mixed>> $fields
     * @return string
     */
    protected function generateCreate(string $entityName, string $tableName, array $fields): string
    {
        $variableName = lcfirst($entityName);
        $dataMapping = $this->generateDataMappingForCreate($entityName, $tableName, $fields);

        return <<<PHP

    /**
     * Create a new {$entityName} record.
     *
     * @param {$entityName} \${$tableName} The {$entityName} record to create
     * @return {$entityName} The created {$entityName} record with ID populated
     */
    public function create({$entityName} \${$tableName}): {$entityName}
    {
        \$data = [
{$dataMapping}
        ];

        \$columns = array_keys(\$data);
        \$placeholders = array_map(fn(\$col) => ":{\$col}", \$columns);

        \$sql = "INSERT INTO {$tableName} ("
             . implode(', ', \$columns)
             . ") VALUES ("
             . implode(', ', \$placeholders)
             . ")";

        \$stmt = \$this->connection->prepare(\$sql);

        foreach (\$data as \$col => \$value) {
            \$stmt->bindValue(":{\$col}", \$value, \$this->getPdoType(\$value));
            // if (\$value === 'NOW()') {
            //     \$stmt->bindValue(":{\$col}", null);
            // } else {
            //     \$stmt->bindValue(":{\$col}", \$value, \$this->getPdoType(\$value));
            // }
        }

        \$stmt->execute();

        \$id = (int) \$this->connection->lastInsertId();
        \${$tableName}->setId(\$id);

        return \$this->findById(\$id);
    }
PHP;
    }

    /**
     * Generate data mapping for create method.
     *
     * @param string $entityName
     * @param array<string, array<string, mixed>> $fields
     * @return string
     */
    protected function generateDataMappingForCreate(string $entityName, string $tableName, array $fields): string
    {
        $mappings = [];

        foreach ($fields as $fieldName => $config) {
            // Skip auto-increment primary key
            if (isset($config['primary']) && $config['primary']) {
                continue;
            }

            // // Skip timestamps (handled separately)
            // if (in_array($fieldName, ['created_at', 'updated_at'])) {
            //     continue;
            // }

            $getterName = $this->generateGetterName($entityName, $fieldName, $config);
            $value = "\${$tableName}->{$getterName}()";


            $dbType = $config['db_type'] ?? '';

            // Handle boolean conversion
            if ($dbType === 'boolean') {
                $value = "{$value} ? 1 : 0";
            } elseif ($dbType === 'array') {
                $value = "json_encode({$value})";
            } elseif ($dbType === 'enum') {
                $value = "{$value}->value";
            }


            $mappings[] = "            '{$fieldName}' => {$value},";
        }

        // Add timestamps
        //$mappings[] = "            'created_at' => 'NOW()',";
        //$mappings[] = "            'updated_at' => 'NOW()',";

        return implode("\n", $mappings);
    }

    /**
     * Generate update method.
     *
     * @param string $entityName
     * @param array<string, array<string, mixed>> $fields
     * @return string
     */
    protected function generateUpdate(string $entityName, string $tableName, array $fields): string
    {
        $dataMapping = $this->generateDataMappingForUpdate($entityName, $tableName, $fields);

        return <<<PHP

    /**
     * Update an existing {$entityName} record.
     *
     * @param {$entityName} \${$tableName} The {$entityName} record to update
     * @return bool True if update was successful
     */
    public function update({$entityName} \${$tableName}): bool
    {
        \$fieldsToUpdate = [
{$dataMapping}
        ];

        return \$this->updateFields(\${$tableName}->getId(), \$fieldsToUpdate);
    }
PHP;
    }

    /**
     * Generate data mapping for update method.
     *
     * @param string $entityName
     * @param array<string, array<string, mixed>> $fields
     * @return string
     */
    protected function generateDataMappingForUpdate(string $entityName, string $tableName, array $fields): string
    {
        $mappings = [];

        foreach ($fields as $fieldName => $config) {
            // Skip auto-increment primary key
            if (isset($config['primary']) && $config['primary']) {
                continue;
            }

            // Skip timestamps
            // if (in_array($fieldName, ['created_at', 'updated_at'])) {
            //     continue;
            // }

            $getterName = $this->generateGetterName($entityName, $fieldName, $config);
            $value = "\${$tableName}->{$getterName}()";

            $dbType = $config['db_type'] ?? '';

            // Handle boolean conversion
            if ($dbType === 'boolean') {
                $value = "{$value} ? 1 : 0";
            } elseif ($dbType === 'array') {
                $value = "json_encode({$value})";
            } elseif ($dbType === 'enum') {
                $value = "{$value}->value";
            }

            $mappings[] = "            '{$fieldName}' => {$value},";
        }

        return implode("\n", $mappings);
    }

    /**
     * Generate mapToEntity private method.
     *
     * @param string $entityName
     * @param array<string, array<string, mixed>> $fields
     * @param array<string, array<string, mixed>> $joins
     * @return string
     */
    protected function generateMapToEntity(string $entityName, array $fields, array $joins): string
    {
        $variableName = lcfirst($entityName);
        $setters = $this->generateEntitySetters($entityName, $fields, $joins);

        return <<<PHP

    /**
     * Map database row to {$entityName} entity.
     *
     * Hydrates a {$entityName} entity from database result set including
     * related user data from JOIN.
     *
     * @param array<string, mixed> \$data Database row data
     * @return {$entityName} Fully hydrated {$entityName} entity
     */
    private function mapToEntity(array \$data): {$entityName}
    {
        \${$variableName} = new {$entityName}();

{$setters}

        return \${$variableName};
    }
PHP;
    }

    /**
     * Generate entity setters for mapToEntity.
     *
     * @param string $entityName
     * @param array<string, array<string, mixed>> $fields
     * @param array<string, array<string, mixed>> $joins
     * @return string
     */
    protected function generateEntitySetters(string $entityName, array $fields, array $joins): string
    {
        $variableName = lcfirst($entityName);
        $setters = [];

        foreach ($fields as $fieldName => $config) {
            $setterName = $this->generateSetterName($entityName, $fieldName, $config);
            $dbType = $config['db_type'] ?? 'string';

            // Generate appropriate type casting
            $value = "\$data['{$fieldName}']";


            // This pattern is used for fields that are nullable in the database and
            // --- need to be type-casted to a specific PHP type (like float or int) when they are not null.
            // --- ex: $value = "\$data['{$fieldName}'] !== null ? (float) \$data['{$fieldName}'] : null";
            if ($dbType === 'bigIncrements' || $dbType === 'bigInteger' || $dbType === 'integer') {
                if ($config['nullable'] ?? false) {
                    $value = "\$data['{$fieldName}'] !== null ? (int) \$data['{$fieldName}'] : null";
                } else {
                    $value = "(int) \$data['{$fieldName}']";
                }
            } elseif ($dbType === 'decimal') {
                if ($config['nullable'] ?? false) {
                    $value = "\$data['{$fieldName}'] !== null ? (float) \$data['{$fieldName}'] : null";
                } else {
                    $value = "(float) \$data['{$fieldName}']";
                }
            } elseif ($dbType === 'boolean') {
                $value = "(bool) \$data['{$fieldName}']";
            } elseif ($dbType === 'array') {
                $value =
                   "is_array(\$decodedRoles = json_decode(\$data['{$fieldName}'] ?? '[]', true)) ? \$decodedRoles : []";
            } elseif ($dbType === 'enum') {
                $capsFieldName = $this->toCapitalizationCase($fieldName);
                $enumShortName = "{$entityName}$capsFieldName";
                $value = "$enumShortName::from(\$data['{$fieldName}'])";
            } elseif ($config['nullable'] ?? false) {
                $value = "\$data['{$fieldName}']";
            }

            $setters[] = "        \${$variableName}->{$setterName}({$value});";
        }

//         // Add setters for JOIN columns
//         foreach ($joins as $table => $config) {
//             $select = $config['select'] ?? [];
//             foreach ($select as $column) {
//                 $setterName = 'set' . str_replace('_', '', ucwords($column, '_'));
//                 $setters[] = <<<PHP

//         if (isset(\$data['{$column}'])) {
//             \${$variableName}->{$setterName}(\$data['{$column}']);
//         }
// PHP;
//             }
//         }

        return implode("\n", $setters);
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
     * Generate toArray method.
     *
     * @param string $entityName
     * @param array<string, array<string, mixed>> $fields
     * @return string
     */
    protected function generateToArray(string $entityName, string $tableName, array $fields): string
    {
        $variableName = lcfirst($entityName);
        $arrayMapping = $this->generateArrayMapping($entityName, $tableName, $fields);

        return <<<PHP

    /**
     * Convert a {$entityName} record to an array with selected fields.
     *
     * @param {$entityName} \${$tableName} The {$entityName} record to convert
     * @param array<string> \$fields Optional list of specific fields to include
     * @return array<string, mixed> Array representation of {$entityName} record
     */
    public function toArray({$entityName} \${$tableName}, array \$fields = []): array
    {
        \$allFields = [
{$arrayMapping}
        ];

        if (!empty(\$fields)) {
            return array_intersect_key(\$allFields, array_flip(\$fields));
        }

        return \$allFields;
    }
PHP;
    }

    /**
     * Generate array mapping for toArray method.
     *
     * @param string $entityName
     * @param array<string, array<string, mixed>> $fields
     * @return string
     */
    protected function generateArrayMapping(string $entityName, string $tableName, array $fields): string
    {
        $mappings = [];

        foreach ($fields as $fieldName => $config) {
            $getterName = $this->generateGetterName($entityName, $fieldName, $config);

            // Special handling for status field with CHECK constraint
            if ($fieldName === 'status' && isset($config['check'])) {
                $mappings[] = <<<PHP
            '{$fieldName}' => match (\${$tableName}->{$getterName}()) {
                'A' => '☑️ Active',
                'P' => '⏳ Pending',
                'I' => '❌ Inactive',
                default => '❓ Unknown'
            },
PHP;
            } else {
                $mappings[] = "            '{$fieldName}' => \${$tableName}->{$getterName}(),";
            }
        }

        return implode("\n", $mappings);
    }

    /**
     * Generate custom methods from schema configuration.
     *
     * @param array<int, array<string, mixed>> $customMethods
     * @return string
     */
    protected function generateCustomMethods(array $customMethods): string
    {
        if (empty($customMethods)) {
            return '';
        }

        $methods = [];
        foreach ($customMethods as $method) {
            $methods[] = $this->generateCustomMethod($method);
        }

        return implode("\n", $methods);
    }

    /**
     * Generate a single custom method.
     *
     * @param array<string, mixed> $method
     * @return string
     */
    protected function generateCustomMethod(array $method): string
    {
        $name = $method['name'];
        $description = $method['description'] ?? '';
        $params = $method['params'] ?? [];
        $return = $method['return'] ?? 'mixed';

        $paramStrings = [];
        $paramDocs = [];
        foreach ($params as $param) {
            $type = $param['type'];
            $paramName = $param['name'];
            $default = $param['default'] ?? null;

            $paramStr = "{$type} \${$paramName}";
            if ($default !== null) {
                $paramStr .= " = {$default}";
            }
            $paramStrings[] = $paramStr;
            $paramDocs[] = "     * @param {$type} \${$paramName}";
        }

        $paramString = implode(', ', $paramStrings);
        $paramDocString = !empty($paramDocs) ? "\n" . implode("\n", $paramDocs) : '';

        // Generate method body
        if (isset($method['body'])) {
            $body = $method['body'];
        } elseif (isset($method['implementation'])) {
            $body = $this->generateDelegateMethodBody($method);
        } else {
            $body = "        throw new \\LogicException('Not implemented');";
        }

        return <<<PHP

    /**
     * {$description}
{$paramDocString}
     * @return {$return}
     */
    public function {$name}({$paramString}): {$return}
    {
{$body}
    }
PHP;
    }

    /**
     * Generate method body that delegates to base class method.
     *
     * @param array<string, mixed> $method
     * @return string
     */
    protected function generateDelegateMethodBody(array $method): string
    {
        $implementation = $method['implementation'];
        $criteria = $method['criteria'] ?? [];
        $params = $method['params'] ?? [];

        $criteriaArray = [];
        foreach ($criteria as $key => $value) {
            $criteriaArray[] = "'{$key}' => {$value}";
        }
        $criteriaString = !empty($criteriaArray) ? '[' . implode(', ', $criteriaArray) . ']' : '[]';

        $paramNames = [];
        $skipFirst = !empty($criteria); // If we have criteria, skip first param (it's in criteria)
        foreach ($params as $index => $param) {
            if ($skipFirst && $index === 0) {
                continue;
            }
            $paramNames[] = '$' . $param['name'];
        }

        $paramString = !empty($paramNames) ? ', ' . implode(', ', $paramNames) : '';

        return "        return \$this->{$implementation}({$criteriaString}{$paramString});";
    }

    /**
     * Generate getter method name for a field.
     *
     * @param string $entityName
     * @param string $fieldName
     * @param array<string, mixed> $config
     * @return string
     */
    protected function generateGetterName(string $entityName, string $fieldName, array $config): string
    {
        // Special handling for foreign keys and specific fields
        // if ($fieldName === 'id') {
        //     return "get{$entityName}Id";
        // }

        // if ($fieldName === 'store_id') {
        //     return "get{$entityName}StoreId";
        // }

        // if ($fieldName === 'user_id') {
        //     return "get{$entityName}UserId";
        // }

        // if ($fieldName === 'status') {
        //     return "get{$entityName}Status";
        // }

        // Boolean fields
        if (($config['db_type'] ?? '') === 'boolean') {
            return 'get' . str_replace('_', '', ucwords($fieldName, '_'));
        }

        // Default getter
        return 'get' . str_replace('_', '', ucwords($fieldName, '_'));
    }

    /**
     * Generate setter method name for a field.
     *
     * @param string $entityName
     * @param string $fieldName
     * @param array<string, mixed> $config
     * @return string
     */
    protected function generateSetterName(string $entityName, string $fieldName, array $config): string
    {
        // Special handling for foreign keys and specific fields
        // if ($fieldName === 'id') {
        //     return "set{$entityName}Id";
        // }

        // if ($fieldName === 'store_id') {
        //     return "set{$entityName}StoreId";
        // }

        // if ($fieldName === 'user_id') {
        //     return "set{$entityName}UserId";
        // }

        // if ($fieldName === 'status') {
        //     return "set{$entityName}Status";
        // }

        // Default setter
        return 'set' . str_replace('_', '', ucwords($fieldName, '_'));
    }
}
