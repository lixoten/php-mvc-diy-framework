<?php

declare(strict_types=1);

namespace Core\Repository;

use Core\Database\ConnectionInterface;

abstract class AbstractRepository implements BaseRepositoryInterface
{
    protected ConnectionInterface $connection;
    protected string $tableName;
    protected string $tableAlias = 't';
    protected string $primaryKey = 'id';

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }



    public function findFoo(): string
    {
        return "foo";
    }


    /** {@inheritdoc} */
    public function findByCriteriaWithFields(
        array $criteria,
        array $fields,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        if (empty($fields)) {
            $fields = ['*'];
        }

        $this->validateFieldNames($fields);
        $columns = implode(', ', $fields);

        $sql = "SELECT {$columns} FROM {$this->tableName} {$this->tableAlias}";

        // Build WHERE clause
        if (!empty($criteria)) {
            $whereClauses = [];
            foreach (array_keys($criteria) as $field) {
                $whereClauses[] = "{$this->tableAlias}.{$field} = :{$field}";
            }
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        // Build ORDER BY clause
        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $field => $direction) {
                $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $cleanField = $this->prefixFieldWithAlias($field);
                $this->validateFieldNames([$cleanField]);
                $orderClauses[] = "{$cleanField} {$dir}";
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
        } else {
            $sql .= " ORDER BY {$this->tableAlias}.created_at DESC";
        }

        // Add LIMIT and OFFSET
        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
            if ($offset !== null) {
                $sql .= ' OFFSET :offset';
            }
        }

        $stmt = $this->connection->prepare($sql);

        // Bind criteria parameters
        foreach ($criteria as $field => $value) {
            $stmt->bindValue(":{$field}", $value, $this->getPdoType($value));
        }

        // Bind limit and offset
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        }
        if ($offset !== null) {
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /// edit ///////////////////////////////////////////////////////

    /** {@inheritdoc} */
    public function findByIdWithFields(int $id, array $fields): ?array
    {
        if (empty($fields)) {
            $fields = ['*'];
        }

        $this->validateFieldNames($fields);
        $columns = implode(', ', $fields);

        $sql = "SELECT {$columns} FROM {$this->tableName} WHERE {$this->primaryKey} = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ?: null;
    }


    /** {@inheritdoc} */
    public function updateFields(int $id, array $fieldsToUpdate): bool
    {
        if (empty($fieldsToUpdate)) {
            return false;
        }

        $setClauses = [];
        $params = [':id' => $id];

        foreach ($fieldsToUpdate as $field => $value) {
            // JSON-encode array values for database storage
            if (is_array($value)) {
                $value = json_encode($value, JSON_THROW_ON_ERROR);
            }

            $setClauses[] = "{$field} = :{$field}";
            $params[":{$field}"] = $value;
        }

        $setClauses[] = "updated_at = NOW()";
        $sql = "UPDATE {$this->tableName} SET " . implode(', ', $setClauses)
             . " WHERE {$this->primaryKey} = :id";

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute($params);
    }



    /** {@inheritdoc} */
    public function countBy(array $criteria = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} {$this->tableAlias}";
        $params = [];

        if (!empty($criteria)) {
            $whereClauses = [];
            foreach ($criteria as $field => $value) {
                $placeholder = ':' . str_replace('.', '_', $field);
                $whereClauses[] = "{$this->tableAlias}.{$field} = {$placeholder}";
                $params[$placeholder] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        $stmt = $this->connection->prepare($sql);

        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value, $this->getPdoType($value));
        }

        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int) ($result['count'] ?? 0);
    }




    /** {@inheritdoc} */
    public function insertFields(array $data): int
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Data array cannot be empty.');
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = "INSERT INTO {$this->tableName} ("
             . implode(', ', $columns)
             . ") VALUES ("
             . implode(', ', $placeholders)
             . ")";

        $stmt = $this->connection->prepare($sql);

        foreach ($data as $col => $value) {
            $stmt->bindValue(":{$col}", $value, $this->getPdoType($value));
        }

        $stmt->execute();

        return (int) $this->connection->lastInsertId();
    }





    /** {@inheritdoc} */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->tableName} WHERE {$this->primaryKey} = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }







#    /**
#     * Find records by criteria with full entity mapping.
#     * Child repositories must override this method to provide entity-specific mapping.
#     *
#     * @param array<string, mixed> $criteria Filtering criteria (field => value pairs)
#     * @param array<string, string> $orderBy Sorting criteria (field => direction pairs)
#     * @param int|null $limit Maximum number of records to return
#     * @param int|null $offset Number of records to skip
#     * @return array<object> Array of entity objects
#     */
#    public function findBy(
#        array $criteria = [],
#        array $orderBy = [],
#        ?int $limit = null,
#        ?int $offset = null
#    ): array {
#        $sql = "SELECT {$this->tableAlias}.* FROM {$this->tableName} {$this->tableAlias}";
#        $params = [];
#
#        // Build WHERE clause
#        if (!empty($criteria)) {
#            $whereClauses = [];
#            foreach ($criteria as $field => $value) {
#                $whereClauses[] = "{$this->tableAlias}.{$field} = :{$field}";
#                $params[":{$field}"] = $value;
#            }
#            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
#        }
#
#        // Build ORDER BY clause
#        if (!empty($orderBy)) {
#            $orderClauses = [];
#            foreach ($orderBy as $field => $direction) {
#                $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
#                $orderClauses[] = "{$this->tableAlias}.{$field} {$dir}";
#            }
#            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
#        } else {
#            $sql .= " ORDER BY {$this->tableAlias}.created_at DESC";
#        }
#
#        // Add LIMIT and OFFSET
#        if ($limit !== null) {
#            $sql .= ' LIMIT :limit';
#            if ($offset !== null) {
#                $sql .= ' OFFSET :offset';
#            }
#        }
#
#        $stmt = $this->connection->prepare($sql);
#
#        // Bind parameters
#        foreach ($params as $param => $value) {
#            $stmt->bindValue($param, $value, $this->getPdoType($value));
#        }
#
#        if ($limit !== null) {
#            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
#        }
#        if ($offset !== null) {
#            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
#        }
#
#        $stmt->execute();
#
#        $results = [];
#        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
#            $results[] = $this->mapToEntity($row);
#        }
#
#        return $results;
#    }
#
#    /**
#     * Map database row to entity object.
#     * Child repositories MUST implement this method.
#     *
#     * @param array<string, mixed> $data Database row data
#     * @return object Hydrated entity object
#     */
#    abstract protected function mapToEntity(array $data): object;




#
#    /**
#     * Count total users
#     *
#     * @param array $criteria Optional filtering criteria
#     * @return int Total number of users matching criteria
#     */
#    public function countAllXxx(array $criteria = []): int
#    {
#        $sql = "SELECT COUNT(*) as count FROM user";
#        $params = [];
#
#        // Add WHERE clauses for criteria
#        if (!empty($criteria)) {
#            $whereClauses = [];
#            foreach ($criteria as $field => $value) {
#                $whereClauses[] = "$field = :$field";
#                $params[":$field"] = $value;
#            }
#            $sql .= " WHERE " . implode(' AND ', $whereClauses);
#        }
#
#        $stmt = $this->connection->prepare($sql);
#
#        // Bind parameters
#        foreach ($params as $param => $value) {
#            $stmt->bindValue($param, $value);
#        }
#
#        $stmt->execute();
#        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
#
#        return (int) $result['count'];
#    }
#



    /**
     * Validate field names to prevent SQL injection.
     *
     * @param array<string> $fields
     * @throws \InvalidArgumentException
     */
    protected function validateFieldNames(array $fields): void
    {
        foreach ($fields as $field) {
            if (!preg_match('/^[A-Za-z0-9_\\.\\*]+$/', $field)) {
                throw new \InvalidArgumentException("Invalid field name: {$field}");
            }
        }
    }

    /**
     * Prefix field with table alias if not already prefixed.
     *
     * @param string $field
     * @return string
     */
    protected function prefixFieldWithAlias(string $field): string
    {
        return strpos($field, '.') === false
            ? "{$this->tableAlias}.{$field}"
            : $field;
    }

    /**
     * Get PDO parameter type for a value.
     *
     * @param mixed $value
     * @return int
     */
    protected function getPdoType(mixed $value): int
    {
        return match (true) {
            is_int($value) => \PDO::PARAM_INT,
            is_bool($value) => \PDO::PARAM_BOOL,
            is_null($value) => \PDO::PARAM_NULL,
            default => \PDO::PARAM_STR,
        };
    }
}
