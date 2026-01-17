<?php

declare(strict_types=1);

namespace App\Features\Image;

use Core\Database\AbstractRepository;
use PDO;

/**
 * Repository implementation for pending image upload operations.
 *
 * Handles database operations for the `pending_image_upload` table.
 */
class PendingImageUploadRepository extends AbstractRepository implements PendingImageUploadRepositoryInterface
{
    protected string $table = 'pending_image_upload';
    protected string $entityClass = PendingImageUpload::class;

    /**
     * Find a pending upload record by its unique upload token.
     *
     * @param string $uploadToken UUID v4 token (36 characters)
     * @return PendingImageUpload|null Returns entity if found, null otherwise
     */
    public function findByUploadToken(string $uploadToken): ?PendingImageUpload
    {
        $sql = "SELECT * FROM {$this->table} WHERE upload_token = :upload_token LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':upload_token', $uploadToken, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    /**
     * Find a pending upload record by its primary key ID.
     *
     * @param int $id Primary key ID
     * @return PendingImageUpload|null Returns entity if found, null otherwise
     */
    public function findById(int $id): ?PendingImageUpload
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    /**
     * Find pending upload records matching the given criteria.
     *
     * @param array<string, mixed> $criteria Associative array of field => value conditions
     * @return array<int, PendingImageUpload> Array of matching entities
     */
    public function findBy(array $criteria): array
    {
        $conditions = [];
        $params = [];

        foreach ($criteria as $key => $value) {
            // Support operators in key (e.g., 'expires_at <')
            if (preg_match('/^(\w+)\s*([<>=!]+)$/', $key, $matches)) {
                $field = $matches[1];
                $operator = $matches[2];
                $conditions[] = "{$field} {$operator} :{$field}";
                $params[":{$field}"] = $value;
            } else {
                // Default to equality
                $conditions[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
        }

        $whereClause = implode(' AND ', $conditions);
        $sql = "SELECT * FROM {$this->table} WHERE {$whereClause}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => $this->hydrate($row), $rows);
    }

    /**
     * Insert a new pending upload record into the database.
     *
     * @param array<string, mixed> $data Associative array of column => value pairs
     * @return int The auto-generated ID of the inserted record
     * @throws \RuntimeException If insertion fails
     */
    public function insertFields(array $data): int
    {
        return $this->insert($data);
    }

    /**
     * Delete a pending upload record by its primary key ID.
     *
     * @param int $id Primary key ID of the record to delete
     * @return bool True if deleted successfully, false if record not found
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Hydrate a database row into a PendingImageUpload entity.
     *
     * @param array<string, mixed> $row Database row
     * @return PendingImageUpload
     */
    protected function hydrate(array $row): PendingImageUpload
    {
        $entity = new PendingImageUpload();
        $entity->setId((int)$row['id']);
        $entity->setUploadToken((string)$row['upload_token']);
        $entity->setStoreId((int)$row['store_id']);
        $entity->setUserId((int)$row['user_id']);
        $entity->setTempPath((string)$row['temp_path']);
        $entity->setOriginalFilename((string)$row['original_filename']);
        $entity->setClientMimeType((string)$row['client_mime_type']);
        $entity->setFileSizeBytes((int)$row['file_size_bytes']);
        $entity->setCreatedAt((string)$row['created_at']);
        $entity->setExpiresAt((string)$row['expires_at']);

        return $entity;
    }
}