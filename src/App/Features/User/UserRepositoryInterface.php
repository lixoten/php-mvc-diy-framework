<?php

declare(strict_types=1);

namespace App\Features\User;

// use App\Entities\User;
use Core\Repository\BaseRepositoryInterface;

/**
 * Generated File - Date: 20251104_174033
 * interface for User.
 */
interface UserRepositoryInterface extends BaseRepositoryInterface
{
#    /**
#     * Find a User by ID with full entity mapping.
#     *
#     * @param int $id
#     * @return User|null
#     */
#    public function findById(int $id): ?User;
#
#    /**
#     * Find a User by ID, selecting only specified columns (raw data).
#     *
#     * @param int $id
#     * @param array<string> $fields
#     * @return array<string, mixed>|null
#     */
#    public function findByIdWithFields(int $id, array $fields): ?array;
#
#    /**
#     * Find User records based on criteria with full entity mapping.
#     *
#     * @param array<string, mixed> $criteria
#     * @param array<string, string> $orderBy
#     * @param int|null $limit
#     * @param int|null $offset
#     * @return array<User>
#     */
#    public function findBy(
#        array $criteria = [],
#        array $orderBy = [],
#        ?int $limit = null,
#        ?int $offset = null
#    ): array;
#
#    /**
#     * Create a new User.
#     *
#     * @param User $user
#     * @return User The created User with ID
#     */
#    public function create(User $user): User;
#
#    /**
#     * Update an existing User.
#     *
#     * @param User $user
#     * @return bool True if update was successful
#     */
#    public function update(User $user): bool;
#
#    /**
#     * Update selected fields for a User by its primary ID.
#     *
#     * @param int $id The record ID.
#     * @param array<string, mixed> $fieldsToUpdate Associative array of fields to update.
#     * @return bool True on success, false on failure.
#     */
#    public function updateFields(int $id, array $fieldsToUpdate): bool;
#
#    /**
#     * Delete a User (hard delete).
#     *
#     * @param int $id
#     * @return bool True if deletion was successful
#     */
#    public function delete(int $id): bool;
#
#    /**
#     * Count total User records matching criteria.
#     *
#     * @param array<string, mixed> $criteria Optional filtering criteria
#     * @return int Total number of records matching criteria
#     */
#    public function countBy(array $criteria = []): int;
#
#    /**
#     * Convert a User entity to an array with selected fields.
#     *
#     * @param User $user The User record to convert.
#     * @param array<string> $fields Optional list of specific fields to include.
#     * @return array<string, mixed> Array representation of User record.
#     */
#    public function toArray(User $user, array $fields = []): array;
#
}
