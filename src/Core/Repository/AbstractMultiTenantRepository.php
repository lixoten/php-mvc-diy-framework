<?php

declare(strict_types=1);

namespace Core\Repository;

use App\Enums\UserStatus;
use App\Features\User\User;

/**
 * Abstract repository for entities with user_id and store_id columns.
 *
 * This class provides common query methods for multi-tenant entities.
 * Only repositories that manage entities belonging to both users and stores
 * should extend this class.
 */
abstract class AbstractMultiTenantRepository extends AbstractRepository implements BaseRepositoryInterface
{
    // USER //////////////////////////////////////////////////////
    /** {@inheritdoc} */
    public function findByUserIdWithFields(
        int $userId,
        array $fields,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        if (empty($fields)) {
            $fields = ['*'];
        }

        return $this->findByCriteriaWithFields(
            ['user_id' => $userId],
            $fields,
            $orderBy,
            $limit,
            $offset
        );
    }


    /** {@inheritdoc} */
    public function countByUserId(int $userId): int
    {
        return $this->countBy(['user_id' => $userId]);
    }

    // STORE /////////////////////////////////////////////////////

    /** {@inheritdoc} */
    public function findByStoreIdWithFields(
        int $storeId,
        array $fields,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        if (empty($fields)) {
            $fields = ['*'];
        }

        return $this->findByCriteriaWithFields(
            ['store_id' => $storeId],
            $fields,
            $orderBy,
            $limit,
            $offset
        );
    }


    /** {@inheritdoc} */
    public function countByStoreId(int $storeId): int
    {
        return $this->countBy(['store_id' => $storeId]);
    }

    /////////////////////////////////////////

    /** {@inheritdoc} */
    public function findAllWithFields(
        array $fields,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        if (empty($fields)) {
            $fields = ['*'];
        }

        return $this->findByCriteriaWithFields(
            [], // No specific criteria, so it finds all
            $fields,
            $orderBy,
            $limit,
            $offset
        );
    }




    /** {@inheritdoc} */
    public function countAll(array $criteria = []): int
    {
        return $this->countBy(criteria: $criteria);
    }






#
#    /**
#     * Save draft (auto-save feature).
#     *
#     * @param array<string, mixed> $data Draft data to save
#     * @return bool True if update was successful
#     */
#    public function saveDraft(array $data): bool
#    {
#        $fieldsToUpdate = [
#            'title' => $data['title'] ?? '',
#            'content' => $data['content'] ?? '',
#            'generic_text' => $data['generic_text'] ?? '',
#        ];
#
#        return $this->updateFields((int)$data['id'], $fieldsToUpdate);
#    }
#
#









    // this is inside 'interface StoreRepositoryInterface'
    //??????????????????????????????????????????????
    /** {@inheritdoc} */
    public function findByUserId(
        int $userId,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        return $this->findBy(
            ['user_id' => $userId],
            $orderBy,
            $limit,
            $offset
        );
    }







#    /**
#     * Find entities by store ID with full entity mapping.
#     * Child repositories must implement findBy() to use this method.
#     *
#     * @param int $storeId
#     * @param array<string, string> $orderBy
#     * @param int|null $limit
#     * @param int|null $offset
#     * @return array<object>
#     */
#    public function findByStoreId(  // xxx
#        int $storeId,
#        array $orderBy = [],
#        ?int $limit = null,
#        ?int $offset = null
#    ): array {
#        return $this->findBy(
#            ['store_id' => $storeId],
#            $orderBy,
#            $limit,
#            $offset
#        );
#    }


#
#    /**
#     * Find entities by both user ID and store ID with full entity mapping.
#     * Child repositories must implement findBy() to use this method.
#     *
#     * @param int $userId
#     * @param int $storeId
#     * @param array<string, string> $orderBy
#     * @param int|null $limit
#     * @param int|null $offset
#     * @return array<object>
#     */
#    public function findByUserAndStore(
#        int $userId,
#        int $storeId,
#        array $orderBy = [],
#        ?int $limit = null,
#        ?int $offset = null
#    ): array {
#        return $this->findBy(
#            ['user_id' => $userId, 'store_id' => $storeId],
#            $orderBy,
#            $limit,
#            $offset
#        );
#    }
#
#    /**
#     * Find entities by both user ID and store ID with specified fields (raw data).
#     *
#     * @param int $userId
#     * @param int $storeId
#     * @param array<string> $fields
#     * @param array<string, string> $orderBy
#     * @param int|null $limit
#     * @param int|null $offset
#     * @return array<array<string, mixed>>
#     */
#    public function findByUserAndStoreWithFields(
#        int $userId,
#        int $storeId,
#        array $fields,
#        array $orderBy = [],
#        ?int $limit = null,
#        ?int $offset = null
#    ): array {
#        if (empty($fields)) {
#            $fields = ['*'];
#        }
#
#        return $this->findByCriteriaWithFields(
#            ['user_id' => $userId, 'store_id' => $storeId],
#            $fields,
#            $orderBy,
#            $limit,
#            $offset
#        );
#    }

#    /**
#     * Count entities by both user ID and store ID.
#     *
#     * @param int $userId
#     * @param int $storeId
#     * @return int
#     */
#    public function countByUserAndStore(int $userId, int $storeId): int
#    {
#        return $this->countBy(['user_id' => $userId, 'store_id' => $storeId]);
#    }
#
#    // /**
#    //  * Abstract method that child repositories must implement.
#    //  * This method should include JOIN logic and entity mapping.
#    //  *
#    //  * @param array<string, mixed> $criteria
#    //  * @param array<string, string> $orderBy
#    //  * @param int|null $limit
#    //  * @param int|null $offset
#    //  * @return array<object>
#    //  */
#    // abstract public function findBy(
#    //     array $criteria = [],
#    //     array $orderBy = [],
#    //     ?int $limit = null,
#    //     ?int $offset = null
#    // ): array;
#
#    // /**
#    //  * Find Testy records based on criteria with full entity mapping.
#    //  *
#    //  * @param array<string, mixed> $criteria Filtering criteria (field => value pairs)
#    //  * @param array<string, string> $orderBy Sorting criteria (field => direction pairs)
#    //  * @param int|null $limit Maximum number of records to return
#    //  * @param int|null $offset Number of records to skip
#    //  * @return array<Testy> Array of Testy entities matching criteria
#    //  */
#    // public function findBy(
#    //     array $criteria = [],
#    //     array $orderBy = [],
#    //     ?int $limit = null,
#    //     ?int $offset = null
#    // ): array {
#    //     return [];
#    // }
#
#
#
#
#
#
#//    /**
#//      * Find all entities, selecting only specified columns.
#//      *
#//      * @param array<string> $fields The fields to select.
#//      * @param array<string, string> $orderBy Optional sorting criteria.
#//      * @param int|null $limit Maximum number of results.
#//      * @param int|null $offset Result offset for pagination.
#//      * @return array<array<string, mixed>> An array of associative arrays representing the records.
#//      */
#//     public function findAllWithFields(
#//         array $fields,
#//         array $orderBy = [],
#//         ?int $limit = null,
#//         ?int $offset = null
#//     ): array {
#//         if (empty($fields)) {
#//             $fields = ['*'];
#//         }
#
#//         return $this->findByCriteriaWithFields(
#//             [], // No specific criteria, so it finds all
#//             $fields,
#//             $orderBy,
#//             $limit,
#//             $offset
#//         );
#//     }
#
#
#  // /**
#    //  * Count records by criteria.
#    //  *
#    //  * @param array<string, mixed> $criteria
#    //  * @return int
#    //  */
#    // public function countBy(array $criteria = []): int
#    // {
#    //     $sql = "SELECT COUNT(*) as count FROM {$this->tableName} {$this->tableAlias}";
#    //     $params = [];
#
#    //     if (!empty($criteria)) {
#    //         $whereClauses = [];
#    //         foreach ($criteria as $field => $value) {
#    //             $placeholder = ':' . str_replace('.', '_', $field);
#    //             $whereClauses[] = "{$this->tableAlias}.{$field} = {$placeholder}";
#    //             $params[$placeholder] = $value;
#    //         }
#    //         $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
#    //     }
#
#    //     $stmt = $this->connection->prepare($sql);
#
#    //     foreach ($params as $param => $value) {
#    //         $stmt->bindValue($param, $value, $this->getPdoType($value));
#    //     }
#
#    //     $stmt->execute();
#    //     $result = $stmt->fetch(\PDO::FETCH_ASSOC);
#
#    //     return (int) ($result['count'] ?? 0);
#    // }
#
#
#
#
#    // /**
#    //  * Find a record by ID, selecting only specified fields.
#    //  *
#    //  * @param int $id
#    //  * @param array<string> $fields
#    //  * @return array<string, mixed>|null
#    //  */
#    // public function findByIdWithFields(int $id, array $fields): ?array
#    // {
#    //     if (empty($fields)) {
#    //         $fields = ['*'];
#    //     }
#
#    //     $this->validateFieldNames($fields);
#    //     $columns = implode(', ', $fields);
#
#    //     $sql = "SELECT {$columns} FROM {$this->tableName} WHERE {$this->primaryKey} = :id";
#    //     $stmt = $this->connection->prepare($sql);
#    //     $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
#    //     $stmt->execute();
#
#    //     $result = $stmt->fetch(\PDO::FETCH_ASSOC);
#
#    //     return $result ?: null;
#    // }
#
#
#
#    // /**
#    //  * Update only the specified fields for a record.
#    //  *
#    //  * @param int $id
#    //  * @param array<string, mixed> $fieldsToUpdate
#    //  * @return bool
#    //  */
#    // public function updateFields(int $id, array $fieldsToUpdate): bool
#    // {
#    //     if (empty($fieldsToUpdate)) {
#    //         return false;
#    //     }
#
#    //     $setClauses = [];
#    //     $params = [':id' => $id];
#
#    //     foreach ($fieldsToUpdate as $field => $value) {
#    //         $setClauses[] = "{$field} = :{$field}";
#    //         $params[":{$field}"] = $value;
#    //     }
#
#    //     $setClauses[] = "updated_at = NOW()";
#    //     $sql = "UPDATE {$this->tableName} SET " . implode(', ', $setClauses)
#    //          . " WHERE {$this->primaryKey} = :id";
#
#    //     $stmt = $this->connection->prepare($sql);
#
#    //     return $stmt->execute($params);
#    // }
#
##
##
##    /**
##     * Delete a user
##     *
##     * @return bool True if deletion was successful
##     */
##    public function delete(int $id): bool
##    {
##        $sql = "DELETE FROM user WHERE id = :id";
##        $stmt = $this->connection->prepare($sql);
##        $stmt->bindValue(':id', $id);
##        $stmt->execute();
##
##        return $stmt->rowCount() > 0;
##    }
##
##
##
##
##
#
#    // /**
#    //  * Find all user
#    //  *
#    //  * @param array $criteria Optional filtering criteria
#    //  * @param array $orderBy Optional sorting criteria
#    //  * @param int|null $limit Maximum number of results
#    //  * @param int|null $offset Result offset for pagination
#    //  * @return User[] Array of User entities
#    //  */
#    // public function findAll(
#    //     array $criteria = [],
#    //     array $orderBy = [],
#    //     ?int $limit = null,
#    //     ?int $offset = null
#    // ): array {
#    //     $sql = "SELECT * FROM user";
#    //     $params = [];
#
#    //     // Add WHERE clauses for criteria
#    //     if (!empty($criteria)) {
#    //         $whereClauses = [];
#    //         foreach ($criteria as $field => $value) {
#    //             $whereClauses[] = "$field = :$field";
#    //             $params[":$field"] = $value;
#    //         }
#    //         $sql .= " WHERE " . implode(' AND ', $whereClauses);
#    //     }
#
#    //     // Add ORDER BY clause
#    //     if (!empty($orderBy)) {
#    //         $orderClauses = [];
#    //         foreach ($orderBy as $field => $direction) {
#    //             $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
#    //             $orderClauses[] = "$field $direction";
#    //         }
#    //         $sql .= " ORDER BY " . implode(', ', $orderClauses);
#    //     }
#
#    //     // Add LIMIT and OFFSET
#    //     if ($limit !== null) {
#    //         $sql .= " LIMIT :limit";
#    //         $params[':limit'] = $limit;
#
#    //         if ($offset !== null) {
#    //             $sql .= " OFFSET :offset";
#    //             $params[':offset'] = $offset;
#    //         }
#    //     }
#
#    //     $stmt = $this->connection->prepare($sql);
#
#    //     // Bind parameters
#    //     foreach ($params as $param => $value) {
#    //         $stmt->bindValue($param, $value);
#    //     }
#
#    //     $stmt->execute();
#
#    //     $users = [];
#    //     while ($userData = $stmt->fetch(\PDO::FETCH_ASSOC)) {
#    //         $users[] = $this->mapToEntity($userData);
#    //     }
#
#    //     return $users;
#    // }
#
##
##
##
##
##    /**
##     * Find a user by username
##     */
##    public function findByUsername(string $username): ?User
##    {
##        $sql = "SELECT * FROM user WHERE username = :username";
##        $stmt = $this->connection->prepare($sql);
##        $stmt->bindValue(':username', $username);
##        $stmt->execute();
##
##        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);
##
##        if (!$userData) {
##            return null;
##        }
##
##        return $this->mapToEntity($userData);
##    }
##
##
##
##
##
##    /**
##     * Find user by status
##     *
##     * @param UserStatus $status The status to filter by
##     * @param array $orderBy Optional sorting criteria
##     * @param int|null $limit Maximum number of results
##     * @param int|null $offset Result offset for pagination
##     * @return User[] Array of User entities
##     */
##    public function findByStatus(
##        UserStatus $status,
##        array $orderBy = [],
##        ?int $limit = null,
##        ?int $offset = null
##    ): array {
##        return $this->findAll(
##            ['status' => $status->value],
##            $orderBy,
##            $limit,
##            $offset
##        );
##    }
##
##
##
##
##
##    /**
##     * Find user by role
##     *
##     * @param string $role The role to search for
##     * @param array $orderBy Optional sorting criteria
##     * @param int|null $limit Maximum number of results
##     * @param int|null $offset Result offset for pagination
##     * @return User[] Array of User entities
##     */
##    public function findByRole(
##        string $role,
##        array $orderBy = [],
##        ?int $limit = null,
##        ?int $offset = null
##    ): array {
##        // Since roles are stored as JSON, we need a LIKE query
##        $sql = "SELECT * FROM user WHERE roles LIKE :role_pattern";
##        $params = [':role_pattern' => '%' . $role . '%'];
##
##        // Add ORDER BY clause
##        if (!empty($orderBy)) {
##            $orderClauses = [];
##            foreach ($orderBy as $field => $direction) {
##                $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
##                $orderClauses[] = "$field $direction";
##            }
##            $sql .= " ORDER BY " . implode(', ', $orderClauses);
##        }
##
##        // Add LIMIT and OFFSET
##        if ($limit !== null) {
##            $sql .= " LIMIT :limit";
##            $params[':limit'] = $limit;
##
##            if ($offset !== null) {
##                $sql .= " OFFSET :offset";
##                $params[':offset'] = $offset;
##            }
##        }
##
##        $stmt = $this->connection->prepare($sql);
##
##        // Bind parameters
##        foreach ($params as $param => $value) {
##            $stmt->bindValue($param, $value);
##        }
##
##        $stmt->execute();
##
##        $users = [];
##        while ($userData = $stmt->fetch(\PDO::FETCH_ASSOC)) {
##            $user = $this->mapToEntity($userData);
##
##            // Double-check that user actually has this role (since LIKE query can be imprecise)
##            if ($user->hasRole($role)) {
##                $users[] = $user;
##            }
##        }
##
##        return $users;
##    }
##
##
##
##
##
##    /**
##     * Count total users
##     *
##     * @param array $criteria Optional filtering criteria
##     * @return int Total number of users matching criteria
##     */
##    public function countAll(array $criteria = []): int
##    {
##        $sql = "SELECT COUNT(*) as count FROM user";
##        $params = [];
##
##        // Add WHERE clauses for criteria
##        if (!empty($criteria)) {
##            $whereClauses = [];
##            foreach ($criteria as $field => $value) {
##                $whereClauses[] = "$field = :$field";
##                $params[":$field"] = $value;
##            }
##            $sql .= " WHERE " . implode(' AND ', $whereClauses);
##        }
##
##        $stmt = $this->connection->prepare($sql);
##
##        // Bind parameters
##        foreach ($params as $param => $value) {
##            $stmt->bindValue($param, $value);
##        }
##
##        $stmt->execute();
##        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
##
##        return (int) $result['count'];
##    }
##
##
##
##
##
##
##    /**
##     * Check if username exists
##     *
##     * @param string $username The username to check
##     * @param int|null $excludeUserId Optional user ID to exclude from check
##     * @return bool True if username exists
##     */
##    public function usernameExists(string $username, ?int $excludeUserId = null): bool
##    {
##        $sql = "SELECT COUNT(*) as count FROM user WHERE username = :username";
##        $params = [':username' => $username];
##
##        if ($excludeUserId !== null) {
##            $sql .= " AND id != :exclude_id";
##            $params[':exclude_id'] = $excludeUserId;
##        }
##
##        $stmt = $this->connection->prepare($sql);
##
##        // Bind parameters
##        foreach ($params as $param => $value) {
##            $stmt->bindValue($param, $value);
##        }
##
##        $stmt->execute();
##        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
##
##        return (int) $result['count'] > 0;
##    }
##
##
##
##
##
##    /**
##     * Check if email exists
##     *
##     * @param string $email The email to check
##     * @param int|null $excludeUserId Optional user ID to exclude from check
##     * @return bool True if email exists
##     */
##    public function emailExists(string $email, ?int $excludeUserId = null): bool
##    {
##        $sql = "SELECT COUNT(*) as count FROM user WHERE email = :email";
##        $params = [':email' => $email];
##
##        if ($excludeUserId !== null) {
##            $sql .= " AND id != :exclude_id";
##            $params[':exclude_id'] = $excludeUserId;
##        }
##
##        $stmt = $this->connection->prepare($sql);
##
##        // Bind parameters
##        foreach ($params as $param => $value) {
##            $stmt->bindValue($param, $value);
##        }
##
##        $stmt->execute();
##        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
##
##        return (int) $result['count'] > 0;
##    }
##
##
##
##
##    /**
##     * Find a user by email
##     */
##    public function findByEmail(string $email): ?User
##    {
##        $sql = "SELECT * FROM user WHERE email = :email";
##        $stmt = $this->connection->prepare($sql);
##        $stmt->bindValue(':email', $email);
##        $stmt->execute();
##
##        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);
##
##        if (!$userData) {
##            return null;
##        }
##
##        return $this->mapToEntity($userData);
##    }
##
##
##
##
##
##    /**
##     * Find a user by activation token
##     */
##    public function findByActivationToken(string $token): ?User
##    {
##        $sql = "SELECT * FROM user WHERE activation_token = :token";
##        $stmt = $this->connection->prepare($sql);
##        $stmt->bindValue(':token', $token);
##        $stmt->execute();
##
##        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);
##
##        if (!$userData) {
##            return null;
##        }
##
##        return $this->mapToEntity($userData);
##    }
##
##
##
##
##
##    /**
##     * Find a user by reset token
##     */
##    public function findByResetToken(string $token): ?User
##    {
##        $sql = "SELECT * FROM user WHERE reset_token = :token";
##        $stmt = $this->connection->prepare($sql);
##        $stmt->bindValue(':token', $token);
##        $stmt->execute();
##
##        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);
##
##        if (!$userData) {
##            return null;
##        }
##
##        return $this->mapToEntity($userData);
##    }
##
##
##
#
#
#
#  /**
#     * Find User records based on criteria with full entity mapping.
#     *
#     * @param array<string, mixed> $criteria Filtering criteria (field => value pairs)
#     * @param array<string, string> $orderBy Sorting criteria (field => direction pairs)
#     * @param int|null $limit Maximum number of records to return
#     * @param int|null $offset Number of records to skip
#     * @return array<User> Array of User entities matching criteria
#     */
#    public function findByXxx2(
#        array $criteria = [],
#        array $orderBy = [],
#        ?int $limit = null,
#        ?int $offset = null
#    ): array {
#        $sql = "SELECT t.*, u.username
#                FROM user t
#                LEFT JOIN user u ON t.user_id = u.id";
#
#        $params = [];
#
#        // Build WHERE clause
#        if (!empty($criteria)) {
#            $whereClauses = [];
#            foreach ($criteria as $field => $value) {
#                $whereClauses[] = "u.{$field} = :{$field}";
#                $params[":{$field}"] = $value;
#            }
#            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
#        }
#
#        // Build ORDER BY clause
#        if (!empty($orderBy)) {
#            $orderClauses = [];
#            foreach ($orderBy as $field => $direction) {
#                $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
#                $orderClauses[] = "u.{$field} {$dir}";
#            }
#            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
#        } else {
#            $sql .= ' ORDER BY u.created_at DESC';
#        }
#
#        // Add LIMIT and OFFSET
#        if ($limit !== null) {
#            $sql .= ' LIMIT :limit';
#            if ($offset !== null) {
#                $sql .= ' OFFSET :offset';
#            }
#        }
#
#        $stmt = $this->connection->prepare($sql);
#
#        // Bind parameters
#        foreach ($params as $param => $value) {
#            $stmt->bindValue($param, $value, $this->getPdoType($value));
#        }
#
#        if ($limit !== null) {
#            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
#        }
#        if ($offset !== null) {
#            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
#        }
#
#        $stmt->execute();
#
#        $results = [];
#        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
#            $results[] = $this->mapToEntity($row);
#        }
#
#        return $results;
#    }
#
#
#
#
#
#
#
#
#
#
#
}
