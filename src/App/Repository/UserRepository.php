<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entities\User;
use App\Enums\UserStatus;
use Core\Database\ConnectionInterface;

class UserRepository implements UserRepositoryInterface
{
    private ConnectionInterface $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Find a user by ID
     */
    public function findById(int $userId): ?User
    {
        $sql = "SELECT * FROM users WHERE user_id = :user_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();

        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        return $this->mapToEntity($userData);
    }

    /**
     * Find a user by username
     */
    public function findByUsername(string $username): ?User
    {
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':username', $username);
        $stmt->execute();

        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        return $this->mapToEntity($userData);
    }

    /**
     * Find a user by email
     */
    public function findByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        return $this->mapToEntity($userData);
    }

    /**
     * Find a user by activation token
     */
    public function findByActivationToken(string $token): ?User
    {
        $sql = "SELECT * FROM users WHERE activation_token = :token";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':token', $token);
        $stmt->execute();

        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        return $this->mapToEntity($userData);
    }

    /**
     * Find a user by reset token
     */
    public function findByResetToken(string $token): ?User
    {
        $sql = "SELECT * FROM users WHERE reset_token = :token";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':token', $token);
        $stmt->execute();

        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        return $this->mapToEntity($userData);
    }

    /**
     * Create a new user
     *
     * @return User The created user with ID
     */
    public function create(User $user): User
    {
        $sql = "INSERT INTO users (
                username,
                email,
                password_hash,
                roles,
                status,
                activation_token,
                reset_token,
                reset_token_expiry,
                created_at,
                updated_at
            ) VALUES (
                :username,
                :email,
                :password_hash,
                :roles,
                :status,
                :activation_token,
                :reset_token,
                :reset_token_expiry,
                NOW(),
                NOW()
            )";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':username', $user->getUsername());
        $stmt->bindValue(':email', $user->getEmail());
        $stmt->bindValue(':password_hash', $user->getPasswordHash());
        $stmt->bindValue(':roles', json_encode($user->getRoles()));
        $stmt->bindValue(':status', $user->getStatus()->value);
        $stmt->bindValue(':activation_token', $user->getActivationToken());
        $stmt->bindValue(':reset_token', $user->getResetToken());
        $stmt->bindValue(':reset_token_expiry', $user->getResetTokenExpiry());

        $stmt->execute();

        // Get the ID of the newly created user
        $userId = (int) $this->connection->lastInsertId();
        $user->setUserId($userId);

        // Update with created_at and updated_at from db
        return $this->findById($userId);
    }

    /**
     * Update an existing user
     *
     * @return bool True if update was successful
     */
    public function update(User $user): bool
    {
        $sql = "UPDATE users SET
                username = :username,
                email = :email,
                password_hash = :password_hash,
                roles = :roles,
                status = :status,
                activation_token = :activation_token,
                reset_token = :reset_token,
                reset_token_expiry = :reset_token_expiry,
                updated_at = NOW()
            WHERE user_id = :user_id";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':user_id', $user->getUserId());
        $stmt->bindValue(':username', $user->getUsername());
        $stmt->bindValue(':email', $user->getEmail());
        $stmt->bindValue(':password_hash', $user->getPasswordHash());
        $stmt->bindValue(':roles', json_encode($user->getRoles()));
        $stmt->bindValue(':status', $user->getStatus()->value);
        $stmt->bindValue(':activation_token', $user->getActivationToken());
        $stmt->bindValue(':reset_token', $user->getResetToken());
        $stmt->bindValue(':reset_token_expiry', $user->getResetTokenExpiry());

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Delete a user
     *
     * @return bool True if deletion was successful
     */
    public function delete(int $userId): bool
    {
        $sql = "DELETE FROM users WHERE user_id = :user_id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

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
    ): array {
        $sql = "SELECT * FROM users";
        $params = [];

        // Add WHERE clauses for criteria
        if (!empty($criteria)) {
            $whereClauses = [];
            foreach ($criteria as $field => $value) {
                $whereClauses[] = "$field = :$field";
                $params[":$field"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        // Add ORDER BY clause
        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $field => $direction) {
                $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $orderClauses[] = "$field $direction";
            }
            $sql .= " ORDER BY " . implode(', ', $orderClauses);
        }

        // Add LIMIT and OFFSET
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            $params[':limit'] = $limit;

            if ($offset !== null) {
                $sql .= " OFFSET :offset";
                $params[':offset'] = $offset;
            }
        }

        $stmt = $this->connection->prepare($sql);

        // Bind parameters
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        $stmt->execute();

        $users = [];
        while ($userData = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $users[] = $this->mapToEntity($userData);
        }

        return $users;
    }

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
    ): array {
        return $this->findAll(
            ['status' => $status->value],
            $orderBy,
            $limit,
            $offset
        );
    }

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
    ): array {
        // Since roles are stored as JSON, we need a LIKE query
        $sql = "SELECT * FROM users WHERE roles LIKE :role_pattern";
        $params = [':role_pattern' => '%' . $role . '%'];

        // Add ORDER BY clause
        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $field => $direction) {
                $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $orderClauses[] = "$field $direction";
            }
            $sql .= " ORDER BY " . implode(', ', $orderClauses);
        }

        // Add LIMIT and OFFSET
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            $params[':limit'] = $limit;

            if ($offset !== null) {
                $sql .= " OFFSET :offset";
                $params[':offset'] = $offset;
            }
        }

        $stmt = $this->connection->prepare($sql);

        // Bind parameters
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        $stmt->execute();

        $users = [];
        while ($userData = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $user = $this->mapToEntity($userData);

            // Double-check that user actually has this role (since LIKE query can be imprecise)
            if ($user->hasRole($role)) {
                $users[] = $user;
            }
        }

        return $users;
    }

    /**
     * Count total users
     *
     * @param array $criteria Optional filtering criteria
     * @return int Total number of users matching criteria
     */
    public function countAll(array $criteria = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM users";
        $params = [];

        // Add WHERE clauses for criteria
        if (!empty($criteria)) {
            $whereClauses = [];
            foreach ($criteria as $field => $value) {
                $whereClauses[] = "$field = :$field";
                $params[":$field"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $stmt = $this->connection->prepare($sql);

        // Bind parameters
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int) $result['count'];
    }

    /**
     * Check if username exists
     *
     * @param string $username The username to check
     * @param int|null $excludeUserId Optional user ID to exclude from check
     * @return bool True if username exists
     */
    public function usernameExists(string $username, ?int $excludeUserId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = :username";
        $params = [':username' => $username];

        if ($excludeUserId !== null) {
            $sql .= " AND user_id != :exclude_id";
            $params[':exclude_id'] = $excludeUserId;
        }

        $stmt = $this->connection->prepare($sql);

        // Bind parameters
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int) $result['count'] > 0;
    }

    /**
     * Check if email exists
     *
     * @param string $email The email to check
     * @param int|null $excludeUserId Optional user ID to exclude from check
     * @return bool True if email exists
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = :email";
        $params = [':email' => $email];

        if ($excludeUserId !== null) {
            $sql .= " AND user_id != :exclude_id";
            $params[':exclude_id'] = $excludeUserId;
        }

        $stmt = $this->connection->prepare($sql);

        // Bind parameters
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }

        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int) $result['count'] > 0;
    }

    /**
     * Map database row to User entity
     *
     * @param array $userData User data from database
     * @return User Populated user entity
     */
    private function mapToEntity(array $userData): User
    {
        $user = new User();

        $user->setUserId((int) $userData['user_id']);
        $user->setUsername($userData['username']);
        $user->setEmail($userData['email']);
        $user->setPasswordHash($userData['password_hash']);

        // Parse JSON roles array
        $roles = json_decode($userData['roles'] ?? '[]', true);
        $user->setRoles(is_array($roles) ? $roles : []);

        // Map status from character to enum
        $user->setStatus(UserStatus::from($userData['status']));

        // Set nullable fields
        $user->setActivationToken($userData['activation_token']);
        $user->setResetToken($userData['reset_token']);
        $user->setResetTokenExpiry($userData['reset_token_expiry']);
        $user->setCreatedAt($userData['created_at']);
        $user->setUpdatedAt($userData['updated_at']);

        return $user;
    }
}
