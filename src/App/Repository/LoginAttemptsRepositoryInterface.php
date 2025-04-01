<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entities\LoginAttempt;

interface LoginAttemptsRepositoryInterface
{
    /**
     * Record a failed login attempt
     *
     * @param array $data Data for the login attempt (username_or_email, ip_address, etc.)
     * @return bool True if the attempt was recorded successfully
     */
    public function record(array $data): bool;

    /**
     * Count recent failed login attempts for a specific username/email
     *
     * @param string $usernameOrEmail The username or email to check
     * @param int $since Timestamp to count attempts since
     * @return int Number of recent attempts
     */
    public function countRecentAttempts(string $usernameOrEmail, int $since): int;

    /**
     * Count recent failed login attempts for a specific IP address
     *
     * @param string $ipAddress The IP address to check
     * @param int $since Timestamp to count attempts since
     * @return int Number of recent attempts
     */
    public function countRecentAttemptsFromIp(string $ipAddress, int $since): int;

    /**
     * Clear failed login attempts for a specific user
     *
     * @param string $usernameOrEmail The username or email to clear attempts for
     * @return bool True if attempts were cleared
     */
    public function clearForUser(string $usernameOrEmail): bool;

    /**
     * Delete expired login attempts
     *
     * @param int $olderThan Timestamp to delete attempts older than
     * @return int Number of deleted records
     */
    public function deleteExpired(int $olderThan): int;

    // TODO, this is a Draft id-1234
    public function findAll(
        ?string $username = null,
        ?string $ip = null,
        ?int $limit = null,
        ?int $offset = null
    ): array;
    public function countAll(?string $username = null, ?string $ip = null): int;
}
