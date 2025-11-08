<?php

declare(strict_types=1);

// namespace App\Repository;
namespace App\Features\Store;

// use App\Entities\Store;
// use App\Features\Store;
use Core\Database\ConnectionInterface;
use Core\Repository\AbstractMultiTenantRepository;
use Core\Repository\BaseRepositoryInterface;

/**
 * Generated File - Date: 20251102_134902zz
 * Repository implementation for Store entity.
 *
 * Handles all database operations for Store records including CRUD operations,
 * draft saving, and entity mapping with JOIN support for related user data.
 *
 * @implements StoreRepositoryInterface
 * @implements BaseRepositoryInterface
 */
class StoreRepository extends AbstractMultiTenantRepository implements StoreRepositoryInterface, BaseRepositoryInterface
{
    // Notes-: this 3 are used by abstract class
    protected string $tableName = 'store';
    protected string $tableAlias = 's';
    protected string $primaryKey = 'id';

    /**
     * Initialize repository with database connection.
     *
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        parent::__construct($connection);
    }

    /**
     * Find a Store by ID with full entity mapping.
     *
     * @param int $id The Store ID
     * @return Store|null The Store entity or null if not found
     */
    public function findById(int $id): ?Store
    {
        $sql = "SELECT s.*, u.username
                FROM store s
                LEFT JOIN user u ON s.user_id = u.id
                WHERE s.id = :id";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    /**
     * Find Store records based on criteria with full entity mapping.
     *
     * @param array<string, mixed> $criteria Filtering criteria (field => value pairs)
     * @param array<string, string> $orderBy Sorting criteria (field => direction pairs)
     * @param int|null $limit Maximum number of records to return
     * @param int|null $offset Number of records to skip
     * @return array<Store> Array of Store entities matching criteria
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $sql = "SELECT s.*, u.username
                FROM store s
                LEFT JOIN user u ON s.user_id = u.id";

        $params = [];

        // Build WHERE clause
        if (!empty($criteria)) {
            $whereClauses = [];
            foreach ($criteria as $field => $value) {
                $whereClauses[] = "s.{$field} = :{$field}";
                $params[":{$field}"] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        // Build ORDER BY clause
        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $field => $direction) {
                $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $orderClauses[] = "s.{$field} {$dir}";
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
        } else {
            $sql .= ' ORDER BY s.created_at DESC';
        }

        // Add LIMIT and OFFSET
        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
            if ($offset !== null) {
                $sql .= ' OFFSET :offset';
            }
        }

        $stmt = $this->connection->prepare($sql);

        // Bind parameters
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value, $this->getPdoType($value));
        }

        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        }
        if ($offset !== null) {
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        }

        $stmt->execute();

        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = $this->mapToEntity($row);
        }

        return $results;
    }

    /**
     * Create a new Store record.
     *
     * @param Store $store The Store record to create
     * @return Store The created Store record with ID populated
     */
    public function create(Store $store): Store
    {
        $data = [
            'user_id' => $store->getUserId(),
            'status' => $store->getStatus(),
            'slug' => $store->getSlug(),
            'name' => $store->getName(),
            'description' => $store->getDescription(),
            'theme' => $store->getTheme(),
            'created_at' => 'NOW()',
            'updated_at' => 'NOW()',
        ];

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = "INSERT INTO store ("
             . implode(', ', $columns)
             . ") VALUES ("
             . implode(', ', $placeholders)
             . ")";

        $stmt = $this->connection->prepare($sql);

        foreach ($data as $col => $value) {
            if ($value === 'NOW()') {
                $stmt->bindValue(":{$col}", null);
            } else {
                $stmt->bindValue(":{$col}", $value, $this->getPdoType($value));
            }
        }

        $stmt->execute();

        $id = (int) $this->connection->lastInsertId();
        $store->setId($id);

        return $this->findById($id);
    }

    /**
     * Update an existing Store record.
     *
     * @param Store $store The Store record to update
     * @return bool True if update was successful
     */
    public function update(Store $store): bool
    {
        $fieldsToUpdate = [
            'user_id' => $store->getUserId(),
            'status' => $store->getStatus(),
            'slug' => $store->getSlug(),
            'name' => $store->getName(),
            'description' => $store->getDescription(),
            'theme' => $store->getTheme(),
        ];

        return $this->updateFields($store->getId(), $fieldsToUpdate);
    }

    /**
     * Map database row to Store entity.
     *
     * Hydrates a Store entity from database result set including
     * related user data from JOIN.
     *
     * @param array<string, mixed> $data Database row data
     * @return Store Fully hydrated Store entity
     */
    private function mapToEntity(array $data): Store
    {
        $store = new Store();

        $store->setId((int) $data['id']);
        $store->setUserId($data['user_id']);
        $store->setStatus($data['status']);
        $store->setSlug($data['slug']);
        $store->setName($data['name']);
        $store->setDescription($data['description']);
        $store->setTheme($data['theme']);

        return $store;
    }

    /**
     * Convert a Store record to an array with selected fields.
     *
     * @param Store $store The Store record to convert
     * @param array<string> $fields Optional list of specific fields to include
     * @return array<string, mixed> Array representation of Store record
     */
    public function toArray(Store $store, array $fields = []): array
    {
        $allFields = [
            'id' => $store->getId(),
            'user_id' => $store->getUserId(),
            'status' => match ($store->getStatus()) {
                'A' => '☑️ Active',
                'P' => '⏳ Pending',
                'I' => '❌ Inactive',
                default => '❓ Unknown'
            },
            'slug' => $store->getSlug(),
            'name' => $store->getName(),
            'description' => $store->getDescription(),
            'theme' => $store->getTheme(),
        ];

        if (!empty($fields)) {
            return array_intersect_key($allFields, array_flip($fields));
        }

        return $allFields;
    }
}
