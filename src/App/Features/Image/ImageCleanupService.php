<?php

declare(strict_types=1);

namespace App\Features\Image;

use Psr\Log\LoggerInterface;

/**
 * ✅ LOW-LEVEL SERVICE: Only handles physical cleanup operations.
 * Does NOT enforce business rules (validation, authorization).
 */
class ImageCleanupService implements ImageCleanupServiceInterface
{
    public function __construct(
        private PendingImageUploadRepositoryInterface $pendingUploadRepository, // ✅ ADDED
        private LoggerInterface $logger,
        private int $expiryThresholdMinutes = 60
    ) {}

    /**
     * ✅ AUTOMATED: Cleanup expired pending uploads (called by cron job).
     *
     * @return int Number of expired uploads cleaned up
     */
    public function cleanupExpiredPendingUploads(): int
    {
        $now = date('Y-m-d H:i:s');

        // ✅ Query pending_image_upload table for expired records
        $expiredUploads = $this->pendingUploadRepository->findBy([
            'expires_at <' => $now,
        ]);

        $cleanedCount = 0;
        foreach ($expiredUploads as $pendingUpload) {
            // ✅ Delete temp file
            if (file_exists($pendingUpload->getTempPath())) {
                @unlink($pendingUpload->getTempPath());
                $this->logger->info('Deleted temp file', [
                    'temp_path' => $pendingUpload->getTempPath(),
                ]);
            }

            // ✅ Delete database record
            $this->pendingUploadRepository->delete($pendingUpload->getId());
            $cleanedCount++;
        }

        $this->logger->info("Cleaned up {$cleanedCount} expired pending uploads.");
        return $cleanedCount;
    }

    /**
     * ✅ LOW-LEVEL: Delete temp file + database record.
     * ⚠️ DOES NOT validate ownership or authorization (caller must do this).
     *
     * @param string $uploadToken UUID token for pending upload
     * @return void
     */
    public function cleanupSinglePendingImage(string $uploadToken): void
    {
        // ✅ Find pending upload record
        $pendingUpload = $this->pendingUploadRepository->findByUploadToken($uploadToken);

        if (!$pendingUpload) {
            $this->logger->warning('Cleanup attempted on non-existent token', [
                'upload_token' => $uploadToken,
            ]);
            return;
        }

        // ✅ Delete temp file
        if (file_exists($pendingUpload->getTempPath())) {
            @unlink($pendingUpload->getTempPath());
            $this->logger->info('Deleted temp file', [
                'temp_path' => $pendingUpload->getTempPath(),
            ]);
        }

        // ✅ Delete database record
        $this->pendingUploadRepository->delete($pendingUpload->getId());
        $this->logger->info('Deleted pending upload record', [
            'upload_token' => $uploadToken,
        ]);
    }
}