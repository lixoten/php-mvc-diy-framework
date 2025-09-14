<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entities\Testy;
use Core\Database\ConnectionInterface;
use Core\Repository\BaseRepositoryInterface;

class TestyRepository implements TestyRepositoryInterface, BaseRepositoryInterface
{
    private ConnectionInterface $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function saveDraft(array $data): bool // js-feature
    {
        // Auto Save / Draft Feature - JS
        // Example: Save draft to the main testys table (update only draft fields)
        $sql = "UPDATE testys SET
                    title = :title,
                    content = :content,
                    favorite_word = :favorite_word,
                    updated_at = NOW()
                WHERE testy_id = :testy_id";

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            ':testy_id' => $data['testy_id'] ?? null,
            ':title' => $data['title'] ?? '',
            ':content' => $data['content'] ?? '',
            ':favorite_word' => $data['favorite_word'] ?? '',
        ]);
    }


    /**
     * Find a testy by ID
     */
    public function findById(int $testyId): ?Testy
    {
        $sql = "SELECT p.*, u.username FROM testys p
                LEFT JOIN users u ON p.testy_user_id = u.user_id
                WHERE p.testy_id = :testy_id";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':testy_id', $testyId);
        $stmt->execute();

        $testyData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$testyData) {
            return null;
        }

        return $this->mapToEntity($testyData);
    }


    /**
     * Find testys by store ID
     */
    public function findByStoreId(
        int $storeId,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        return $this->findBy(
            ['testy_store_id' => $storeId],
            $orderBy,
            $limit,
            $offset
        );
    }




    /**
     * Find testys based on criteria.
     *
     * @param array $criteria Optional filtering criteria (e.g., ['testy_user_id' => 5, 'testy_status' => 'Published'])
     * @param array $orderBy Optional sorting criteria (e.g., ['created_at' => 'DESC'])
     * @param int|null $limit Maximum number of results
     * @param int|null $offset Result offset for pagination
     * @return Testy[] Array of Testy entities
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $sql = "SELECT p.*, u.username FROM testys p
                LEFT JOIN users u ON p.testy_user_id = u.user_id";
        $params = [];

        // Add WHERE clauses for criteria
        if (!empty($criteria)) {
            $whereClauses = [];
            foreach ($criteria as $field => $value) {
                $whereClauses[] = "p.$field = :$field";
                $params[":$field"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        // Add ORDER BY clause
        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $field => $direction) {
                $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $orderClauses[] = "p.$field $direction";
            }
            $sql .= " ORDER BY " . implode(', ', $orderClauses);
        } else {
            // Default ordering by creation date, newest first
            $sql .= " ORDER BY p.created_at DESC";
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

        $testys = [];
        while ($testyData = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $testys[] = $this->mapToEntity($testyData);
        }

        return $testys;
    }

    /**
     * Find testys by user ID
     */
    public function findByUserId(
        int $userId,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        return $this->findBy(
            ['testy_user_id' => $userId],
            $orderBy,
            $limit,
            $offset
        );
    }

    /**
     * Create a new testy
     */
    public function create(object $testy): object
    {
        $sql = "INSERT INTO testys (
                testy_store_id,
                testy_user_id,
                testy_status,
                slug,
                title,
                content,
                favorite_word,
                created_at,
                updated_at
            ) VALUES (
                :testy_store_id,
                :testy_user_id,
                :testy_status,
                :slug,
                :title,
                :content,
                :favorite_word,
                NOW(),
                NOW()
            )";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':testy_store_id', $testy->getTestyStoreId());
        $stmt->bindValue(':testy_user_id', $testy->getTestyUserId());
        $stmt->bindValue(':testy_status', $testy->getTestyStatus());
        $stmt->bindValue(':slug', $testy->getSlug());
        $stmt->bindValue(':title', $testy->getTitle());
        $stmt->bindValue(':content', $testy->getContent());
        $stmt->bindValue(':favorite_word', $testy->getFavoriteWord());

        $stmt->execute();

        // Get the ID of the newly created testy
        $testyId = (int) $this->connection->lastInsertId();
        $testy->setTestyId($testyId);

        // Fetch fresh from database to get created_at and updated_at
        return $this->findById($testyId);
    }

    // /** @var App\Entities\Testy */
    /**
     * Update an existing testy
     */
    public function update(object $testy): bool
    {
        $sql = "UPDATE testys SET
                testy_store_id = :testy_store_id,
                testy_user_id = :testy_user_id,
                testy_status = :testy_status,
                slug = :slug,
                title = :title,
                content = :content,
                favorite_word = :favorite_word,
                updated_at = NOW()
            WHERE testy_id = :testy_id";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':testy_id', $testy->getTestyId());
        $stmt->bindValue(':testy_store_id', $testy->getTestyStoreId());
        $stmt->bindValue(':testy_user_id', $testy->getTestyUserId());
        $stmt->bindValue(':testy_status', $testy->getTestyStatus());
        $stmt->bindValue(':slug', $testy->getSlug());
        $stmt->bindValue(':title', $testy->getTitle());
        $stmt->bindValue(':content', $testy->getContent());
        $stmt->bindValue(':favorite_word', $testy->getFavoriteWord());

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Delete a testy
     */
    public function delete(int $testyId): bool
    {
        $sql = "DELETE FROM testys WHERE testy_id = :testy_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':testy_id', $testyId);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Count total testys based on criteria.
     *
     * @param array $criteria Optional filtering criteria
     * @return int The total count.
     */
    public function countBy(array $criteria = []): int // Renamed from countAll
    {
        $sql = "SELECT COUNT(*) as count FROM testys p"; // Use alias 'p'
        $params = [];

        // Add WHERE clauses for criteria
        if (!empty($criteria)) {
            $whereClauses = [];
            foreach ($criteria as $field => $value) {
                $placeholder = ':' . str_replace('.', '_', $field);
                $whereClauses[] = "p.$field = $placeholder"; // Use alias 'p'
                $params[$placeholder] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $stmt = $this->connection->prepare($sql);

        // Bind parameters
        foreach ($params as $param => $value) {
            $pdoType = \PDO::PARAM_STR;
            if (is_int($value)) {
                $pdoType = \PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $pdoType = \PDO::PARAM_BOOL;
            } elseif (is_null($value)) {
                $pdoType = \PDO::PARAM_NULL;
            }
            $stmt->bindValue($param, $value, $pdoType);
        }

        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int) ($result['count'] ?? 0); // Ensure integer return, default 0
    }



    /**
     * Counts testys associated with a specific store ID.
     */
    public function countByStoreId(int $storeId): int
    {
        // Simply reuse the existing countAll logic
        return $this->countBy(['testy_store_id' => $storeId]);
    }

    /**
     * Counts testys associated with a specific user ID.
     */
    public function countByUserId(int $userId): int
    {
        // Simply reuse the existing countAll logic
        return $this->countBy(['testy_user_id' => $userId]);
    }


    /**
     * Map database row to Testy entity
     */
    private function mapToEntity(array $testyData): Testy
    {
        $testy = new Testy();

        $testy->setTestyId((int) $testyData['testy_id']);
        $testy->setTestyStoreId((int) $testyData['testy_store_id']);
        $testy->setTestyUserId((int) $testyData['testy_user_id']);
        $testy->setTestyStatus($testyData['testy_status']);
        $testy->setSlug($testyData['slug']);
        $testy->setTitle($testyData['title']);
        $testy->setContent($testyData['content']);
        $testy->setFavoriteWord($testyData['favorite_word']);
        $testy->setCreatedAt($testyData['created_at']);
        $testy->setUpdatedAt($testyData['updated_at']);

        // Optional username from join
        if (isset($testyData['username'])) {
            $testy->setUsername($testyData['username']);
        }

        return $testy;
    }

    /**
     * Convert a Testy entity to an array with selected fields.
     *
     * @param Testy $testy
     * @param array $fields
     * @return array
     */
    public function toArray(Testy $testy, array $fields = []): array
    {
        $allFields = [
            'id' => $testy->getTestyId(),
            'store_id' => $testy->getTestyStoreId(),
            'user_id' => $testy->getTestyUserId(),
            'status' => match ($testy->getTestyStatus()) {
                'A' => '☑️ Active',
                'P' => '⏳ Pending',
                'I' => '❌ Inactive',
                default => '❓ Unknown'
            },
            'slug' => $testy->getSlug(),
            'title' => $testy->getTitle(),
            'content' => $testy->getContent(),
            'favorite_word' => $testy->getFavoriteWord(),
            'created_at' => $testy->getCreatedAt(),
            'updated_at' => $testy->getUpdatedAt(),
            'username' => $testy->getUsername(),
        ];

        // 'created_at' => $testy->getCreatedAt() instanceof \DateTime ?
            // $testy->getCreatedAt()->format('Y-m-d H:i:s') : $testy->getCreatedAt(),
        // 'updated_at' => $testy->getUpdatedAt() instanceof \DateTime ?
            // $testy->getUpdatedAt()->format('Y-m-d H:i:s') : $testy->getUpdatedAt(),




        // Return only the requested fields
        if (!empty($fields)) {
            return array_intersect_key($allFields, array_flip($fields));
        }

        // Return all fields by default
        return $allFields;
    }
}
