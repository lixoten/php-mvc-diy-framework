<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entities\Testy;
use Core\Database\ConnectionInterface;
use Core\Repository\BaseRepositoryInterface;

class TestyRepository implements TestyRepositoryInterface, BaseRepositoryInterface
{
    private ConnectionInterface $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function saveDraft(array $data): bool // js-feature // Deleteme
    {
        // Auto Save / Draft Feature - JS
        // Example: Save draft to the main testy table (update only draft fields)
        $sql = "UPDATE testy SET
                    title = :title,
                    content = :content,
                    generic_text = :generic_text,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            ':id' => $data['id'] ?? null,
            ':title' => $data['title'] ?? '',
            ':content' => $data['content'] ?? '',
            ':generic_text' => $data['generic_text'] ?? '',
        ]);
    }


    /**
     * Find a testy by ID
     */
    public function findById(int $testyId): ?Testy
    {
        $sql = "SELECT p.*, u.username FROM testy p
                LEFT JOIN users u ON p.user_id = u.user_id
                WHERE p.id = :id";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $testyId);
        $stmt->execute();

        $testyData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$testyData) {
            return null;
        }

        return $this->mapToEntity($testyData);
    }


    /**
     * Find a testy by ID, selecting only specified columns.
     *
     * @param int $testyId
     * @param array<string> $fields
     * @return array<string, mixed>|null
     */
    public function findByIdWithFields(int $testyId, array $fields): ?array
    {
        if (empty($fields)) {
            // Default to all columns if none specified
            $fields = ['*'];
        }
        $columns = implode(', ', $fields);

        $sql = "SELECT $columns FROM testy WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $testyId, \PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result === false) {
            return null;
        }

        return $result;
    }

    /**
     * Find testy by store ID
     */
    public function findByStoreId(
        int $storeId,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        return $this->findBy(
            ['store_id' => $storeId],
            $orderBy,
            $limit,
            $offset
        );
    }


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
        } else {
            $fields = array_merge($fields, ['id', 'status']);
            // unset($fields['id']);
            // unset($fields['status']);
        }

        foreach ($fields as $f) {
            if (!preg_match('/^[A-Za-z0-9_\\.]+$/', (string) $f)) {
                throw new \InvalidArgumentException("Invalid field name: {$f}");
            }
        }

        $columns = implode(', ', $fields);
        $sql = "SELECT {$columns} FROM testy p WHERE p.store_id = :store_id";

        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $field => $direction) {
                $dir = strtoupper((string) $direction) === 'DESC' ? 'DESC' : 'ASC';
                $cleanField = strpos((string) $field, '.') === false ? "p.{$field}" : (string) $field;
                if (!preg_match('/^[A-Za-z0-9_\\.]+$/', $cleanField)) {
                    throw new \InvalidArgumentException("Invalid order field: {$cleanField}");
                }
                $orderClauses[] = "{$cleanField} {$dir}";
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
        } else {
            $sql .= ' ORDER BY p.created_at DESC';
        }

        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
            if ($offset !== null) {
                $sql .= ' OFFSET :offset';
            }
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':store_id', $storeId, \PDO::PARAM_INT);

        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        }
        if ($offset !== null) {
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        }

        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($rows === false) {
            return [];
        }

        return $rows;
    }




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

        foreach ($fields as $f) {
            if (!preg_match('/^[A-Za-z0-9_\\.]+$/', (string) $f)) {
                throw new \InvalidArgumentException("Invalid field name: {$f}");
            }
        }

        $columns = implode(', ', $fields);
        $sql = "SELECT {$columns} FROM testy p WHERE p.user_id = :user_id";

        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $field => $direction) {
                $dir = strtoupper((string) $direction) === 'DESC' ? 'DESC' : 'ASC';
                $cleanField = strpos((string) $field, '.') === false ? "p.{$field}" : (string) $field;
                if (!preg_match('/^[A-Za-z0-9_\\.]+$/', $cleanField)) {
                    throw new \InvalidArgumentException("Invalid order field: {$cleanField}");
                }
                $orderClauses[] = "{$cleanField} {$dir}";
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
        } else {
            $sql .= ' ORDER BY p.created_at DESC';
        }

        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
            if ($offset !== null) {
                $sql .= ' OFFSET :offset';
            }
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);

        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        }

        if ($offset !== null) {
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        }

        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($rows === false) {
            return [];
        }

        return $rows;
    }



    /**
     * Find testy based on criteria.
     *
     * @param array $criteria Optional filtering criteria (e.g., ['user_id' => 5, 'status' => 'Published'])
     * @param array $orderBy Optional sorting criteria (e.g., ['created_at' => 'DESC'])
     * @param int|null $limit Maximum number of results
     * @param int|null $offset Result offset for pagination
     * @return Testy[] Array of Testy entities
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $sql = "SELECT p.*, u.username FROM testy p
                LEFT JOIN users u ON p.user_id = u.user_id";
        $params = [];

        // Add WHERE clauses for criteria
        if (!empty($criteria)) {
            $whereClauses = [];
            foreach ($criteria as $field => $value) {
                $whereClauses[] = "p.$field = :$field";
                $params[":$field"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        // Add ORDER BY clause
        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $field => $direction) {
                $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $orderClauses[] = "p.$field $direction";
            }
            $sql .= " ORDER BY " . implode(', ', $orderClauses);
        } else {
            // Default ordering by creation date, newest first
            $sql .= " ORDER BY p.created_at DESC";
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

        $testy = [];
        while ($testyData = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $testy[] = $this->mapToEntity($testyData);
        }

        return $testy;
    }

    /**
     * Find testy by user ID
     */
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

    /**
     * Create a new testy
     */
    public function create(object $testy): object
    {
        $sql = "INSERT INTO testy (
                store_id,
                user_id,
                status,
                slug,
                title,
                content,
                generic_text,
                date_of_birth,
                telephone,
                gender_id,
                gender_other,
                is_verified,
                interest_soccer_ind,
                interest_baseball_ind,
                interest_football_ind,
                interest_hockey_ind,
                created_at,
                updated_at
            ) VALUES (
                :store_id,
                :user_id,
                :status,
                :slug,
                :title,
                :content,
                :generic_text,
                :date_of_birth,
                :telephone,
                :gender_id,
                :gender_other,
                :is_verified,
                :interest_soccer_ind,
                :interest_baseball_ind,
                :interest_football_ind,
                :interest_hockey_ind,

                NOW(),
                NOW()
            )";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':store_id', $testy->getTestyStoreId());
        $stmt->bindValue(':user_id', $testy->getTestyUserId());
        $stmt->bindValue(':status', $testy->getTestyStatus());
        $stmt->bindValue(':slug', $testy->getSlug());
        $stmt->bindValue(':title', $testy->getTitle());
        $stmt->bindValue(':content', $testy->getContent());
        $stmt->bindValue(':generic_text', $testy->getFavoriteWord());
        $stmt->bindValue(':date_of_birth', $testy->getDateOfBirth());
        $stmt->bindValue(':telephone', $testy->getTelephone());
        $stmt->bindValue(':gender_id', $testy->getGenderId());
        $stmt->bindValue(':gender_other', $testy->getGenderOther());
        $stmt->bindValue(':is_verified', $testy->getIsVerified());
        $stmt->bindValue(':interest_soccer_ind', $testy->getInterestSoccerInd());
        $stmt->bindValue(':interest_baseball_ind', $testy->getInterestBaseballInd());
        $stmt->bindValue(':interest_football_ind', $testy->getInterestFootballInd());
        $stmt->bindValue(':interest_hockey_ind', $testy->getInterestHockeyInd());

        $stmt->execute();

        // Get the ID of the newly created testy
        $testyId = (int) $this->connection->lastInsertId();
        $testy->setTestyId($testyId);

        // Fetch fresh from database to get created_at and updated_at
        return $this->findById($testyId);
    }




    // /** @var App\Entities\Testy */
    /**
     * Update an existing testy
     */
    public function update(object $testy): bool
    {
        $sql = "UPDATE testy SET
                store_id = :store_id,
                user_id = :user_id,
                status = :status,
                slug = :slug,
                title = :title,
                content = :content,
                generic_text   = :generic_text,
                date_of_birth   = :date_of_birth,
                telephone       = :telephone,
                gender_id       = :gender_id,
                gender_other    = :gender_other,
                is_verified     = :is_verified,
                interest_soccer_ind     = :interest_soccer_ind,
                interest_baseball_ind   = :interest_baseball_ind,
                interest_football_ind   = :interest_football_ind,
                interest_hockey_ind     = :interest_hockey_ind,
                updated_at      = NOW()
            WHERE id      = :id";

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':id', $testy->getTestyId());
        $stmt->bindValue(':store_id', $testy->getTestyStoreId());
        $stmt->bindValue(':user_id', $testy->getTestyUserId());
        $stmt->bindValue(':status', $testy->getTestyStatus());
        $stmt->bindValue(':slug', $testy->getSlug());
        $stmt->bindValue(':title', $testy->getTitle());
        $stmt->bindValue(':content', $testy->getContent());
        $stmt->bindValue(':generic_text', $testy->getFavoriteWord());
        $stmt->bindValue(':date_of_birth', $testy->getDateOfBirth());
        $stmt->bindValue(':telephone', $testy->getTelephone());
        $stmt->bindValue(':gender_id', $testy->getGenderId());
        $stmt->bindValue(':gender_other', $testy->getGenderOther());
        $stmt->bindValue(':is_verified', $testy->getIsVerified());
        $stmt->bindValue(':interest_soccer_ind', $testy->getInterestSoccerInd());
        $stmt->bindValue(':interest_baseball_ind', $testy->getInterestBaseballInd());
        $stmt->bindValue(':interest_football_ind', $testy->getInterestFootballInd());
        $stmt->bindValue(':interest_hockey_ind', $testy->getInterestHockeyInd());

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }



    /**
     * Update only the specified fields for a Testy record.
     *
     * @param int $testyId
     * @param array<string, mixed> $fieldsToUpdate
     * @return bool
     */
    public function updateFields(int $testyId, array $fieldsToUpdate): bool
    {
        if (empty($fieldsToUpdate)) {
            return false;
        }

        $setClauses = [];
        $params = [':id' => $testyId];

        foreach ($fieldsToUpdate as $field => $value) {
            $setClauses[] = "$field = :$field";
            $params[":$field"] = $value;
        }

        $setClauses[] = "updated_at = NOW()";
        $sql = "UPDATE testy SET " . implode(', ', $setClauses) . " WHERE id = :id";

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute($params);
    }




    /**
     * Insert a new record into the testy table.
     *
     * @param array<string, mixed> $data The data to insert.
     * @return int The ID of the newly inserted record.
     */
    public function insertFields(array $data): int
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Data array cannot be empty.');
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);
        $sql = "INSERT INTO testy (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $this->connection->prepare($sql);

        foreach ($data as $col => $value) {
            $stmt->bindValue(":$col", $value);
        }

        $stmt->execute();

        return (int) $this->connection->lastInsertId();
    }








    /**
     * Delete a testy
     */
    public function delete(int $testyId): bool
    {
        $sql = "DELETE FROM testy WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $testyId);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Count total testy based on criteria.
     *
     * @param array $criteria Optional filtering criteria
     * @return int The total count.
     */
    public function countBy(array $criteria = []): int // Renamed from countAll
    {
        $sql = "SELECT COUNT(*) as count FROM testy p"; // Use alias 'p'
        $params = [];

        // Add WHERE clauses for criteria
        if (!empty($criteria)) {
            $whereClauses = [];
            foreach ($criteria as $field => $value) {
                $placeholder = ':' . str_replace('.', '_', $field);
                $whereClauses[] = "p.$field = $placeholder"; // Use alias 'p'
                $params[$placeholder] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $stmt = $this->connection->prepare($sql);

        // Bind parameters
        foreach ($params as $param => $value) {
            $pdoType = \PDO::PARAM_STR;
            if (is_int($value)) {
                $pdoType = \PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $pdoType = \PDO::PARAM_BOOL;
            } elseif (is_null($value)) {
                $pdoType = \PDO::PARAM_NULL;
            }
            $stmt->bindValue($param, $value, $pdoType);
        }

        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int) ($result['count'] ?? 0); // Ensure integer return, default 0
    }



    /**
     * Counts testy associated with a specific store ID.
     */
    public function countByStoreId(int $storeId): int
    {
        // Simply reuse the existing countAll logic
        return $this->countBy(['store_id' => $storeId]);
    }

    /**
     * Counts testy associated with a specific user ID.
     */
    public function countByUserId(int $userId): int
    {
        // Simply reuse the existing countAll logic
        return $this->countBy(['user_id' => $userId]);
    }


    /**
     * Map database row to Testy entity
     */
    private function mapToEntity(array $testyData): Testy
    {
        $testy = new Testy();

        $testy->setTestyId((int) $testyData['id']);
        $testy->setTestyStoreId((int) $testyData['store_id']);
        $testy->setTestyUserId((int) $testyData['user_id']);
        $testy->setTestyStatus($testyData['status']);
        $testy->setSlug($testyData['slug']);
        $testy->setTitle($testyData['title']);
        $testy->setContent($testyData['content']);
        $testy->setFavoriteWord($testyData['generic_text']);
        $testy->setDateOfBirth($testyData['date_of_birth']);
        $testy->setTelephone($testyData['telephone']);
        $testy->setGenderId($testyData['gender_id']);
        $testy->setGenderOther($testyData['gender_other']);
        $testy->setIsVerified($testyData['is_verified']);
        $testy->setInterestSoccerInd($testyData['interest_soccer_ind']);
        $testy->setInterestBaseballInd($testyData['interest_baseball_ind']);
        $testy->setInterestFootballInd($testyData['interest_football_ind']);
        $testy->setInterestHockeyInd($testyData['interest_hockey_ind']);
        $testy->setCreatedAt($testyData['created_at']);
        $testy->setUpdatedAt($testyData['updated_at']);

        // Optional username from join
        if (isset($testyData['username'])) {
            $testy->setUsername($testyData['username']);
        }

        return $testy;
    }

    /**
     * Convert a Testy entity to an array with selected fields.
     *
     * @param Testy $testy
     * @param array $fields
     * @return array
     */
    public function toArray(Testy $testy, array $fields = []): array
    {
        $allFields = [
            'id' => $testy->getTestyId(),
            'store_id' => $testy->getTestyStoreId(),
            'user_id' => $testy->getTestyUserId(),
            'status' => match ($testy->getTestyStatus()) {
                'A' => '☑️ Active',
                'P' => '⏳ Pending',
                'I' => '❌ Inactive',
                default => '❓ Unknown'
            },
            'slug' => $testy->getSlug(),
            'title' => $testy->getTitle(),
            'content' => $testy->getContent(),
            'generic_text' => $testy->getFavoriteWord(),
            'date_of_birth' => $testy->getDateOfBirth(),
            'telephone' => $testy->getTelephone(),
            'gender_id' => $testy->getGenderId(),
            'gender_other' => $testy->getGenderOther(),
            'is_verified' => $testy->getIsVerified(),
            'interest_soccer_ind' => $testy->getInterestSoccerInd(),
            'interest_baseball_ind' => $testy->getInterestBaseballInd(),
            'interest_football_ind' => $testy->getInterestFootballInd(),
            'interest_hockey_ind' => $testy->getInterestHockeyInd(),
            'created_at' => $testy->getCreatedAt(),
            'updated_at' => $testy->getUpdatedAt(),
            'username' => $testy->getUsername(),
        ];

        // 'created_at' => $testy->getCreatedAt() instanceof \DateTime ?
            // $testy->getCreatedAt()->format('Y-m-d H:i:s') : $testy->getCreatedAt(),
        // 'updated_at' => $testy->getUpdatedAt() instanceof \DateTime ?
            // $testy->getUpdatedAt()->format('Y-m-d H:i:s') : $testy->getUpdatedAt(),




        // Return only the requested fields
        if (!empty($fields)) {
            return array_intersect_key($allFields, array_flip($fields));
        }

        // Return all fields by default
        return $allFields;
    }
}
