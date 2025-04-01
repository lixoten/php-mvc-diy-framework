<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entities\RememberToken;

interface RememberTokenRepositoryInterface
{
    /**
     * Create a new remember token
     */
    public function create(int $userId, string $selector, string $hashedValidator, string $expiresAt): bool;

    /**
     * Find a token by selector
     */
    public function findBySelector(string $selector): ?RememberToken;

    /**
     * Delete a token by user ID
     */
    public function deleteByUserId(int $userId): bool;

    /**
     * Delete a token by selector
     */
    public function deleteBySelector(string $selector): bool;

    /**
     * Delete expired tokens
     */
    public function deleteExpired(): int;
}
