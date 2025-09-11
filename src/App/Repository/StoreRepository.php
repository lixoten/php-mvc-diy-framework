<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entities\Store;
use Core\Database\ConnectionInterface;

class StoreRepository implements StoreRepositoryInterface
{
    private ConnectionInterface $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Find a store by ID
     */
    public function findById(int $storeId): ?Store
    {
        $sql = "SELECT s.*, u.username FROM stores s
                LEFT JOIN users u ON s.store_user_id = u.user_id
                WHERE s.store_id = :store_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':store_id', $storeId);
        $stmt->execute();

        $storeData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$storeData) {
            return null;
        }

        return $this->mapToEntity($storeData);
    }

    /**
     * Find a store by slug
     */
    public function findBySlug(string $slug): ?Store
    {
        $sql = "SELECT s.*, u.username FROM stores s
                LEFT JOIN users u ON s.store_user_id = u.user_id
                WHERE s.slug = :slug";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        $stmt->execute();

        $storeData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$storeData) {
            return null;
        }

        return $this->mapToEntity($storeData);
    }

    /**
     * Find a store by user ID
     */
    public function findByUserId(int $userId): ?Store
    {
        $sql = "SELECT s.*, u.username FROM stores s
                LEFT JOIN users u ON s.store_user_id = u.user_id
                WHERE s.store_user_id = :user_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();

        $storeData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$storeData) {
            return null;
        }

        return $this->mapToEntity($storeData);
    }

    /**
     * Find all stores
     *
     * @param array $criteria Optional filtering criteria
     * @param array $orderBy Optional sorting criteria
     * @param int|null $limit Maximum number of results
     * @param int|null $offset Result offset for pagination
     * @return Store[] Array of Store entities
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $sql = "SELECT s.*, u.username FROM stores s
                LEFT JOIN users u ON s.store_user_id = u.user_id";
        $params = [];

        // Add WHERE clauses for criteria
        if (!empty($criteria)) {
            $whereClauses = [];
            foreach ($criteria as $field => $value) {
                $whereClauses[] = "s.$field = :$field";
                $params[":$field"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        // Add ORDER BY clause
        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $field => $direction) {
                $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $orderClauses[] = "s.$field $direction";
            }
            $sql .= " ORDER BY " . implode(', ', $orderClauses);
        } else {
            // Default ordering by name
            $sql .= " ORDER BY s.name ASC";
        }

        // Add LIMIT and OFFSET
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            $params[':limit'] = $limit;

            if ($offset !== null) {
                $sql .= " OFFSET :offset";
                $params[':offset'] = $offset;
            }
        }

        $stmt = $this->connection->prepare($sql);

        // Bind parameters
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        $stmt->execute();

        $stores = [];
        while ($storeData = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $stores[] = $this->mapToEntity($storeData);
        }

        return $stores;
    }

    /**
     * Find stores by status
     */
    public function findByStatus(
        string $storeStatus,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        return $this->findBy(
            ['store_status' => $storeStatus],
            $orderBy,
            $limit,
            $offset
        );
    }

    /**
     * Create a new store
     */
    public function create(Store $store): Store
    {
        $sql = "INSERT INTO stores (
                store_user_id,
                store_status,
                slug,
                name,
                description,
                theme,
                created_at,
                updated_at
            ) VALUES (
                :store_user_id,
                :store_status,
                :slug,
                :name,
                :description,
                :theme,
                NOW(),
                NOW()
            )";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':store_user_id', $store->getUserId());
        $stmt->bindValue(':store_status', $store->getStatus());
        $stmt->bindValue(':slug', $store->getSlug());
        $stmt->bindValue(':name', $store->getName());
        $stmt->bindValue(':description', $store->getDescription());
        $stmt->bindValue(':theme', $store->getTheme());

        $stmt->execute();

        // Get the ID of the newly created store
        $storeId = (int) $this->connection->lastInsertId();
        $store->setStoreId($storeId);

        // Fetch fresh from database to get created_at and updated_at
        return $this->findById($storeId);
    }

    /**
     * Update an existing store
     */
    public function update(Store $store): bool
    {
        $sql = "UPDATE stores SET
                store_user_id = :store_user_id,
                store_status = :store_status,
                slug = :slug,
                name = :name,
                description = :description,
                theme = :theme,
                updated_at = NOW()
            WHERE store_id = :store_id";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':store_id', $store->getStoreId());
        $stmt->bindValue(':store_user_id', $store->getUserId());
        $stmt->bindValue(':store_status', $store->getStatus());
        $stmt->bindValue(':slug', $store->getSlug());
        $stmt->bindValue(':name', $store->getName());
        $stmt->bindValue(':description', $store->getDescription());
        $stmt->bindValue(':theme', $store->getTheme());

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Delete a store
     */
    public function delete(int $storeId): bool
    {
        $sql = "DELETE FROM stores WHERE store_id = :store_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':store_id', $storeId);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Count total stores
     */
    public function countBy(array $criteria = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM stores";
        $params = [];

        // Add WHERE clauses for criteria
        if (!empty($criteria)) {
            $whereClauses = [];
            foreach ($criteria as $field => $value) {
                $whereClauses[] = "$field = :$field";
                $params[":$field"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $stmt = $this->connection->prepare($sql);

        // Bind parameters
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int) $result['count'];
    }

    /**
     * Check if slug exists
     */
    public function slugExists(string $slug, ?int $excludeStoreId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM stores WHERE slug = :slug";
        $params = [':slug' => $slug];

        if ($excludeStoreId !== null) {
            $sql .= " AND store_id != :exclude_id";
            $params[':exclude_id'] = $excludeStoreId;
        }

        $stmt = $this->connection->prepare($sql);

        // Bind parameters
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int) $result['count'] > 0;
    }

    /**
     * Get primary/default store (useful for single-store mode)
     */
    public function getPrimaryStore(): ?Store
    {
        // Strategy: Get the first active store
        $sql = "SELECT s.*, u.username FROM stores s
                LEFT JOIN users u ON s.store_user_id = u.user_id
                WHERE s.store_status = 'A'
                ORDER BY s.store_id ASC
                LIMIT 1";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $storeData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$storeData) {
            return null;
        }

        return $this->mapToEntity($storeData);
    }

    /**
     * Map database row to Store entity
     */
    private function mapToEntity(array $storeData): Store
    {
        $store = new Store();

        $store->setStoreId((int) $storeData['store_id']);
        $store->setUserId((int) $storeData['store_user_id']);
        $store->setStoreStatus($storeData['store_status']);
        $store->setSlug($storeData['slug']);
        $store->setName($storeData['name']);
        $store->setDescription($storeData['description']);
        $store->setTheme($storeData['theme']);
        $store->setCreatedAt($storeData['created_at']);
        $store->setUpdatedAt($storeData['updated_at']);

        // Optional username from join
        if (isset($storeData['username'])) {
            $store->setUsername($storeData['username']);
        }

        return $store;
    }
}
