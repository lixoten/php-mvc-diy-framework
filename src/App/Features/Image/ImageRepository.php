<?php

declare(strict_types=1);

namespace App\Features\Image;

use App\Enums\ImageStatus;
use Core\Database\ConnectionInterface;
use Core\Repository\AbstractMultiTenantRepository;
use Core\Repository\BaseRepositoryInterface;
use App\Features\Image\Image;

/**
 * Generated File - Date: 2025-10-30 20:01
 * Repository implementation for Image entity.
 *
 * Handles all database operations for Image records including CRUD operations,
 * draft saving, and entity mapping with JOIN support for related user data.
 *
 * @implements ImageRepositoryInterface
 * @implements BaseRepositoryInterface
 */
class ImageRepository extends AbstractMultiTenantRepository implements ImageRepositoryInterface, BaseRepositoryInterface
{
    //private ConnectionInterface $connection; // temp

    // Notes-: this 3 are used by abstract class
    protected string $tableName = 'image';
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
    public function findById(int $id): ?Image
    {
        $sql = "SELECT t.*
                FROM image t
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
     * Map database row to Image entity.
     *
     * Hydrates a Image entity from database result set including
     * related user data from JOIN.
     *
     * @param array<string, mixed> $data Database row data
     * @return Image Fully hydrated Image entity
     */
    protected function mapToEntity(array $data): Image
    {
        $image = new Image();


        return $image;
    }

}
