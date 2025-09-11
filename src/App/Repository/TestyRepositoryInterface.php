<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entities\Testy;

interface TestyRepositoryInterface
{
    /**
     * Find a testy by ID
     */
    public function findById(int $testyId): ?Testy;

    /**
     * Find all testys
     *
     * @param array $criteria Optional filtering criteria
     * @param array $orderBy Optional sorting criteria
     * @param int|null $limit Maximum number of results
     * @param int|null $offset Result offset for pagination
     * @return Testy[] Array of Testy entities
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;


    /**
     * Find testys by store ID
     */
    public function findByStoreId(
        int $storeId,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Find testys by user ID
     *
     * @param int $userId The user ID to filter by
     * @param array $orderBy Optional sorting criteria
     * @param int|null $limit Maximum number of results
     * @param int|null $offset Result offset for pagination
     * @return Testy[] Array of Testy entities
     */
    public function findByUserId(
        int $userId,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Create a new testy
     *
     * @return Testy The created testy with ID
     */
    public function create(object $testy): object;

    /**
     * Update an existing testy
     *
     * @return bool True if update was successful
     */
    public function update(object $testy): bool;

    /**
     * Delete a testy
     *
     * @return bool True if deletion was successful
     */
    public function delete(int $testyId): bool;

    /**
     * Count total testys
     *
     * @param array $criteria Optional filtering criteria
     * @return int Total number of testys matching criteria
     */
    public function countBy(array $criteria = []): int;

    /**
     * Counts testys associated with a specific store ID.
     *
     * @param int $storeId
     * @return int
     */
    public function countByStoreId(int $storeId): int;

    /**
     * Counts testys associated with a specific user ID.
     *
     * @param int $userId
     * @return int
     */
    public function countByUserId(int $userId): int;

        /**
     * Convert a Testy entity to an array with selected fields.
     *
     * @param Testy $testy
     * @param array $fields
     * @return array
     */
    public function toArray(Testy $testy, array $fields = []): array;
}
