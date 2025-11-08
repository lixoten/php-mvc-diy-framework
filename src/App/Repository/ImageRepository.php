<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entities\Image;
use Core\Database\ConnectionInterface;
use Core\Repository\AbstractMultiTenantRepository;
use Core\Repository\BaseRepositoryInterface;

class ImageRepository extends AbstractMultiTenantRepository implements ImageRepositoryInterface, BaseRepositoryInterface
{
    protected string $tableName = 'image';
    protected string $tableAlias = 'i';
    protected string $primaryKey = 'id';

    public function __construct(ConnectionInterface $connection)
    {
        parent::__construct($connection);
    }

    /**
     * Find an image by ID with full entity mapping.
     *
     * @param int $id
     * @return Image|null
     */
    public function findById(int $id): ?Image
    {
        $sql = "SELECT i.*, u.username FROM {$this->tableName} i
                LEFT JOIN users u ON i.user_id = u.user_id
                WHERE i.{$this->primaryKey} = :id";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $imageData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$imageData) {
            return null;
        }

        return $this->mapToEntity($imageData);
    }

    /**
     * Find images based on criteria with full entity mapping.
     *
     * @param array<string, mixed> $criteria
     * @param array<string, string> $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array<Image>
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $sql = "SELECT i.*, u.username FROM {$this->tableName} i
                LEFT JOIN users u ON i.user_id = u.user_id";
        $params = [];

        // Build WHERE clause
        if (!empty($criteria)) {
            $whereClauses = [];
            foreach ($criteria as $field => $value) {
                $whereClauses[] = "i.{$field} = :{$field}";
                $params[":{$field}"] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        // Build ORDER BY clause
        if (!empty($orderBy)) {
            $orderClauses = [];
            foreach ($orderBy as $field => $direction) {
                $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $orderClauses[] = "i.{$field} {$dir}";
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
        } else {
            $sql .= ' ORDER BY i.created_at DESC';
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

        $images = [];
        while ($imageData = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $images[] = $this->mapToEntity($imageData);
        }

        return $images;
    }

    /**
     * Find images by gallery ID.
     *
     * @param int $galleryId
     * @param array<string, string> $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array<Image>
     */
    public function findByGalleryId(
        int $galleryId,
        array $orderBy = ['display_order' => 'ASC'],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        return $this->findBy(
            ['gallery_id' => $galleryId],
            $orderBy,
            $limit,
            $offset
        );
    }

    /**
     * Find images by gallery ID with specified fields.
     *
     * @param int $galleryId
     * @param array<string> $fields
     * @param array<string, string> $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array<array<string, mixed>>
     */
    public function findByGalleryIdWithFields(
        int $galleryId,
        array $fields,
        array $orderBy = ['display_order' => 'ASC'],
        ?int $limit = null,
        ?int $offset = null
    ): array {
        if (empty($fields)) {
            $fields = ['*'];
        }

        return $this->findByCriteriaWithFields(
            ['gallery_id' => $galleryId],
            $fields,
            $orderBy,
            $limit,
            $offset
        );
    }

    /**
     * Count images by gallery ID.
     *
     * @param int $galleryId
     * @return int
     */
    public function countByGalleryId(int $galleryId): int
    {
        return $this->countBy(['gallery_id' => $galleryId]);
    }

    /**
     * Get featured image for a gallery.
     *
     * @param int $galleryId
     * @return Image|null
     */
    public function getFeaturedImageByGalleryId(int $galleryId): ?Image
    {
        $results = $this->findBy(['gallery_id' => $galleryId, 'is_featured' => 1], [], 1);
        return $results[0] ?? null;
    }

    /**
     * Set featured image for a gallery (unsets all others in the same gallery).
     *
     * @param int $imageId
     * @param int $galleryId
     * @return bool
     */
    public function setFeaturedImage(int $imageId, int $galleryId): bool
    {
        // First, unset all featured images in this gallery
        $sql = "UPDATE {$this->tableName}
                SET is_featured = 0, updated_at = NOW()
                WHERE gallery_id = :gallery_id";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':gallery_id', $galleryId, \PDO::PARAM_INT);
        $stmt->execute();

        // Then, set the specified image as featured
        return $this->updateFields($imageId, ['is_featured' => 1]);
    }

    /**
     * Update display order for an image.
     *
     * @param int $id
     * @param int $displayOrder
     * @return bool
     */
    public function updateDisplayOrder(int $id, int $displayOrder): bool
    {
        return $this->updateFields($id, ['display_order' => $displayOrder]);
    }

    /**
     * Create a new image.
     *
     * @param Image $image
     * @return Image
     */
    public function create(object $image): object
    {
        $data = [
            'store_id' => $image->getImageStoreId(),
            'user_id' => $image->getImageUserId(),
            'gallery_id' => $image->getGalleryId(),
            'status' => $image->getImageStatus(),
            'title' => $image->getTitle(),
            'description' => $image->getDescription(),
            'filename' => $image->getFilename(),
            'filepath' => $image->getFilepath(),
            'filesize' => $image->getFilesize(),
            'mime_type' => $image->getMimeType(),
            'width' => $image->getWidth(),
            'height' => $image->getHeight(),
            'alt_text' => $image->getAltText(),
            'caption' => $image->getCaption(),
            'display_order' => $image->getDisplayOrder(),
            'is_featured' => $image->getIsFeatured() ? 1 : 0,
            'created_at' => 'NOW()',
            'updated_at' => 'NOW()',
        ];

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = "INSERT INTO {$this->tableName} ("
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
        $image->setImageId($id);

        return $this->findById($id);
    }

    /**
     * Update an existing image.
     *
     * @param Image $image
     * @return bool
     */
    public function update(object $image): bool
    {
        $fieldsToUpdate = [
            'store_id' => $image->getImageStoreId(),
            'user_id' => $image->getImageUserId(),
            'gallery_id' => $image->getGalleryId(),
            'status' => $image->getImageStatus(),
            'title' => $image->getTitle(),
            'description' => $image->getDescription(),
            'filename' => $image->getFilename(),
            'filepath' => $image->getFilepath(),
            'filesize' => $image->getFilesize(),
            'mime_type' => $image->getMimeType(),
            'width' => $image->getWidth(),
            'height' => $image->getHeight(),
            'alt_text' => $image->getAltText(),
            'caption' => $image->getCaption(),
            'display_order' => $image->getDisplayOrder(),
            'is_featured' => $image->getIsFeatured() ? 1 : 0,
        ];

        return $this->updateFields($image->getImageId(), $fieldsToUpdate);
    }

    /**
     * Map database row to Image entity.
     *
     * @param array<string, mixed> $imageData
     * @return Image
     */
    private function mapToEntity(array $imageData): Image
    {
        $image = new Image();

        $image->setImageId((int) $imageData['id']);
        $image->setImageStoreId((int) $imageData['store_id']);
        $image->setImageUserId((int) $imageData['user_id']);
        $image->setGalleryId($imageData['gallery_id'] !== null ? (int) $imageData['gallery_id'] : null);
        $image->setImageStatus($imageData['status']);
        $image->setTitle($imageData['title']);
        $image->setDescription($imageData['description']);
        $image->setFilename($imageData['filename']);
        $image->setFilepath($imageData['filepath']);
        $image->setFilesize((int) $imageData['filesize']);
        $image->setMimeType($imageData['mime_type']);
        $image->setWidth($imageData['width'] !== null ? (int) $imageData['width'] : null);
        $image->setHeight($imageData['height'] !== null ? (int) $imageData['height'] : null);
        $image->setAltText($imageData['alt_text']);
        $image->setCaption($imageData['caption']);
        $image->setDisplayOrder((int) $imageData['display_order']);
        $image->setIsFeatured((bool) $imageData['is_featured']);
        $image->setCreatedAt($imageData['created_at']);
        $image->setUpdatedAt($imageData['updated_at']);

        if (isset($imageData['username'])) {
            $image->setUsername($imageData['username']);
        }

        return $image;
    }

    /**
     * Convert an Image entity to an array with selected fields.
     *
     * @param Image $image
     * @param array<string> $fields
     * @return array<string, mixed>
     */
    public function toArray(Image $image, array $fields = []): array
    {
        $allFields = [
            'id' => $image->getImageId(),
            'store_id' => $image->getImageStoreId(),
            'user_id' => $image->getImageUserId(),
            'gallery_id' => $image->getGalleryId(),
            'status' => match ($image->getImageStatus()) {
                'A' => '☑️ Active',
                'P' => '⏳ Pending',
                'I' => '❌ Inactive',
                default => '❓ Unknown'
            },
            'title' => $image->getTitle(),
            'description' => $image->getDescription(),
            'filename' => $image->getFilename(),
            'filepath' => $image->getFilepath(),
            'filesize' => $image->getFilesize(),
            'filesize_human' => $image->getHumanReadableFilesize(),
            'mime_type' => $image->getMimeType(),
            'width' => $image->getWidth(),
            'height' => $image->getHeight(),
            'aspect_ratio' => $image->getAspectRatio(),
            'alt_text' => $image->getAltText(),
            'caption' => $image->getCaption(),
            'display_order' => $image->getDisplayOrder(),
            'is_featured' => $image->getIsFeatured(),
            'public_url' => $image->getPublicUrl(),
            'created_at' => $image->getCreatedAt(),
            'updated_at' => $image->getUpdatedAt(),
            'username' => $image->getUsername(),
        ];

        if (!empty($fields)) {
            return array_intersect_key($allFields, array_flip($fields));
        }

        return $allFields;
    }
}
