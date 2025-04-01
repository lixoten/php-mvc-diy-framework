<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entities\User;
use App\Enums\UserStatus;

interface UserRepositoryInterface
{
    /**
     * Find a user by ID
     */
    public function findById(int $userId): ?User;

    /**
     * Find a user by username
     */
    public function findByUsername(string $username): ?User;

    /**
     * Find a user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find a user by activation token
     */
    public function findByActivationToken(string $token): ?User;

    /**
     * Find a user by reset token
     */
    public function findByResetToken(string $token): ?User;

    /**
     * Create a new user
     *
     * @return User The created user with ID
     */
    public function create(User $user): User;

    /**
     * Update an existing user
     *
     * @return bool True if update was successful
     */
    public function update(User $user): bool;

    /**
     * Delete a user
     *
     * @return bool True if deletion was successful
     */
    public function delete(int $userId): bool;

    /**
     * Find all users
     *
     * @param array $criteria Optional filtering criteria
     * @param array $orderBy Optional sorting criteria
     * @param int|null $limit Maximum number of results
     * @param int|null $offset Result offset for pagination
     * @return User[] Array of User entities
     */
    public function findAll(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Find users by status
     *
     * @param UserStatus $status The status to filter by
     * @param array $orderBy Optional sorting criteria
     * @param int|null $limit Maximum number of results
     * @param int|null $offset Result offset for pagination
     * @return User[] Array of User entities
     */
    public function findByStatus(
        UserStatus $status,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Find users by role
     *
     * @param string $role The role to search for
     * @param array $orderBy Optional sorting criteria
     * @param int|null $limit Maximum number of results
     * @param int|null $offset Result offset for pagination
     * @return User[] Array of User entities
     */
    public function findByRole(
        string $role,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Count total users
     *
     * @param array $criteria Optional filtering criteria
     * @return int Total number of users matching criteria
     */
    public function countAll(array $criteria = []): int;

    /**
     * Check if username exists
     *
     * @param string $username The username to check
     * @param int|null $excludeUserId Optional user ID to exclude from check
     * @return bool True if username exists
     */
    public function usernameExists(string $username, ?int $excludeUserId = null): bool;

    /**
     * Check if email exists
     *
     * @param string $email The email to check
     * @param int|null $excludeUserId Optional user ID to exclude from check
     * @return bool True if email exists
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool;
}
