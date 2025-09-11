<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entities\Post;

interface PostRepositoryInterface
{
    /**
     * Find a post by ID
     */
    public function findById(int $postId): ?Post;

    /**
     * Find all posts
     *
     * @param array $criteria Optional filtering criteria
     * @param array $orderBy Optional sorting criteria
     * @param int|null $limit Maximum number of results
     * @param int|null $offset Result offset for pagination
     * @return Post[] Array of Post entities
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;


    /**
     * Find posts by store ID
     */
    public function findByStoreId(
        int $storeId,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Find posts by user ID
     *
     * @param int $userId The user ID to filter by
     * @param array $orderBy Optional sorting criteria
     * @param int|null $limit Maximum number of results
     * @param int|null $offset Result offset for pagination
     * @return Post[] Array of Post entities
     */
    public function findByUserId(
        int $userId,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Create a new post
     *
     * @return Post The created post with ID
     */
    public function create(object $post): object;

    /**
     * Update an existing post
     *
     * @return bool True if update was successful
     */
    public function update(object $post): bool;

    /**
     * Delete a post
     *
     * @return bool True if deletion was successful
     */
    public function delete(int $postId): bool;

    /**
     * Count total posts
     *
     * @param array $criteria Optional filtering criteria
     * @return int Total number of posts matching criteria
     */
    public function countBy(array $criteria = []): int;

    /**
     * Counts posts associated with a specific store ID.
     *
     * @param int $storeId
     * @return int
     */
    public function countByStoreId(int $storeId): int;

    /**
     * Counts posts associated with a specific user ID.
     *
     * @param int $userId
     * @return int
     */
    public function countByUserId(int $userId): int;

        /**
     * Convert a Post entity to an array with selected fields.
     *
     * @param Post $post
     * @param array $fields
     * @return array
     */
    public function toArray(Post $post, array $fields = []): array;
}
