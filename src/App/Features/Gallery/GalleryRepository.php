<?php

declare(strict_types=1);

namespace App\Features\Gallery;

use Core\Database\ConnectionInterface;
use Core\Repository\AbstractMultiTenantRepository;
use Core\Repository\BaseRepositoryInterface;

/**
 * Generated File - Date: 2025-10-30 20:01
 * Repository implementation for Gallery entity.
 *
 * Handles all database operations for Gallery records including CRUD operations,
 * draft saving, and entity mapping with JOIN support for related user data.
 *
 * @implements GalleryRepositoryInterface
 * @implements BaseRepositoryInterface
 */
class GalleryRepository extends AbstractMultiTenantRepository implements
    GalleryRepositoryInterface,
    BaseRepositoryInterface
{
    //private ConnectionInterface $connection; // temp

    // Notes-: this 3 are used by abstract class
    protected string $tableName  = 'gallery';
    protected string $tableAlias = 't';
    protected string $primaryKey = 'id';

    /**
     * Initialize repository with database connection.
     *
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        //  $this->connection = $connection; // shitload
        parent::__construct($connection);
    }


    /** {@inheritdoc} */
    public function findById(int $id): ?Gallery
    {
        $sql = "SELECT t.*
                FROM gallery t
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

#    /**
#     * Find a Gallery by ID WITH user data (explicit JOIN).
#     * Use this when you specifically need user information.
#     *
#     * @param int $id The Gallery ID
#     * @return Gallery|null The Gallery entity with user data or null if not found
#     */
#    public function findByIdWithUser(int $id): ?Gallery
#    {
#        $sql = "SELECT t.*, u.username
#                FROM gallery t
#                LEFT JOIN user u ON t.user_id = u.id
#                WHERE t.id = :id";
#
#        $stmt = $this->connection->prepare($sql);
#        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
#        $stmt->execute();
#
#        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
#
#        if (!$data) {
#            return null;
#        }
#
#        return $this->mapToEntityWithUser($data);
#    }
#
#    /**
#     * Find Gallery records WITH user data (explicit JOIN).
#     * Use this when you specifically need user information.
#     *
#     * @param array<string, mixed> $criteria Filtering criteria (field => value pairs)
#     * @param array<string, string> $orderBy Sorting criteria (field => direction pairs)
#     * @param int|null $limit Maximum number of records to return
#     * @param int|null $offset Number of records to skip
#     * @return array<Gallery> Array of Gallery entities with user data
#     */
#    public function findByWithUser(
#        array $criteria = [],
#        array $orderBy = [],
#        ?int $limit = null,
#        ?int $offset = null
#    ): array {
#        $sql = "SELECT t.*, u.username
#                FROM gallery t
#                LEFT JOIN user u ON t.user_id = u.id";
#
#        $params = [];
#
#        if (!empty($criteria)) {
#            $whereClauses = [];
#            foreach ($criteria as $field => $value) {
#                $whereClauses[] = "t.{$field} = :{$field}";
#                $params[":{$field}"] = $value;
#            }
#            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
#        }
#
#        if (!empty($orderBy)) {
#            $orderClauses = [];
#            foreach ($orderBy as $field => $direction) {
#                $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
#                $orderClauses[] = "t.{$field} {$dir}";
#            }
#            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
#        } else {
#            $sql .= ' ORDER BY t.created_at DESC';
#        }
#
#        if ($limit !== null) {
#            $sql .= ' LIMIT :limit';
#            if ($offset !== null) {
#                $sql .= ' OFFSET :offset';
#            }
#        }
#
#        $stmt = $this->connection->prepare($sql);
#
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
#            $results[] = $this->mapToEntityWithUser($row);
#        }
#
#        return $results;
#    }
#
#
#
#    /**
#     * Map database row to Gallery entity WITH user data from JOIN.
#     *
#     * @param array<string, mixed> $data Database row data including user columns
#     * @return Gallery Fully hydrated Gallery entity with user data
#     */
#    private function mapToEntityWithUser(array $data): Gallery
#    {
#        $gallery = $this->mapToEntity($data);
#
#        if (isset($data['username'])) {
#            $gallery->setUsername($data['username']);
#        }
#
#        return $gallery;
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
#    /**
#     * Create a new Gallery record.
#     *
#     * @param Gallery $gallery The Gallery record to create
#     * @return Gallery The created Gallery record with ID populated
#     */
#    public function create(Gallery $gallery): Gallery
#    {
#        $data = [
#            'store_id' => $gallery->getStoreId(),
#            'user_id' => $gallery->getUserId(),
#            'status' => $gallery->getStatus(),
#            'slug' => $gallery->getSlug(),
#            'title' => $gallery->getTitle(),
#            'content' => $gallery->getContent(),
#            'generic_text' => $gallery->getGenericText(),
#            'image_count' => $gallery->getImageCount(),
#            'cover_image_id' => $gallery->getCoverImageId(),
#            'date_of_birth' => $gallery->getDateOfBirth(),
#            'generic_date' => $gallery->getGenericDate(),
#            'generic_month' => $gallery->getGenericMonth(),
#            'generic_week' => $gallery->getGenericWeek(),
#            'generic_time' => $gallery->getGenericTime(),
#            'generic_datetime' => $gallery->getGenericDatetime(),
#            'telephone' => $gallery->getTelephone(),
#            'gender_id' => $gallery->getGenderId(),
#            'gender_other' => $gallery->getGenderOther(),
#            'is_verified' => $gallery->getIsVerified() ? 1 : 0,
#            'interest_soccer_ind' => $gallery->getInterestSoccerInd() ? 1 : 0,
#            'interest_baseball_ind' => $gallery->getInterestBaseballInd() ? 1 : 0,
#            'interest_football_ind' => $gallery->getInterestFootballInd() ? 1 : 0,
#            'interest_hockey_ind' => $gallery->getInterestHockeyInd() ? 1 : 0,
#            'primary_email' => $gallery->getPrimaryEmail(),
#            'secret_code_hash' => $gallery->getSecretCodeHash(),
#            'balance' => $gallery->getBalance(),
#            'generic_decimal' => $gallery->getGenericDecimal(),
#            'volume_level' => $gallery->getVolumeLevel(),
#            'start_rating' => $gallery->getStartRating(),
#            'generic_number' => $gallery->getGenericNumber(),
#            'generic_num' => $gallery->getGenericNum(),
#            'generic_color' => $gallery->getGenericColor(),
#            'wake_up_time' => $gallery->getWakeUpTime(),
#            'favorite_week_day' => $gallery->getFavoriteWeekDay(),
#            'online_address' => $gallery->getOnlineAddress(),
#            'profile_picture' => $gallery->getProfilePicture(),
#            'created_at' => 'NOW()',
#            'updated_at' => 'NOW()',
#        ];
#
#        $columns = array_keys($data);
#        $placeholders = array_map(fn($col) => ":{$col}", $columns);
#
#        $sql = "INSERT INTO gallery ("
#             . implode(', ', $columns)
#             . ") VALUES ("
#             . implode(', ', $placeholders)
#             . ")";
#
#        $stmt = $this->connection->prepare($sql);
#
#        foreach ($data as $col => $value) {
#            if ($value === 'NOW()') {
#                $stmt->bindValue(":{$col}", null);
#            } else {
#                $stmt->bindValue(":{$col}", $value, $this->getPdoType($value));
#            }
#        }
#
#        $stmt->execute();
#
#        $id = (int) $this->connection->lastInsertId();
#        $gallery->setId($id);
#
#        return $this->findById($id);
#    }
#
#    /**
#     * Update an existing Gallery record.
#     *
#     * @param Gallery $gallery The Gallery record to update
#     * @return bool True if update was successful
#     */
#    public function update(Gallery $gallery): bool
#    {
#        $fieldsToUpdate = [
#            'store_id' => $gallery->getStoreId(),
#            'user_id' => $gallery->getUserId(),
#            'status' => $gallery->getStatus(),
#            'slug' => $gallery->getSlug(),
#            'title' => $gallery->getTitle(),
#            'content' => $gallery->getContent(),
#            'generic_text' => $gallery->getGenericText(),
#            'image_count' => $gallery->getImageCount(),
#            'cover_image_id' => $gallery->getCoverImageId(),
#            'date_of_birth' => $gallery->getDateOfBirth(),
#            'generic_date' => $gallery->getGenericDate(),
#            'generic_month' => $gallery->getGenericMonth(),
#            'generic_week' => $gallery->getGenericWeek(),
#            'generic_time' => $gallery->getGenericTime(),
#            'generic_datetime' => $gallery->getGenericDatetime(),
#            'telephone' => $gallery->getTelephone(),
#            'gender_id' => $gallery->getGenderId(),
#            'gender_other' => $gallery->getGenderOther(),
#            'is_verified' => $gallery->getIsVerified() ? 1 : 0,
#            'interest_soccer_ind' => $gallery->getInterestSoccerInd() ? 1 : 0,
#            'interest_baseball_ind' => $gallery->getInterestBaseballInd() ? 1 : 0,
#            'interest_football_ind' => $gallery->getInterestFootballInd() ? 1 : 0,
#            'interest_hockey_ind' => $gallery->getInterestHockeyInd() ? 1 : 0,
#            'primary_email' => $gallery->getPrimaryEmail(),
#            'secret_code_hash' => $gallery->getSecretCodeHash(),
#            'balance' => $gallery->getBalance(),
#            'generic_decimal' => $gallery->getGenericDecimal(),
#            'volume_level' => $gallery->getVolumeLevel(),
#            'start_rating' => $gallery->getStartRating(),
#            'generic_number' => $gallery->getGenericNumber(),
#            'generic_num' => $gallery->getGenericNum(),
#            'generic_color' => $gallery->getGenericColor(),
#            'wake_up_time' => $gallery->getWakeUpTime(),
#            'favorite_week_day' => $gallery->getFavoriteWeekDay(),
#            'online_address' => $gallery->getOnlineAddress(),
#            'profile_picture' => $gallery->getProfilePicture(),
#        ];
#
#        return $this->updateFields($gallery->getId(), $fieldsToUpdate);
#    }

    /**
     * Map database row to Gallery entity.
     *
     * Hydrates a Gallery entity from database result set including
     * related user data from JOIN.
     *
     * @param array<string, mixed> $data Database row data
     * @return Gallery Fully hydrated Gallery entity
     */
    protected function mapToEntity(array $data): Gallery
    {
        $gallery = new Gallery();

        $gallery->setId((int) $data['id']);
        $gallery->setStoreId($data['store_id']);
        $gallery->setUserId($data['user_id']);
        $gallery->setStatus($data['status']);
        $gallery->setSlug($data['slug']);
        $gallery->setName($data['name']);
        $gallery->setDescription($data['description']);
        $gallery->setImageCount($data['image_count'] !== null ? (int) $data['image_count'] : null);
        $gallery->setCoverImageId($data['cover_image_id'] !== null ? (int) $data['cover_image_id'] : null);

        return $gallery;
    }

#    /**
#     * Convert a Gallery record to an array with selected fields.
#     *
#     * @param Gallery $gallery The Gallery record to convert
#     * @param array<string> $fields Optional list of specific fields to include
#     * @return array<string, mixed> Array representation of Gallery record
#     */
#    public function toArray(Gallery $gallery, array $fields = []): array
#    {
#        $allFields = [
#            'id' => $gallery->getId(),
#            'store_id' => $gallery->getStoreId(),
#            'user_id' => $gallery->getUserId(),
#            'status' => $gallery->getStatus(),
#            'slug' => $gallery->getSlug(),
#            'title' => $gallery->getTitle(),
#            'content' => $gallery->getContent(),
#            'generic_text' => $gallery->getGenericText(),
#            'image_count' => $gallery->getImageCount(),
#            'cover_image_id' => $gallery->getCoverImageId(),
#            'date_of_birth' => $gallery->getDateOfBirth(),
#            'generic_date' => $gallery->getGenericDate(),
#            'generic_month' => $gallery->getGenericMonth(),
#            'generic_week' => $gallery->getGenericWeek(),
#            'generic_time' => $gallery->getGenericTime(),
#            'generic_datetime' => $gallery->getGenericDatetime(),
#            'telephone' => $gallery->getTelephone(),
#            'gender_id' => $gallery->getGenderId(),
#            'gender_other' => $gallery->getGenderOther(),
#            'is_verified' => $gallery->getIsVerified(),
#            'interest_soccer_ind' => $gallery->getInterestSoccerInd(),
#            'interest_baseball_ind' => $gallery->getInterestBaseballInd(),
#            'interest_football_ind' => $gallery->getInterestFootballInd(),
#            'interest_hockey_ind' => $gallery->getInterestHockeyInd(),
#            'primary_email' => $gallery->getPrimaryEmail(),
#            'secret_code_hash' => $gallery->getSecretCodeHash(),
#            'balance' => $gallery->getBalance(),
#            'generic_decimal' => $gallery->getGenericDecimal(),
#            'volume_level' => $gallery->getVolumeLevel(),
#            'start_rating' => $gallery->getStartRating(),
#            'generic_number' => $gallery->getGenericNumber(),
#            'generic_num' => $gallery->getGenericNum(),
#            'generic_color' => $gallery->getGenericColor(),
#            'wake_up_time' => $gallery->getWakeUpTime(),
#            'favorite_week_day' => $gallery->getFavoriteWeekDay(),
#            'online_address' => $gallery->getOnlineAddress(),
#            'profile_picture' => $gallery->getProfilePicture(),
#        ];
#
#        if (!empty($fields)) {
#            return array_intersect_key($allFields, array_flip($fields));
#        }
#
#        return $allFields;
#    }
}
