<?php

declare(strict_types=1);

namespace App\Features\Testy;

// use App\Entities\Testy;
use Core\Database\ConnectionInterface;
use Core\Repository\AbstractMultiTenantRepository;
use Core\Repository\BaseRepositoryInterface;

/**
 * Generated File - Date: 2025-10-30 20:01
 * Repository implementation for Testy entity.
 *
 * Handles all database operations for Testy records including CRUD operations,
 * draft saving, and entity mapping with JOIN support for related user data.
 *
 * @implements TestyRepositoryInterface
 * @implements BaseRepositoryInterface
 */
class TestyRepository extends AbstractMultiTenantRepository implements TestyRepositoryInterface, BaseRepositoryInterface
{
    // Notes-: this 3 are used by abstract class
    protected string $tableName = 'testy';
    protected string $tableAlias = 't';
    protected string $primaryKey = 'id';

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
     * Find a Testy by ID with full entity mapping.
     *
     * @param int $id The Testy ID
     * @return Testy|null The Testy entity or null if not found
     */
    public function findById(int $id): ?Testy
    {
        $sql = "SELECT t.*, u.username
                FROM testy t
                LEFT JOIN user u ON t.user_id = u.id
                WHERE t.id = :id";

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
     * Find Testy records based on criteria with full entity mapping.
     *
     * @param array<string, mixed> $criteria Filtering criteria (field => value pairs)
     * @param array<string, string> $orderBy Sorting criteria (field => direction pairs)
     * @param int|null $limit Maximum number of records to return
     * @param int|null $offset Number of records to skip
     * @return array<Testy> Array of Testy entities matching criteria
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $sql = "SELECT t.*, u.username
                FROM testy t
                LEFT JOIN user u ON t.user_id = u.id";

        $params = [];

        // Build WHERE clause
        if (!empty($criteria)) {
            $whereClauses = [];
            foreach ($criteria as $field => $value) {
                $whereClauses[] = "t.{$field} = :{$field}";
                $params[":{$field}"] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        // Build ORDER BY clause
        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $field => $direction) {
                $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $orderClauses[] = "t.{$field} {$dir}";
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
        } else {
            $sql .= ' ORDER BY t.created_at DESC';
        }

        // Add LIMIT and OFFSET
        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
            if ($offset !== null) {
                $sql .= ' OFFSET :offset';
            }
        }

        $stmt = $this->connection->prepare($sql);

        // Bind parameters
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value, $this->getPdoType($value));
        }

        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        }
        if ($offset !== null) {
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        }

        $stmt->execute();

        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = $this->mapToEntity($row);
        }

        return $results;
    }

    /**
     * Create a new Testy record.
     *
     * @param Testy $testy The Testy record to create
     * @return Testy The created Testy record with ID populated
     */
    public function create(Testy $testy): Testy
    {
        $data = [
            'store_id' => $testy->getStoreId(),
            'user_id' => $testy->getUserId(),
            'status' => $testy->getStatus(),
            'slug' => $testy->getSlug(),
            'title' => $testy->getTitle(),
            'content' => $testy->getContent(),
            'generic_text' => $testy->getGenericText(),
            'image_count' => $testy->getImageCount(),
            'cover_image_id' => $testy->getCoverImageId(),
            'date_of_birth' => $testy->getDateOfBirth(),
            'generic_date' => $testy->getGenericDate(),
            'generic_month' => $testy->getGenericMonth(),
            'generic_week' => $testy->getGenericWeek(),
            'generic_time' => $testy->getGenericTime(),
            'generic_datetime' => $testy->getGenericDatetime(),
            'telephone' => $testy->getTelephone(),
            'gender_id' => $testy->getGenderId(),
            'gender_other' => $testy->getGenderOther(),
            'is_verified' => $testy->getIsVerified() ? 1 : 0,
            'interest_soccer_ind' => $testy->getInterestSoccerInd() ? 1 : 0,
            'interest_baseball_ind' => $testy->getInterestBaseballInd() ? 1 : 0,
            'interest_football_ind' => $testy->getInterestFootballInd() ? 1 : 0,
            'interest_hockey_ind' => $testy->getInterestHockeyInd() ? 1 : 0,
            'primary_email' => $testy->getPrimaryEmail(),
            'secret_code_hash' => $testy->getSecretCodeHash(),
            'balance' => $testy->getBalance(),
            'generic_decimal' => $testy->getGenericDecimal(),
            'volume_level' => $testy->getVolumeLevel(),
            'start_rating' => $testy->getStartRating(),
            'generic_number' => $testy->getGenericNumber(),
            'generic_num' => $testy->getGenericNum(),
            'generic_color' => $testy->getGenericColor(),
            'wake_up_time' => $testy->getWakeUpTime(),
            'favorite_week_day' => $testy->getFavoriteWeekDay(),
            'online_address' => $testy->getOnlineAddress(),
            'profile_picture' => $testy->getProfilePicture(),
            'created_at' => 'NOW()',
            'updated_at' => 'NOW()',
        ];

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = "INSERT INTO testy ("
             . implode(', ', $columns)
             . ") VALUES ("
             . implode(', ', $placeholders)
             . ")";

        $stmt = $this->connection->prepare($sql);

        foreach ($data as $col => $value) {
            if ($value === 'NOW()') {
                $stmt->bindValue(":{$col}", null);
            } else {
                $stmt->bindValue(":{$col}", $value, $this->getPdoType($value));
            }
        }

        $stmt->execute();

        $id = (int) $this->connection->lastInsertId();
        $testy->setId($id);

        return $this->findById($id);
    }

    /**
     * Update an existing Testy record.
     *
     * @param Testy $testy The Testy record to update
     * @return bool True if update was successful
     */
    public function update(Testy $testy): bool
    {
        $fieldsToUpdate = [
            'store_id' => $testy->getStoreId(),
            'user_id' => $testy->getUserId(),
            'status' => $testy->getStatus(),
            'slug' => $testy->getSlug(),
            'title' => $testy->getTitle(),
            'content' => $testy->getContent(),
            'generic_text' => $testy->getGenericText(),
            'image_count' => $testy->getImageCount(),
            'cover_image_id' => $testy->getCoverImageId(),
            'date_of_birth' => $testy->getDateOfBirth(),
            'generic_date' => $testy->getGenericDate(),
            'generic_month' => $testy->getGenericMonth(),
            'generic_week' => $testy->getGenericWeek(),
            'generic_time' => $testy->getGenericTime(),
            'generic_datetime' => $testy->getGenericDatetime(),
            'telephone' => $testy->getTelephone(),
            'gender_id' => $testy->getGenderId(),
            'gender_other' => $testy->getGenderOther(),
            'is_verified' => $testy->getIsVerified() ? 1 : 0,
            'interest_soccer_ind' => $testy->getInterestSoccerInd() ? 1 : 0,
            'interest_baseball_ind' => $testy->getInterestBaseballInd() ? 1 : 0,
            'interest_football_ind' => $testy->getInterestFootballInd() ? 1 : 0,
            'interest_hockey_ind' => $testy->getInterestHockeyInd() ? 1 : 0,
            'primary_email' => $testy->getPrimaryEmail(),
            'secret_code_hash' => $testy->getSecretCodeHash(),
            'balance' => $testy->getBalance(),
            'generic_decimal' => $testy->getGenericDecimal(),
            'volume_level' => $testy->getVolumeLevel(),
            'start_rating' => $testy->getStartRating(),
            'generic_number' => $testy->getGenericNumber(),
            'generic_num' => $testy->getGenericNum(),
            'generic_color' => $testy->getGenericColor(),
            'wake_up_time' => $testy->getWakeUpTime(),
            'favorite_week_day' => $testy->getFavoriteWeekDay(),
            'online_address' => $testy->getOnlineAddress(),
            'profile_picture' => $testy->getProfilePicture(),
        ];

        return $this->updateFields($testy->getId(), $fieldsToUpdate);
    }

    /**
     * Map database row to Testy entity.
     *
     * Hydrates a Testy entity from database result set including
     * related user data from JOIN.
     *
     * @param array<string, mixed> $data Database row data
     * @return Testy Fully hydrated Testy entity
     */
    private function mapToEntity(array $data): Testy
    {
        $testy = new Testy();

        $testy->setId((int) $data['id']);
        $testy->setStoreId($data['store_id']);
        $testy->setUserId($data['user_id']);
        $testy->setStatus($data['status']);
        $testy->setSlug($data['slug']);
        $testy->setTitle($data['title']);
        $testy->setContent($data['content']);
        $testy->setGenericText($data['generic_text']);
        $testy->setImageCount($data['image_count'] !== null ? (int) $data['image_count'] : null);
        $testy->setCoverImageId($data['cover_image_id'] !== null ? (int) $data['cover_image_id'] : null);
        $testy->setDateOfBirth($data['date_of_birth']);
        $testy->setGenericDate($data['generic_date']);
        $testy->setGenericMonth($data['generic_month']);
        $testy->setGenericWeek($data['generic_week']);
        $testy->setGenericTime($data['generic_time']);
        $testy->setGenericDatetime($data['generic_datetime']);
        $testy->setTelephone($data['telephone']);
        $testy->setGenderId($data['gender_id']);
        $testy->setGenderOther($data['gender_other']);
        $testy->setIsVerified((bool) $data['is_verified']);
        $testy->setInterestSoccerInd((bool) $data['interest_soccer_ind']);
        $testy->setInterestBaseballInd((bool) $data['interest_baseball_ind']);
        $testy->setInterestFootballInd((bool) $data['interest_football_ind']);
        $testy->setInterestHockeyInd((bool) $data['interest_hockey_ind']);
        $testy->setPrimaryEmail($data['primary_email']);
        $testy->setSecretCodeHash($data['secret_code_hash']);
        $testy->setBalance((float) $data['balance']);
        $testy->setGenericDecimal($data['generic_decimal'] !== null ? (float) $data['generic_decimal'] : null);
        $testy->setVolumeLevel($data['volume_level'] !== null ? (int) $data['volume_level'] : null);
        $testy->setStartRating($data['start_rating'] !== null ? (float) $data['start_rating'] : null);
        $testy->setGenericNumber((int) $data['generic_number']);
        $testy->setGenericNum((int) $data['generic_num']);
        $testy->setGenericColor($data['generic_color']);
        $testy->setWakeUpTime($data['wake_up_time']);
        $testy->setFavoriteWeekDay($data['favorite_week_day']);
        $testy->setOnlineAddress($data['online_address']);
        $testy->setProfilePicture($data['profile_picture']);

        return $testy;
    }

    /**
     * Convert a Testy record to an array with selected fields.
     *
     * @param Testy $testy The Testy record to convert
     * @param array<string> $fields Optional list of specific fields to include
     * @return array<string, mixed> Array representation of Testy record
     */
    public function toArray(Testy $testy, array $fields = []): array
    {
        $allFields = [
            'id' => $testy->getId(),
            'store_id' => $testy->getStoreId(),
            'user_id' => $testy->getUserId(),
            'status' => $testy->getStatus(),
            'slug' => $testy->getSlug(),
            'title' => $testy->getTitle(),
            'content' => $testy->getContent(),
            'generic_text' => $testy->getGenericText(),
            'image_count' => $testy->getImageCount(),
            'cover_image_id' => $testy->getCoverImageId(),
            'date_of_birth' => $testy->getDateOfBirth(),
            'generic_date' => $testy->getGenericDate(),
            'generic_month' => $testy->getGenericMonth(),
            'generic_week' => $testy->getGenericWeek(),
            'generic_time' => $testy->getGenericTime(),
            'generic_datetime' => $testy->getGenericDatetime(),
            'telephone' => $testy->getTelephone(),
            'gender_id' => $testy->getGenderId(),
            'gender_other' => $testy->getGenderOther(),
            'is_verified' => $testy->getIsVerified(),
            'interest_soccer_ind' => $testy->getInterestSoccerInd(),
            'interest_baseball_ind' => $testy->getInterestBaseballInd(),
            'interest_football_ind' => $testy->getInterestFootballInd(),
            'interest_hockey_ind' => $testy->getInterestHockeyInd(),
            'primary_email' => $testy->getPrimaryEmail(),
            'secret_code_hash' => $testy->getSecretCodeHash(),
            'balance' => $testy->getBalance(),
            'generic_decimal' => $testy->getGenericDecimal(),
            'volume_level' => $testy->getVolumeLevel(),
            'start_rating' => $testy->getStartRating(),
            'generic_number' => $testy->getGenericNumber(),
            'generic_num' => $testy->getGenericNum(),
            'generic_color' => $testy->getGenericColor(),
            'wake_up_time' => $testy->getWakeUpTime(),
            'favorite_week_day' => $testy->getFavoriteWeekDay(),
            'online_address' => $testy->getOnlineAddress(),
            'profile_picture' => $testy->getProfilePicture(),
        ];

        if (!empty($fields)) {
            return array_intersect_key($allFields, array_flip($fields));
        }

        return $allFields;
    }
}
