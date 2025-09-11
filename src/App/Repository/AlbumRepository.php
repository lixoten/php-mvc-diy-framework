<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entities\Album;
use Core\Database\ConnectionInterface;
use Core\Repository\BaseRepositoryInterface;

// class AlbumRepository implements AlbumRepositoryInterface, BaseRepositoryInterface
class AlbumRepository implements AlbumRepositoryInterface
{
    private ConnectionInterface $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Find an album by ID
     */
    public function findById(int $albumId): ?Album
    {
        $sql = "SELECT a.*, u.username FROM albums a
                LEFT JOIN users u ON a.album_user_id = u.user_id
                WHERE a.album_id = :album_id";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':album_id', $albumId);
        $stmt->execute();

        $albumData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$albumData) {
            return null;
        }

        return $this->mapToEntity($albumData);
    }

    /**
     * Find album by slug
     */
    public function findBySlug(string $slug): ?Album
    {
        $sql = "SELECT a.*, u.username FROM albums a
                LEFT JOIN users u ON a.album_user_id = u.user_id
                WHERE a.slug = :slug";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        $stmt->execute();

        $albumData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$albumData) {
            return null;
        }

        return $this->mapToEntity($albumData);
    }

    /**
     * Find albums by store ID
     */
    public function findByStoreId(
        int $storeId,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        return $this->findBy(
            ['album_store_id' => $storeId],
            $orderBy,
            $limit,
            $offset
        );
    }

    /**
     * Find albums based on criteria.
     *
     * @param array $criteria Optional filtering criteria (e.g., ['album_user_id' => 5, 'album_status' => 'P'])
     * @param array $orderBy Optional sorting criteria (e.g., ['created_at' => 'DESC'])
     * @param int|null $limit Maximum number of results
     * @param int|null $offset Result offset for pagination
     * @return Album[] Array of Album entities
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $sql = "SELECT a.*, u.username FROM albums a
                LEFT JOIN users u ON a.album_user_id = u.user_id";
        $params = [];

        // Add WHERE clauses for criteria
        if (!empty($criteria)) {
            $whereClauses = [];
            foreach ($criteria as $field => $value) {
                $whereClauses[] = "a.$field = :$field";
                $params[":$field"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        // Add ORDER BY clause
        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $field => $direction) {
                $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $orderClauses[] = "a.$field $direction";
            }
            $sql .= " ORDER BY " . implode(', ', $orderClauses);
        } else {
            // Default ordering by creation date, newest first
            $sql .= " ORDER BY a.created_at DESC";
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

        $albums = [];
        while ($albumData = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $albums[] = $this->mapToEntity($albumData);
        }

        return $albums;
    }

    /**
     * Find albums by user ID
     */
    public function findByUserId(
        int $userId,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        return $this->findBy(
            ['album_user_id' => $userId],
            $orderBy,
            $limit,
            $offset
        );
    }

    /**
     * Create a new album
     */
    public function create(Album $album): Album
    {
        $sql = "INSERT INTO albums (
                album_store_id,
                album_user_id,
                album_status,
                slug,
                name,
                description,
                created_at,
                updated_at
            ) VALUES (
                :album_store_id,
                :album_user_id,
                :album_status,
                :slug,
                :name,
                :description,
                NOW(),
                NOW()
            )";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':album_store_id', $album->getAlbumStoreId());
        $stmt->bindValue(':album_user_id', $album->getAlbumUserId());
        $stmt->bindValue(':album_status', $album->getAlbumStatus());
        $stmt->bindValue(':slug', $album->getSlug());
        $stmt->bindValue(':name', $album->getName());
        $stmt->bindValue(':description', $album->getDescription());

        $stmt->execute();

        // Get the ID of the newly created album
        $albumId = (int) $this->connection->lastInsertId();
        $album->setAlbumId($albumId);

        // Fetch fresh from database to get created_at and updated_at
        return $this->findById($albumId);
    }

    /**
     * Update an existing album
     */
    public function update(Album $album): bool
    {
        $sql = "UPDATE albums SET
                album_store_id = :album_store_id,
                album_user_id = :album_user_id,
                album_status = :album_status,
                slug = :slug,
                name = :name,
                description = :description,
                updated_at = NOW()
            WHERE album_id = :album_id";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':album_id', $album->getAlbumId());
        $stmt->bindValue(':album_store_id', $album->getAlbumStoreId());
        $stmt->bindValue(':album_user_id', $album->getAlbumUserId());
        $stmt->bindValue(':album_status', $album->getAlbumStatus());
        $stmt->bindValue(':slug', $album->getSlug());
        $stmt->bindValue(':name', $album->getName());
        $stmt->bindValue(':description', $album->getDescription());

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Delete an album
     */
    public function delete(int $albumId): bool
    {
        $sql = "DELETE FROM albums WHERE album_id = :album_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':album_id', $albumId);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Count total albums based on criteria.
     *
     * @param array $criteria Optional filtering criteria
     * @return int The total count.
     */
    public function countBy(array $criteria = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM albums a";
        $params = [];

        // Add WHERE clauses for criteria
        if (!empty($criteria)) {
            $whereClauses = [];
            foreach ($criteria as $field => $value) {
                $placeholder = ':' . str_replace('.', '_', $field);
                $whereClauses[] = "a.$field = $placeholder";
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

        return (int) ($result['count'] ?? 0);
    }

    /**
     * Counts albums associated with a specific store ID.
     */
    public function countByStoreId(int $storeId): int
    {
        return $this->countBy(['album_store_id' => $storeId]);
    }

    /**
     * Map database row to Album entity
     */
    private function mapToEntity(array $albumData): Album
    {
        $album = new Album();

        $album->setAlbumId((int) $albumData['album_id']);
        $album->setAlbumStoreId((int) $albumData['album_store_id']);
        $album->setAlbumUserId((int) $albumData['album_user_id']);
        $album->setAlbumStatus($albumData['album_status']);
        $album->setSlug($albumData['slug']);
        $album->setName($albumData['name']);
        $album->setDescription($albumData['description']);
        $album->setCreatedAt($albumData['created_at']);
        $album->setUpdatedAt($albumData['updated_at']);

        // notes-: if we want date format...maybe
        // Convert date strings to DateTime objects
        // $album->setCreatedAt(new \DateTime($albumData['created_at']));
        // $album->setUpdatedAt(new \DateTime($albumData['updated_at']));



        // Optional username from join
        if (isset($albumData['username'])) {
            $album->setUsername($albumData['username']);
        }

        return $album;
    }


    public function toArray(Album $album, array $fields = []): array
    {
        $allFields = [
            'id' => $album->getAlbumId(),
            'store_id' => $album->getAlbumStoreId(),
            'user_id' => $album->getAlbumUserId(),
            'status' => match ($album->getAlbumStatus()) {
                'A' => '☑️ Active',
                'P' => '⏳ Pending',
                'I' => '❌ Inactive',
                default => '❓ Unknown'
            },
            'slug' => $album->getSlug(),
            'name' => $album->getName(),
            'description' => $album->getDescription(),
            'created_at' => $album->getCreatedAt(),
            'updated_at' => $album->getUpdatedAt(),
            'username' => $album->getUsername(),
        ];

        // Return only the requested fields
        if (!empty($fields)) {
            return array_intersect_key($allFields, array_flip($fields));
        }

        // Return all fields by default
        return $allFields;
    }
}
