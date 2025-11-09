<?php

declare(strict_types=1);

// namespace App\Repository;
namespace App\Features\Store;

// use App\Entities\Store;
// use App\Features\Store;
use App\Features\Store\Store;
use Core\Repository\BaseRepositoryInterface;

/**
 * Generated File - Date: 20251102_134902zz
 * interface for Store.
 */
interface StoreRepositoryInterface extends BaseRepositoryInterface
{
#    /**
#     * Find a Store by ID with full entity mapping.
#     *
#     * @param int $id
#     * @return Store|null
#     */
#    public function findById(int $id): ?Store;
#
#    /**
#     * Find a Store by ID, selecting only specified columns (raw data).
#     *
#     * @param int $id
#     * @param array<string> $fields
#     * @return array<string, mixed>|null
#     */
#    public function findByIdWithFields(int $id, array $fields): ?array;
#
#    /**
#     * Find Store records based on criteria with full entity mapping.
#     *
#     * @param array<string, mixed> $criteria
#     * @param array<string, string> $orderBy
#     * @param int|null $limit
#     * @param int|null $offset
#     * @return array<Store>
#     */
#    public function findBy(
#        array $criteria = [],
#        array $orderBy = [],
#        ?int $limit = null,
#        ?int $offset = null
#    ): array;
#
#    /**
#     * Find entities by user ID with full entity mapping.
#     * Child repositories must implement findBy() to use this method.
#     *
#     * @param int $userId
#     * @param array<string, string> $orderBy
#     * @param int|null $limit
#     * @param int|null $offset
#     * @return array<object>
#     */
#    public function findByUserId(
#        int $userId,
#        array $orderBy = [],
#        ?int $limit = null,
#        ?int $offset = null
#    ): array;
#
#
#    /**
#     * Create a new Store.
#     *
#     * @param Store $store
#     * @return Store The created Store with ID
#     */
#    public function create(Store $store): Store;
#
#    /**
#     * Update an existing Store.
#     *
#     * @param Store $store
#     * @return bool True if update was successful
#     */
#    public function update(Store $store): bool;
#
#    /**
#     * Update selected fields for a Store by its primary ID.
#     *
#     * @param int $id The record ID.
#     * @param array<string, mixed> $fieldsToUpdate Associative array of fields to update.
#     * @return bool True on success, false on failure.
#     */
#    public function updateFields(int $id, array $fieldsToUpdate): bool;
#
#    /**
#     * Delete a Store (hard delete).
#     *
#     * @param int $id
#     * @return bool True if deletion was successful
#     */
#    public function delete(int $id): bool;
#
#    /**
#     * Count total Store records matching criteria.
#     *
#     * @param array<string, mixed> $criteria Optional filtering criteria
#     * @return int Total number of records matching criteria
#     */
#    public function countBy(array $criteria = []): int;
#
#    /**
#     * Convert a Store entity to an array with selected fields.
#     *
#     * @param Store $store
#     * @param array<string> $fields
#     * @return array<string, mixed>
#     */
#    public function toArray(Store $store, array $fields = []): array;
}
