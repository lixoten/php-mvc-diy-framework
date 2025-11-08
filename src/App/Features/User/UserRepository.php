<?php

declare(strict_types=1);

namespace App\Features\User;

// use App\Entities\User;
use App\Enums\UserStatus;
use Core\Database\ConnectionInterface;
use Core\Repository\AbstractMultiTenantRepository;
use Core\Repository\BaseRepositoryInterface;

/**
 * Generated File - Date: 20251104_174033
 * Repository implementation for User entity.
 *
 * Handles all database operations for User records including CRUD operations,
 * draft saving, and entity mapping with JOIN support for related user data.
 *
 * @implements UserRepositoryInterface
 * @implements BaseRepositoryInterface
 */
class UserRepository extends AbstractMultiTenantRepository implements UserRepositoryInterface
{
    // Notes-: this 3 are used by abstract class
    protected string $tableName = 'user';
    protected string $tableAlias = 'u';
    protected string $primaryKey = 'id';

    // /**
    //  * Fields that should be JSON-encoded when stored
    //  *
    //  * @var array<string>
    //  */
    // protected array $jsonFields = ['roles', 'my_colors'];


    /**
     * Initialize repository with database connection.
     *
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        parent::__construct($connection);
    }

    /**
     * Find a User by ID with full entity mapping.
     *
     * @param int $id The User ID
     * @return User|null The User entity or null if not found
     */
    public function findById(int $id): ?User
    {
        $sql = "SELECT *
                FROM user
                WHERE id = :id";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }


    /**
     * Create a new User record.
     *
     * @param User $user The User record to create
     * @return User The created User record with ID populated
     */
    public function create(User $user): User
    {


        $data = [
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'password_hash' => $user->getPasswordHash(),
            'roles' => json_encode($user->getRoles()),
            'status' => $user->getStatus()->value,
            'activation_token' => $user->getActivationToken(),
            'reset_token' => $user->getResetToken(),
            'reset_token_expiry' => $user->getResetTokenExpiry(),
            'created_at' => $user->getCreatedAt(),
            'updated_at' => $user->getUpdatedAt(),
        ];

    //    // JSON-encode array values for configured JSON fields
    //     foreach ($data as $field => $value) {
    //         if (is_array($value) && in_array($field, $this->jsonFields, true)) {
    //             $data[$field] = json_encode($value, JSON_THROW_ON_ERROR);
    //         }
    //     }

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = "INSERT INTO user ("
             . implode(', ', $columns)
             . ") VALUES ("
             . implode(', ', $placeholders)
             . ")";

        $stmt = $this->connection->prepare($sql);

        foreach ($data as $col => $value) {
            $stmt->bindValue(":{$col}", $value, $this->getPdoType($value));
            // if ($value === 'NOW()') {
            //     $stmt->bindValue(":{$col}", null);
            // } else {
            //     $stmt->bindValue(":{$col}", $value, $this->getPdoType($value));
            // }
        }

        $stmt->execute();

        $id = (int) $this->connection->lastInsertId();
        $user->setId($id);

        return $this->findById($id);
    }

    /**
     * Update an existing User record.
     *
     * @param User $user The User record to update
     * @return bool True if update was successful
     */
    public function update(User $user): bool
    {
        $fieldsToUpdate = [
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'password_hash' => $user->getPasswordHash(),
            'roles' => json_encode($user->getRoles()),
            'status' => $user->getStatus()->value,
            'activation_token' => $user->getActivationToken(),
            'reset_token' => $user->getResetToken(),
            'reset_token_expiry' => $user->getResetTokenExpiry(),
            'created_at' => $user->getCreatedAt(),
            'updated_at' => $user->getUpdatedAt(),
        ];

        return $this->updateFields($user->getId(), $fieldsToUpdate);
    }

    /**
     * Map database row to User entity.
     *
     * Hydrates a User entity from database result set including
     * related user data from JOIN.
     *
     * @param array<string, mixed> $data Database row data
     * @return User Fully hydrated User entity
     */
    private function mapToEntity(array $data): User
    {
        $user = new User();

        $user->setId((int) $data['id']);
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setPasswordHash($data['password_hash']);
        $user->setRoles(is_array($decodedRoles = json_decode($data['roles'] ?? '[]', true)) ? $decodedRoles : []);
        $user->setStatus(UserStatus::from($data['status']));
        $user->setActivationToken($data['activation_token']);
        $user->setResetToken($data['reset_token']);
        $user->setResetTokenExpiry($data['reset_token_expiry']);
        $user->setCreatedAt($data['created_at']);
        $user->setUpdatedAt($data['updated_at']);

        return $user;
    }

    /**
     * Convert a User record to an array with selected fields.
     *
     * @param User $user The User record to convert
     * @param array<string> $fields Optional list of specific fields to include
     * @return array<string, mixed> Array representation of User record
     */
    public function toArray(User $user, array $fields = []): array
    {
        $allFields = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'password_hash' => $user->getPasswordHash(),
            'roles' => $user->getRoles(),
            'status' => match ($user->getStatus()) {
                'A' => '☑️ Active',
                'P' => '⏳ Pending',
                'I' => '❌ Inactive',
                default => '❓ Unknown'
            },
            'activation_token' => $user->getActivationToken(),
            'reset_token' => $user->getResetToken(),
            'reset_token_expiry' => $user->getResetTokenExpiry(),
            'created_at' => $user->getCreatedAt(),
            'updated_at' => $user->getUpdatedAt(),
        ];

        if (!empty($fields)) {
            return array_intersect_key($allFields, array_flip($fields));
        }

        return $allFields;
    }
}
