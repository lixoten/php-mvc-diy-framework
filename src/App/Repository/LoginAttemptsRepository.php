<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entities\LoginAttempt;
use Core\Database\ConnectionInterface;

class LoginAttemptsRepository implements LoginAttemptsRepositoryInterface
{
    private ConnectionInterface $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function record(array $data): bool
    {
        $sql = "INSERT INTO login_attempts (
                username_or_email,
                ip_address,
                attempted_at,
                user_agent,
                created_at,
                updated_at
            ) VALUES (
                :username_or_email,
                :ip_address,
                :attempted_at,
                :user_agent,
                NOW(),
                NOW()
            )";

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            'username_or_email' => $data['username_or_email'],
            'ip_address' => $data['ip_address'],
            'attempted_at' => date('Y-m-d H:i:s', $data['attempted_at']),
            'user_agent' => $data['user_agent'] ?? null
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function countRecentAttempts(string $usernameOrEmail, int $since): int
    {
        $sql = "SELECT COUNT(*) as count
                FROM login_attempts
                WHERE username_or_email = :username_or_email
                AND attempted_at >= :since";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            'username_or_email' => $usernameOrEmail,
            'since' => date('Y-m-d H:i:s', $since)
        ]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }

    /**
     * {@inheritdoc}
     */
    public function countRecentAttemptsFromIp(string $ipAddress, int $since): int
    {
        $sql = "SELECT COUNT(*) as count
                FROM login_attempts
                WHERE ip_address = :ip_address
                AND attempted_at >= :since";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            'ip_address' => $ipAddress,
            'since' => date('Y-m-d H:i:s', $since)
        ]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }

    /**
     * {@inheritdoc}
     */
    public function clearForUser(string $usernameOrEmail): bool
    {
        $sql = "DELETE FROM login_attempts WHERE username_or_email = :username_or_email";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute(['username_or_email' => $usernameOrEmail]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteExpired(int $olderThan): int
    {
        $sql = "DELETE FROM login_attempts WHERE attempted_at < :older_than";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['older_than' => date('Y-m-d H:i:s', $olderThan)]);

        return $stmt->rowCount();
    }

    /**
     * Find all attempts for a user
     *
     * @param string $usernameOrEmail Username or email to find attempts for
     * @param int|null $limit Maximum number of results
     * @param int|null $offset Pagination offset
     * @return array Array of LoginAttempt entities
     */
    public function findAttemptsByUser(
        string $usernameOrEmail,
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $sql = "SELECT * FROM login_attempts
                WHERE username_or_email = :username_or_email
                ORDER BY attempted_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) {
                $sql .= " OFFSET :offset";
            }
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':username_or_email', $usernameOrEmail);

        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            if ($offset !== null) {
                $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            }
        }

        $stmt->execute();

        $attempts = [];
        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $attempts[] = $this->mapToEntity($data);
        }

        return $attempts;
    }

    /**
     * Map database row to LoginAttempt entity
     */
    private function mapToEntity(array $data): LoginAttempt
    {
        $attempt = new LoginAttempt();
        $attempt->setId((int) $data['id']);
        $attempt->setUsernameOrEmail($data['username_or_email']);
        $attempt->setIpAddress($data['ip_address']);
        $attempt->setAttemptedAt($data['attempted_at']);
        $attempt->setUserAgent($data['user_agent']);
        $attempt->setCreatedAt($data['created_at']);
        $attempt->setUpdatedAt($data['updated_at']);

        return $attempt;
    }

    // TODO, this is a Draft id-1234
    public function findAll(
        ?string $username = null,
        ?string $ip = null,
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $sql = "SELECT * FROM login_attempts WHERE 1=1";
        $params = [];

        if ($username) {
            $sql .= " AND username_or_email LIKE :username";
            $params['username'] = "%$username%";
        }

        if ($ip) {
            $sql .= " AND ip_address LIKE :ip";
            $params['ip'] = "%$ip%";
        }

        $sql .= " ORDER BY attempted_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) {
                $sql .= " OFFSET :offset";
            }
        }

        $stmt = $this->connection->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            if ($offset !== null) {
                $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            }
        }

        $stmt->execute();

        $attempts = [];
        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $attempts[] = $this->mapToEntity($data);
        }

        return $attempts;
    }

    public function countAll(?string $username = null, ?string $ip = null): int
    {
        $sql = "SELECT COUNT(*) as count FROM login_attempts WHERE 1=1";
        $params = [];

        if ($username) {
            $sql .= " AND username_or_email LIKE :username";
            $params['username'] = "%$username%";
        }

        if ($ip) {
            $sql .= " AND ip_address LIKE :ip";
            $params['ip'] = "%$ip%";
        }

        $stmt = $this->connection->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }
}
