<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entities\Store;

interface StoreRepositoryInterface
{
    /**
     * Find a store by ID
     */
    public function findById(int $storeId): ?Store;

    /**
     * Find a store by slug
     */
    public function findBySlug(string $slug): ?Store;

    /**
     * Find a store by user ID
     */
    public function findByUserId(int $userId): ?Store;

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
    ): array;

    /**
     * Find stores by status
     *
     * @param string $storeStatus The status to filter by (A=Active, I=Inactive, S=Suspended)
     * @param array $orderBy Optional sorting criteria
     * @param int|null $limit Maximum number of results
     * @param int|null $offset Result offset for pagination
     * @return Store[] Array of Store entities
     */
    public function findByStatus(
        string $storeStatus,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Create a new store
     *
     * @return Store The created store with ID
     */
    public function create(Store $store): Store;

    /**
     * Update an existing store
     *
     * @return bool True if update was successful
     */
    public function update(Store $store): bool;

    /**
     * Delete a store
     *
     * @return bool True if deletion was successful
     */
    public function delete(int $storeId): bool;

    /**
     * Count total stores
     *
     * @param array $criteria Optional filtering criteria
     * @return int Total number of stores matching criteria
     */
    public function countBy(array $criteria = []): int;

    /**
     * Check if slug exists
     *
     * @param string $slug The slug to check
     * @param int|null $excludeStoreId Optional store ID to exclude from check
     * @return bool True if slug exists
     */
    public function slugExists(string $slug, ?int $excludeStoreId = null): bool;

    /**
     * Get primary/default store (useful for single-store mode)
     *
     * @return Store|null The primary store or null if none exists
     */
    public function getPrimaryStore(): ?Store;
}
