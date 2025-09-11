<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entities\Album;

interface AlbumRepositoryInterface
{
    /**
     * Find a album by ID
     */
    public function findById(int $albumId): ?Album;

    /**
     * Find all albums
     *
     * @param array $criteria Optional filtering criteria
     * @param array $orderBy Optional sorting criteria
     * @param int|null $limit Maximum number of results
     * @param int|null $offset Result offset for pagination
     * @return Album[] Array of Album entities
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;


    /**
     * Find albums by store ID
     */
    public function findByStoreId(
        int $storeId,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Find albums by user ID
     *
     * @param int $userId The user ID to filter by
     * @param array $orderBy Optional sorting criteria
     * @param int|null $limit Maximum number of results
     * @param int|null $offset Result offset for pagination
     * @return Album[] Array of Album entities
     */
    public function findByUserId(
        int $userId,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Create a new album
     *
     * @return Album The created album with ID
     */
    public function create(Album $album): Album;

    /**
     * Update an existing album
     *
     * @return bool True if update was successful
     */
    public function update(Album $album): bool;

    /**
     * Delete a album
     *
     * @return bool True if deletion was successful
     */
    public function delete(int $albumId): bool;

    /**
     * Count total albums
     *
     * @param array $criteria Optional filtering criteria
     * @return int Total number of albums matching criteria
     */
    public function countBy(array $criteria = []): int;

    /**
     * Counts albums associated with a specific store ID.
     *
     * @param int $storeId
     * @return int
     */
    public function countByStoreId(int $storeId): int;

     /**
     * Transform an Album entity into an array
     *
     * @param Album $album The album entity to transform
     * @param array $fields Optional list of fields to include in the array
     * @return array The transformed album as an associative array
     */
    public function toArray(Album $album, array $fields = []): array;
}
