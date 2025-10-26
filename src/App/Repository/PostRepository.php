<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entities\Post;
use Core\Database\ConnectionInterface;
use Core\Repository\BaseRepositoryInterface;

class PostRepository implements PostRepositoryInterface, BaseRepositoryInterface
{
    private ConnectionInterface $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Find a post by ID
     */
    public function findById(int $postId): ?Post
    {
        $sql = "SELECT p.*, u.username FROM post p
                LEFT JOIN users u ON p.user_id = u.user_id
                WHERE p.id = :id";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $postId);
        $stmt->execute();

        $postData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$postData) {
            return null;
        }

        return $this->mapToEntity($postData);
    }


    /**
     * Find a post by ID, selecting only specified columns.
     *
     * @param int $postId
     * @param array<string> $fields
     * @return array<string, mixed>|null
     */
    public function findByIdWithFields(int $id, array $fields): ?array
    {
        if (empty($fields)) {
            // Default to all columns if none specified
            $fields = ['*'];
        }
        $columns = implode(', ', $fields);

        $sql = "SELECT $columns FROM post WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result === false) {
            return null;
        }

        return $result;
    }



    /**
     * Find post by store ID
     */
    public function findByStoreId(
        int $storeId,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        return $this->findBy(
            ['store_id' => $storeId],
            $orderBy,
            $limit,
            $offset
        );
    }





    /** {@inheritdoc} */
    public function findByStoreIdWithFields(
        int $storeId,
        array $fields,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        if (empty($fields)) {
            $fields = ['*'];
        } else {
            $fields = array_merge($fields, ['id', 'status']);
            // unset($fields['id']);
            // unset($fields['status']);
        }

        foreach ($fields as $f) {
            if (!preg_match('/^[A-Za-z0-9_\\.]+$/', (string) $f)) {
                throw new \InvalidArgumentException("Invalid field name: {$f}");
            }
        }

        $columns = implode(', ', $fields);
        $sql = "SELECT {$columns} FROM post p WHERE p.store_id = :store_id";

        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $field => $direction) {
                $dir = strtoupper((string) $direction) === 'DESC' ? 'DESC' : 'ASC';
                $cleanField = strpos((string) $field, '.') === false ? "p.{$field}" : (string) $field;
                if (!preg_match('/^[A-Za-z0-9_\\.]+$/', $cleanField)) {
                    throw new \InvalidArgumentException("Invalid order field: {$cleanField}");
                }
                $orderClauses[] = "{$cleanField} {$dir}";
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
        } else {
            $sql .= ' ORDER BY p.created_at DESC';
        }

        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
            if ($offset !== null) {
                $sql .= ' OFFSET :offset';
            }
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':store_id', $storeId, \PDO::PARAM_INT);

        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        }
        if ($offset !== null) {
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        }

        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($rows === false) {
            return [];
        }

        return $rows;
    }




    /** {@inheritdoc} */
    public function findByUserIdWithFields(
        int $userId,
        array $fields,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        if (empty($fields)) {
            $fields = ['*'];
        }

        foreach ($fields as $f) {
            if (!preg_match('/^[A-Za-z0-9_\\.]+$/', (string) $f)) {
                throw new \InvalidArgumentException("Invalid field name: {$f}");
            }
        }

        $columns = implode(', ', $fields);
        $sql = "SELECT {$columns} FROM post p WHERE p.user_id = :user_id";

        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $field => $direction) {
                $dir = strtoupper((string) $direction) === 'DESC' ? 'DESC' : 'ASC';
                $cleanField = strpos((string) $field, '.') === false ? "p.{$field}" : (string) $field;
                if (!preg_match('/^[A-Za-z0-9_\\.]+$/', $cleanField)) {
                    throw new \InvalidArgumentException("Invalid order field: {$cleanField}");
                }
                $orderClauses[] = "{$cleanField} {$dir}";
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
        } else {
            $sql .= ' ORDER BY p.created_at DESC';
        }

        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
            if ($offset !== null) {
                $sql .= ' OFFSET :offset';
            }
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);

        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        }

        if ($offset !== null) {
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        }

        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($rows === false) {
            return [];
        }

        return $rows;
    }





    /**
     * Find post based on criteria.
     *
     * @param array $criteria Optional filtering criteria (e.g., ['user_id' => 5, 'status' => 'Published'])
     * @param array $orderBy Optional sorting criteria (e.g., ['created_at' => 'DESC'])
     * @param int|null $limit Maximum number of results
     * @param int|null $offset Result offset for pagination
     * @return Post[] Array of Post entities
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $sql = "SELECT p.*, u.username FROM post p
                LEFT JOIN users u ON p.user_id = u.user_id";
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

        $posts = [];
        while ($postData = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $posts[] = $this->mapToEntity($postData);
        }

        return $posts;
    }

    /**
     * Find posts by user ID
     */
    public function findByUserId(
        int $userId,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        return $this->findBy(
            ['user_id' => $userId],
            $orderBy,
            $limit,
            $offset
        );
    }

    /**
     * Create a new post
     */
    public function create(object $post): object
    {
        $sql = "INSERT INTO post (
                store_id,
                user_id,
                status,
                slug,
                title,
                content,
                created_at,
                updated_at
            ) VALUES (
                :store_id,
                :user_id,
                :status,
                :slug,
                :title,
                :content,
                NOW(),
                NOW()
            )";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':store_id', $post->getPostStoreId());
        $stmt->bindValue(':user_id', $post->getPostUserId());
        $stmt->bindValue(':status', $post->getPostStatus());
        $stmt->bindValue(':slug', $post->getSlug());
        $stmt->bindValue(':title', $post->getTitle());
        $stmt->bindValue(':content', $post->getContent());

        $stmt->execute();

        // Get the ID of the newly created post
        $postId = (int) $this->connection->lastInsertId();
        $post->setPostId($postId);

        // Fetch fresh from database to get created_at and updated_at
        return $this->findById($postId);
    }

    // /** @var App\Entities\Post */
    /**
     * Update an existing post
     */
    public function update(object $post): bool
    {
        $sql = "UPDATE post SET
                store_id = :store_id,
                user_id = :user_id,
                status = :status,
                slug = :slug,
                title = :title,
                content = :content,
                updated_at = NOW()
            WHERE id = :id";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':id', $post->getPostId());
        $stmt->bindValue(':store_id', $post->getPostStoreId());
        $stmt->bindValue(':user_id', $post->getPostUserId());
        $stmt->bindValue(':status', $post->getPostStatus());
        $stmt->bindValue(':slug', $post->getSlug());
        $stmt->bindValue(':title', $post->getTitle());
        $stmt->bindValue(':content', $post->getContent());

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Update only the specified fields for a Post record.
     *
     * @param int $postId
     * @param array<string, mixed> $fieldsToUpdate
     * @return bool
     */
    public function updateFields(int $id, array $fieldsToUpdate): bool
    {
        if (empty($fieldsToUpdate)) {
            return false;
        }

        $setClauses = [];
        $params = [':id' => $id];

        foreach ($fieldsToUpdate as $field => $value) {
            $setClauses[] = "$field = :$field";
            $params[":$field"] = $value;
        }

        $setClauses[] = "updated_at = NOW()";
        $sql = "UPDATE post SET " . implode(', ', $setClauses) . " WHERE id = :id";

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute($params);
    }



    /**
     * Insert a new record into the post table.
     *
     * @param array<string, mixed> $data The data to insert.
     * @return int The ID of the newly inserted record.
     */
    public function insertFields(array $data): int
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Data array cannot be empty.');
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);
        $sql = "INSERT INTO post (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $this->connection->prepare($sql);

        foreach ($data as $col => $value) {
            $stmt->bindValue(":$col", $value);
        }

        $stmt->execute();

        return (int) $this->connection->lastInsertId();
    }



    /**
     * Delete a post
     */
    public function delete(int $postId): bool
    {
        $sql = "DELETE FROM post WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $postId);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Count total posts based on criteria.
     *
     * @param array $criteria Optional filtering criteria
     * @return int The total count.
     */
    public function countBy(array $criteria = []): int // Renamed from countAll
    {
        $sql = "SELECT COUNT(*) as count FROM post p"; // Use alias 'p'
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
     * Counts posts associated with a specific store ID.
     */
    public function countByStoreId(int $storeId): int
    {
        // Simply reuse the existing countAll logic
        return $this->countBy(['store_id' => $storeId]);
    }

    /**
     * Counts posts associated with a specific user ID.
     */
    public function countByUserId(int $userId): int
    {
        // Simply reuse the existing countAll logic
        return $this->countBy(['user_id' => $userId]);
    }


    /**
     * Map database row to Post entity
     */
    private function mapToEntity(array $postData): Post
    {
        $post = new Post();

        $post->setPostId((int) $postData['id']);
        $post->setPostStoreId((int) $postData['store_id']);
        $post->setPostUserId((int) $postData['user_id']);
        $post->setPostStatus($postData['status']);
        $post->setSlug($postData['slug']);
        $post->setTitle($postData['title']);
        $post->setContent($postData['content']);
        $post->setCreatedAt($postData['created_at']);
        $post->setUpdatedAt($postData['updated_at']);

        // Optional username from join
        if (isset($postData['username'])) {
            $post->setUsername($postData['username']);
        }

        return $post;
    }

    /**
     * Convert a Post entity to an array with selected fields.
     *
     * @param Post $post
     * @param array $fields
     * @return array
     */
    public function toArray(Post $post, array $fields = []): array
    {
        $allFields = [
            'id' => $post->getPostId(),
            'store_id' => $post->getPostStoreId(),
            'user_id' => $post->getPostUserId(),
            'status' => match ($post->getPostStatus()) {
                'A' => '☑️ Active',
                'P' => '⏳ Pending',
                'I' => '❌ Inactive',
                default => '❓ Unknown'
            },
            'slug' => $post->getSlug(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'created_at' => $post->getCreatedAt(),
            'updated_at' => $post->getUpdatedAt(),
            'username' => $post->getUsername(),
        ];

        // 'created_at' => $post->getCreatedAt() instanceof \DateTime ?
            // $post->getCreatedAt()->format('Y-m-d H:i:s') : $post->getCreatedAt(),
        // 'updated_at' => $post->getUpdatedAt() instanceof \DateTime ?
            // $post->getUpdatedAt()->format('Y-m-d H:i:s') : $post->getUpdatedAt(),




        // Return only the requested fields
        if (!empty($fields)) {
            return array_intersect_key($allFields, array_flip($fields));
        }

        // Return all fields by default
        return $allFields;
    }
}
