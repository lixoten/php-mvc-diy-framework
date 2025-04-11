<?php

declare(strict_types=1);

namespace App\Repository;

interface RateLimitRepositoryInterface
{
    /**
     * Record an attempt
     *
     * @param array $data Attempt data (identifier, action_type, ip_address, success, etc.)
     * @return bool True if recorded successfully
     */
    public function record(array $data): bool;

    /**
     * Count recent attempts for a specific identifier and action
     *
     * @param string $identifier User identifier (username, email, etc.)
     * @param string $actionType Type of action (login, password_reset, etc.)
     * @param int $since Timestamp to count attempts since
     * @return int Number of attempts
     */
    public function countRecentAttempts(string $identifier, string $actionType, int $since): int;

    /**
     * Count recent attempts from a specific IP address for an action
     *
     * @param string $ipAddress The IP address
     * @param string $actionType Type of action
     * @param int $since Timestamp to count attempts since
     * @return int Number of attempts
     */
    public function countRecentAttemptsFromIp(string $ipAddress, string $actionType, int $since): int;

    /**
     * Clear attempts for a specific identifier and action
     *
     * @param string $identifier User identifier
     * @param string $actionType Type of action
     * @return bool True if cleared successfully
     */
    public function clearForIdentifier(string $identifier, string $actionType): bool;

    /**
     * Delete expired attempts
     *
     * @param int $olderThan Delete attempts older than this timestamp
     * @return int Number of deleted records
     */
    public function deleteExpired(int $olderThan): int;

    /**
     * Update the status of the last attempt for a specific identifier and action
     *
     * @param string $identifier User identifier
     * @param string $actionType Type of action
     * @param bool $success Whether the attempt was successful
     * @return bool True if updated successfully
     */
    public function updateLastAttemptStatus(string $identifier, string $actionType, bool $success): bool;
}
