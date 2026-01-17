<?php

declare(strict_types=1);

namespace App\Features\Image;

/**
 * Interface for image cleanup operations.
 *
 * This service handles low-level cleanup operations for pending image uploads,
 * including temporary file deletion and database record removal.
 *
 * ⚠️ IMPORTANT: This service does NOT enforce business rules (validation, authorization).
 * Callers are responsible for authorization checks before invoking cleanup methods.
 */
interface ImageCleanupServiceInterface
{
    /**
     * Cleanup expired pending image uploads (automated cron job operation).
     *
     * This method:
     * - Queries the pending_image_upload table for records where expires_at < NOW()
     * - Deletes the temporary file from the filesystem
     * - Deletes the database record
     *
     * ✅ SAFE FOR AUTOMATED EXECUTION: No authorization checks needed (system-level cleanup)
     *
     * @return int Number of expired uploads cleaned up
     */
    public function cleanupExpiredPendingUploads(): int;

    /**
     * Cleanup a single pending image upload by upload token.
     *
     * This method:
     * - Finds the pending upload record by token
     * - Deletes the temporary file from the filesystem
     * - Deletes the database record
     *
     * ⚠️ CALLER RESPONSIBILITY: Validate ownership and authorization BEFORE calling this method.
     * This is a low-level operation that does NOT check if the upload token belongs to the current user.
     *
     * Example of proper usage:
     * ```php
     * // ✅ GOOD: Service layer validates ownership first
     * public function cancelPendingUpload(string $uploadToken, int $userId): void
     * {
     *     $pendingUpload = $this->pendingUploadRepository->findByUploadToken($uploadToken);
     *     if (!$pendingUpload || $pendingUpload->getUserId() !== $userId) {
     *         throw new ImageUploadException('Invalid or unauthorized upload token.');
     *     }
     *     // Only after validation, delegate to cleanup service
     *     $this->imageCleanupService->cleanupSinglePendingImage($uploadToken);
     * }
     * ```
     *
     * @param string $uploadToken UUID token for the pending upload
     * @return void
     */
    public function cleanupSinglePendingImage(string $uploadToken): void;
}