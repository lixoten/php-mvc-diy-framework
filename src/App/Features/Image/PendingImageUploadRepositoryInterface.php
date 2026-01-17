<?php

declare(strict_types=1);

namespace App\Features\Image;

/**
 * Repository interface for pending image upload operations.
 *
 * Handles database operations for the `pending_image_upload` table,
 * which temporarily stores metadata about uploaded files during
 * the multi-step upload process.
 *
 * Records in this table are deleted after:
 * - Successful finalization (moved to `image` table)
 * - Manual cancellation by user
 * - Automatic expiry (cleaned up by cron job)
 */
interface PendingImageUploadRepositoryInterface
{
    /**
     * Find a pending upload record by its unique upload token.
     *
     * @param string $uploadToken UUID v4 token (36 characters)
     * @return PendingImageUpload|null Returns entity if found, null otherwise
     */
    public function findByUploadToken(string $uploadToken): ?PendingImageUpload;

    /**
     * Find a pending upload record by its primary key ID.
     *
     * @param int $id Primary key ID
     * @return PendingImageUpload|null Returns entity if found, null otherwise
     */
    public function findById(int $id): ?PendingImageUpload;

    /**
     * Find pending upload records matching the given criteria.
     *
     * Supported operators: '=', '<', '>', '<=', '>=', '!='
     *
     * Example usage:
     * ```php
     * // Find expired records
     * $expired = $repository->findBy(['expires_at <' => date('Y-m-d H:i:s')]);
     *
     * // Find uploads for specific user
     * $userUploads = $repository->findBy(['user_id' => 123]);
     *
     * // Exact match (implicit '=' operator)
     * $specific = $repository->findBy(['upload_token' => 'abc-123']);
     * ```
     *
     * @param array<string, mixed> $criteria Associative array of field => value conditions
     * @return array<int, PendingImageUpload> Array of matching entities (empty array if none found)
     */
    public function findBy(array $criteria): array;

    /**
     * Insert a new pending upload record into the database.
     *
     * Matches framework pattern from AbstractRepository.
     *
     * @param array<string, mixed> $data Associative array of column => value pairs
     * @return int The auto-generated ID of the inserted record
     * @throws \RuntimeException If insertion fails
     */
    public function insertFields(array $data): int;

    /**
     * Delete a pending upload record by its primary key ID.
     *
     * This method removes the database record ONLY.
     * The caller is responsible for deleting the associated temporary file
     * from the filesystem before calling this method.
     *
     * @param int $id Primary key ID of the record to delete
     * @return bool True if deleted successfully, false if record not found
     */
    public function delete(int $id): bool;
}
