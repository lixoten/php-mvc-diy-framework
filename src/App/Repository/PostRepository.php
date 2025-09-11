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
        $sql = "SELECT p.*, u.username FROM posts p
                LEFT JOIN users u ON p.post_user_id = u.user_id
                WHERE p.post_id = :post_id";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':post_id', $postId);
        $stmt->execute();

        $postData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$postData) {
            return null;
        }

        return $this->mapToEntity($postData);
    }


    /**
     * Find posts by store ID
     */
    public function findByStoreId(
        int $storeId,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        return $this->findBy(
            ['post_store_id' => $storeId],
            $orderBy,
            $limit,
            $offset
        );
    }




    /**
     * Find posts based on criteria.
     *
     * @param array $criteria Optional filtering criteria (e.g., ['post_user_id' => 5, 'post_status' => 'Published'])
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
        $sql = "SELECT p.*, u.username FROM posts p
                LEFT JOIN users u ON p.post_user_id = u.user_id";
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
            ['post_user_id' => $userId],
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
        $sql = "INSERT INTO posts (
                post_store_id,
                post_user_id,
                post_status,
                slug,
                title,
                content,
                created_at,
                updated_at
            ) VALUES (
                :post_store_id,
                :post_user_id,
                :post_status,
                :slug,
                :title,
                :content,
                NOW(),
                NOW()
            )";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':post_store_id', $post->getPostStoreId());
        $stmt->bindValue(':post_user_id', $post->getPostUserId());
        $stmt->bindValue(':post_status', $post->getPostStatus());
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
        $sql = "UPDATE posts SET
                post_store_id = :post_store_id,
                post_user_id = :post_user_id,
                post_status = :post_status,
                slug = :slug,
                title = :title,
                content = :content,
                updated_at = NOW()
            WHERE post_id = :post_id";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':post_id', $post->getPostId());
        $stmt->bindValue(':post_store_id', $post->getPostStoreId());
        $stmt->bindValue(':post_user_id', $post->getPostUserId());
        $stmt->bindValue(':post_status', $post->getPostStatus());
        $stmt->bindValue(':slug', $post->getSlug());
        $stmt->bindValue(':title', $post->getTitle());
        $stmt->bindValue(':content', $post->getContent());

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Delete a post
     */
    public function delete(int $postId): bool
    {
        $sql = "DELETE FROM posts WHERE post_id = :post_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':post_id', $postId);
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
        $sql = "SELECT COUNT(*) as count FROM posts p"; // Use alias 'p'
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
        return $this->countBy(['post_store_id' => $storeId]);
    }

    /**
     * Counts posts associated with a specific user ID.
     */
    public function countByUserId(int $userId): int
    {
        // Simply reuse the existing countAll logic
        return $this->countBy(['post_user_id' => $userId]);
    }


    /**
     * Map database row to Post entity
     */
    private function mapToEntity(array $postData): Post
    {
        $post = new Post();

        $post->setPostId((int) $postData['post_id']);
        $post->setPostStoreId((int) $postData['post_store_id']);
        $post->setPostUserId((int) $postData['post_user_id']);
        $post->setPostStatus($postData['post_status']);
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
