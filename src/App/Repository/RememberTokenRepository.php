<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entities\RememberToken;
use Core\Database\ConnectionInterface;

class RememberTokenRepository implements RememberTokenRepositoryInterface
{
    /**
     * @var ConnectionInterface
     */
    private ConnectionInterface $connection;

    /**
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Create a new remember token
     */
    public function create(int $userId, string $selector, string $hashedValidator, string $expiresAt): bool
    {
        $sql = "INSERT INTO remember_tokens (user_id, selector, hashed_validator, expires_at, created_at, updated_at)
                VALUES (:user_id, :selector, :hashed_validator, :expires_at, NOW(), NOW())";

        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'selector' => $selector,
            'hashed_validator' => $hashedValidator,
            'expires_at' => $expiresAt
        ]);
    }

    /**
     * Find a token by selector
     */
    public function findBySelector(string $selector): ?RememberToken
    {
        $sql = "SELECT * FROM remember_tokens WHERE selector = :selector";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['selector' => $selector]);

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    /**
     * Delete a token by user ID
     */
    public function deleteByUserId(int $userId): bool
    {
        $sql = "DELETE FROM remember_tokens WHERE user_id = :user_id";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute(['user_id' => $userId]);
    }

    /**
     * Delete expired tokens
     */
    public function deleteExpired(): int
    {
        $sql = "DELETE FROM remember_tokens WHERE expires_at < NOW()";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * Delete a token by selector
     */
    public function deleteBySelector(string $selector): bool
    {
        $sql = "DELETE FROM remember_tokens WHERE selector = :selector";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute(['selector' => $selector]);
    }

    /**
     * Map database row to RememberToken entity
     */
    private function mapToEntity(array $data): RememberToken
    {
        $token = new RememberToken();
        $token->setId((int)$data['id']);
        $token->setUserId((int)$data['user_id']);
        $token->setSelector($data['selector']);
        $token->setHashedValidator($data['hashed_validator']);
        $token->setExpiresAt($data['expires_at']);
        $token->setCreatedAt($data['created_at']);
        $token->setUpdatedAt($data['updated_at']);

        return $token;
    }
}
