<?php

declare(strict_types=1);

namespace App\Repository;

use Core\Database\ConnectionInterface;

class RateLimitRepository implements RateLimitRepositoryInterface
{
    private ConnectionInterface $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function recordAttempt(array $data): bool
    {
        $sql = "INSERT INTO rate_limit_attempts (
                identifier,
                action_type,
                ip_address,
                success,
                attempted_at,
                user_agent,
                created_at,
                updated_at
            ) VALUES (
                :identifier,
                :action_type,
                :ip_address,
                :success,
                :attempted_at,
                :user_agent,
                NOW(),
                NOW()
            )";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':identifier', $data['identifier']);
        $stmt->bindValue(':action_type', $data['action_type']);
        $stmt->bindValue(':ip_address', $data['ip_address']);
        $stmt->bindValue(':success', $data['success'] ?? false ? 1 : 0, \PDO::PARAM_INT);
        $stmt->bindValue(':attempted_at', $data['attempted_at'] ?? date('Y-m-d H:i:s'));
        $stmt->bindValue(':user_agent', $data['user_agent'] ?? null);

        $result = $stmt->execute();

        return $result !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function countRecentAttempts(string $identifier, string $actionType, int $since): int
    {
        $sql = "SELECT COUNT(*) AS count
                FROM rate_limit_attempts
                WHERE identifier = :identifier
                AND action_type = :action_type
                AND attempted_at >= :since";
                // AND success = 0";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':identifier', $identifier);
        $stmt->bindValue(':action_type', $actionType);
        $stmt->bindValue(':since', date('Y-m-d H:i:s', $since));
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }

    /**
     * {@inheritdoc}
     */
    public function countRecentAttemptsFromIp(string $ipAddress, string $actionType, int $since): int
    {
        if ($actionType === 'registration') {
            // For registration, count ALL attempts (success=0 OR success=1)
            $sql = "SELECT COUNT(*) AS count
                    FROM rate_limit_attempts
                    WHERE ip_address = :ip_address
                    AND action_type = :action_type
                    AND attempted_at >= :since";
        } else {
            // For other action types, only count failures
            $sql = "SELECT COUNT(*) AS count
                    FROM rate_limit_attempts
                    WHERE ip_address = :ip_address
                    AND action_type = :action_type
                    AND attempted_at >= :since";
                    // AND success = 0";
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':ip_address', $ipAddress);
        $stmt->bindValue(':action_type', $actionType);
        $stmt->bindValue(':since', date('Y-m-d H:i:s', $since));
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }

    /**
     * {@inheritdoc}
     */
    public function clearForIdentifier(string $identifier, string $actionType): bool
    {
        $sql = "DELETE FROM rate_limit_attempts
                WHERE identifier = :identifier
                AND action_type = :action_type";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':identifier', $identifier);
        $stmt->bindValue(':action_type', $actionType);
        $result = $stmt->execute();

        return $result !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteExpired(int $olderThan, ?string $actionType = null): int
    {
        $cutoffDate = date('Y-m-d H:i:s', $olderThan);

        if ($actionType === null) {
            // Delete all expired records
            $sql = "DELETE FROM rate_limit_attempts WHERE attempted_at < :cutoff";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':cutoff', $cutoffDate);
        } else {
            // Delete only records for specific action type
            $sql = "DELETE FROM rate_limit_attempts WHERE action_type = :action_type AND attempted_at < :cutoff";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':action_type', $actionType);
            $stmt->bindValue(':cutoff', $cutoffDate);
        }

        $stmt->execute();
        return $stmt->rowCount();
    }


    /**
     * {@inheritdoc}
     */
    public function updateLastAttemptStatus(string $identifier, string $actionType, bool $success): bool
    {
        $sql = "UPDATE rate_limit_attempts
                SET success = :success, updated_at = NOW()
                WHERE identifier = :identifier
                AND action_type = :action_type
                ORDER BY attempted_at DESC
                LIMIT 1";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':identifier', $identifier);
        $stmt->bindValue(':action_type', $actionType);
        $stmt->bindValue(':success', $success ? 1 : 0, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}
