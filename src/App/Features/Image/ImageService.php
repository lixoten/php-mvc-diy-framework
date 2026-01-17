<?php

declare(strict_types=1);

// namespace App\Services;
// namespace App\Services;
namespace App\Features\Image;

// use App\Entities\Image;

use App\Enums\ImageStatus;
use App\Features\Image\Exceptions\DuplicateImageContentException;
use App\Features\Image\Exceptions\ImageRecordImmutableException;
use App\Features\Image\Exceptions\ImageUploadException;
use App\Features\Image\Image;
// use App\Repository\ImageRepositoryInterface;
use App\Features\Image\ImageRepositoryInterface;
use Core\Security\TokenServiceInterface;
use Core\Services\ImageStorageServiceInterface;
// use Core\Services\DataTransformerService;
use DI\NotFoundException;
use Psr\Log\LoggerInterface;

class ImageService
{
    private ImageRepositoryInterface $imageRepository;
    private ImageStorageServiceInterface $imageStorageService;
    private LoggerInterface $logger;
    // private DataTransformerService $dataTransformer;

    public function __construct(
        ImageRepositoryInterface $imageRepository,
        ImageStorageServiceInterface $imageStorageService,
        LoggerInterface $logger
        // DataTransformerService $dataTransformer
    ) {
        $this->imageRepository = $imageRepository;
        $this->imageStorageService = $imageStorageService;
        $this->logger = $logger;
        // $this->dataTransformer = $dataTransformer;
    }

    /**
     * Processes form data to create or update an Image record in the database,
     * incorporating metadata from file uploads. This method encapsulates the business rule
     * of updating all file-specific fields upon image upload.
     *
     * @param array<string, mixed> $formData The sanitized and normalized form data,
     *                                       including '_uploaded_file_temp_info' keys.
     * @param int|null $imageId The ID of the image to update, or null to create a new one.
     * @return int The ID of the created or updated image.
     * @throws NotFoundException If imageId is provided but the image does not exist.
     * @throws ImageUploadException If there's a problem with the image file itself (e.g., bad MIME type).
     * @throws DuplicateImageContentException If attempting to create a new image record with content that already
     *                                        exists for another record.
     * @throws ImageRecordImmutableException If attempting to change the file of an existing image record.
     */
    public function processImageData(array $formData, int $currentStoreId, ?int $imageId = null): int
    {
        $image = null;
        $now = date('Y-m-d H:i:s');

        if ($imageId !== null) {
            $image = $this->imageRepository->findById($imageId);
            if (!$image) {
                throw new NotFoundException("Image with ID {$imageId} not found.");
            }
            $image->setUpdatedAt($now);
        } else {
            $image = new Image();
            // $image->setStatus(ImageStatus::from($formData['status'])); // Should default to PENDING or similar for new records
            $image->setUserId($formData['user_id'] ?? null);
            $image->setStoreId($formData['store_id'] ?? null);
        }

        // Apply scalar fields from form data
        $image->setTitle($formData['title'] ?? $image->getTitle());
        $image->setSlug($formData['slug'] ?? $image->getSlug());
        $image->setDescription($formData['description'] ?? $image->getDescription());
        $image->setAltText($formData['alt_text'] ?? $image->getAltText());
        $image->setLicense($formData['license'] ?? $image->getLicense());

        // Handle focal_point: Ensure it's a JSON string or null if provided as array
        if (isset($formData['focal_point'])) {
            $focalPoint = is_array($formData['focal_point']) ? json_encode($formData['focal_point']) : (string) $formData['focal_point'];
            $image->setFocalPoint($focalPoint);
        } else {
            $image->setFocalPoint(null);
        }

        // --- Handle uploaded image file from temporary storage ---
        // Assuming 'image_field' is the name of your file input in the form
        $imageFieldName = 'filename';
        $uploadedFileTempInfo = $formData['_uploaded_file_temp_info'][$imageFieldName] ?? null;

        // ✅ NEW: Robust handling of temporary file info
        if ($uploadedFileTempInfo === null || $uploadedFileTempInfo['status'] === 'no_file') {
            // Case 1: No file was uploaded for this field, or temp info is entirely missing.
            // This is problematic if we're trying to CREATE a new image record.
            if ($imageId === null) {
                $this->logger->warning("ImageService: No image file provided for new image record creation on field '{$imageFieldName}'.", ['formData' => $formData]);
                throw new ImageUploadException("An image file is required to create a new image record.");
            }
            // Case 2: For an existing image record update, it's valid to not upload a new file.
            // If existing record has a filename, we retain it. If no file has ever been attached,
            // we leave filename/related fields as null.
            $this->logger->debug("ImageService: No new image file provided for update of Image ID {$imageId} on field '{$imageFieldName}'. Skipping file processing.", ['formData' => $formData]);
        } elseif ($uploadedFileTempInfo['status'] === 'failed') {
            // Case 3: Temporary file upload failed (error from FormHandler/TemporaryFileUploadService).
            $this->logger->warning("Temporary file upload failed for field '{$imageFieldName}': {$uploadedFileTempInfo['error_message']}", $uploadedFileTempInfo);
            throw new ImageUploadException("Temporary file upload failed for '{$imageFieldName}': " . $uploadedFileTempInfo['error_message']);
        } elseif ($uploadedFileTempInfo && $uploadedFileTempInfo['status'] === 'success') {
            // ✅ BUSINESS RULE: Immutable Image Records
            if ($imageId !== null && $image->getFilename() !== null) {
                // If this is an update and the record already has a file, prevent changing it
                throw new ImageRecordImmutableException();
            }

            $tempPath = (string) $uploadedFileTempInfo['temporary_path'];
            $originalFilename = (string) $uploadedFileTempInfo['original_filename'];
            $mimeType = (string) $uploadedFileTempInfo['mime_type'];
            // $fileSizeBytes = (int) $uploadedFileTempInfo['size_bytes'];
            //$storeId = (int) ($formData['store_id'] ?? 0); // Ensure store_id is available from form data or context
            $storeId = $currentStoreId;

            // if (!$storeId) {
            //     $this->logger->error('ImageService: store_id is missing during image upload processing.');
            //     throw new ImageUploadException('Store ID is required for image upload.');
            // }

            // // ✅ Image-specific validation (MIME type check)
            // $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
            // if (!in_array($mimeType, $allowedMimeTypes, true)) {
            //     $this->logger->warning("Attempted upload of disallowed MIME type: {$mimeType}", ['temp_path' => $tempPath]);
            //     throw new ImageUploadException("Only JPG, PNG, GIF, WebP, AVIF images are allowed. Provided: {$mimeType}");
            // }

            try {
                // ✅ Delegate to ImageStorageService for permanent storage and processing
                // ImageStorageService will calculate hash, move from temp, generate presets, and delete temp file.
                $imageMetadata = $this->imageStorageService->uploadFromTemporary(
                    $tempPath,
                    $storeId,
                    $mimeType,
                    $originalFilename
                );

                // ✅ BUSINESS RULE: Prevent duplicate image content for NEW records
                // If creating a new record AND the uploaded file's hash already exists in the 'image' table
                if ($imageId === null) {
                    $existingRecordWithHash = $this->imageRepository->findByFilename($imageMetadata['hash']);
                    if ($existingRecordWithHash !== null) {
                        // Clean up the newly stored physical files if we're rejecting the record
                        $this->imageStorageService->delete($imageMetadata['hash'], $storeId, $imageMetadata['extension']);
                        throw new DuplicateImageContentException(
                            $imageMetadata['hash'],
                            $existingRecordWithHash->getId(),
                            "This image content already exists in the system as Image ID {$existingRecordWithHash->getId()}."
                        );
                    }
                }

                // Update Image entity with metadata from storage service
                $image->setFilename($imageMetadata['hash']);
                $image->setOriginalFilename($imageMetadata['original_filename']);
                $image->setMimeType($imageMetadata['mime_type']);
                $image->setFileSizeBytes($imageMetadata['file_size_bytes']);
                $image->setWidth($imageMetadata['width']);
                $image->setHeight($imageMetadata['height']);
                $image->setIsOptimized(true); // Assuming file upload implies optimization
                //$image->setStatus(ImageStatus::ACTIVE->value); // Mark as active after file data is present
                // $image->setChecksum($imageMetadata['checksum'] ?? null); // Add if ImageStorageService returns checksum
            } catch (\Throwable $e) {
                $this->logger->error("Error processing image from temporary storage: {$e->getMessage()}", ['exception' => $e, 'temp_path' => $tempPath]);
                // Re-throw as a generic ImageUploadException or a more specific one if needed
                throw new ImageUploadException("Failed to finalize image upload: " . $e->getMessage(), $e->getCode(), $e);
            }
        }


        // Convert entity to array for persistence
        $imageData = $image->toArray();

        if ($imageId !== null) {
            unset($imageData['id']); // Ensure ID is not in data for update
            $this->imageRepository->updateFields($imageId, $imageData);
            return $imageId;
        } else {
            unset($imageData['id']); // Ensure ID is not in data for create
            $newId = $this->imageRepository->insertFields($imageData);
            $image->setId($newId);
            return $newId;
        }
    }




    /**
     * ✅ STEP 2: Finalize image record and delete pending upload entry.
     *
     * @param string $uploadToken
     * @param array<string, mixed> $metadataFormData
     * @param int $currentStoreId
     * @return int Image ID
     * @throws ImageUploadException
     */
    public function finalizeImageRecord(string $uploadToken, array $metadataFormData, int $currentStoreId): int
    {
        // ✅ Find pending upload record
        $pendingUpload = $this->pendingUploadRepository->findByUploadToken($uploadToken);

        if (!$pendingUpload) {
            throw new ImageUploadException('Invalid or expired upload token.');
        }

        // ✅ Verify ownership
        if ($pendingUpload->getStoreId() !== $currentStoreId) {
            throw new ImageUploadException('Store ID mismatch.');
        }

        // ✅ Check expiry
        if ($pendingUpload->isExpired()) {
            throw new ImageUploadException('Upload token has expired.');
        }

        // ✅ Move file to permanent storage
        $tempPath = $pendingUpload->getTempPath();
        if (!file_exists($tempPath)) {
            throw new ImageUploadException('Temporary file not found.');
        }

        try {
            $imageMetadata = $this->imageStorageService->uploadFromTemporary(
                $tempPath,
                $currentStoreId,
                $pendingUpload->getClientMimeType(),
                $pendingUpload->getOriginalFilename()
            );

            // ✅ Create final image record in main `image` table
            $image = new Image();
            $image->setStoreId($currentStoreId);
            $image->setUserId($pendingUpload->getUserId());
            $image->setStatus(ImageStatus::ACTIVE);
            $image->setTitle($metadataFormData['title'] ?? '');
            $image->setSlug($metadataFormData['slug'] ?? '');
            $image->setDescription($metadataFormData['description'] ?? '');
            $image->setAltText($metadataFormData['alt_text'] ?? null);
            $image->setFilename($imageMetadata['hash']);
            $image->setOriginalFilename($pendingUpload->getOriginalFilename());
            $image->setMimeType($imageMetadata['mime_type']);
            $image->setFileSizeBytes($imageMetadata['file_size_bytes']);
            $image->setWidth($imageMetadata['width']);
            $image->setHeight($imageMetadata['height']);
            $image->setChecksum($imageMetadata['checksum']);
            $image->setCreatedAt(date('Y-m-d H:i:s'));
            $image->setUpdatedAt(date('Y-m-d H:i:s'));

            $imageData = $image->toArray();
            unset($imageData['id']);
            $imageId = $this->imageRepository->insertFields($imageData);

            // ✅ Delete pending upload record (cleanup)
            $this->pendingUploadRepository->delete($pendingUpload->getId());

            $this->logger->info('Image finalized', [
                'image_id' => $imageId,
                'upload_token' => $uploadToken,
            ]);

            return $imageId;
        } catch (\Throwable $e) {
            $this->logger->error('Failed to finalize image', [
                'upload_token' => $uploadToken,
                'error' => $e->getMessage(),
            ]);
            throw new ImageUploadException('Failed to finalize image upload.', 0, $e);
        }
    }

    /**
     * ✅ STEP 1: Create pending upload record in separate tracking table.
     *
     * @param array<string, mixed> $tempFileMetadata
     * @param int $userId
     * @param int $storeId
     * @return array{upload_token: string, pending_id: int}
     * @throws ImageUploadException
     */
    public function createPendingImageRecord(array $tempFileMetadata, int $userId, int $storeId): array
    {
        if (!isset($tempFileMetadata['temporary_path'], $tempFileMetadata['original_filename'])) {
            throw new ImageUploadException('Invalid temporary file metadata.');
        }

        // ✅ Generate UUID token
        $uploadToken = $this->generateUploadToken();

        // ✅ Create record in pending_image_upload table (NOT main image table)
        $pendingUpload = new PendingImageUpload();
        $pendingUpload->setUploadToken($uploadToken);
        $pendingUpload->setStoreId($storeId);
        $pendingUpload->setUserId($userId);
        $pendingUpload->setTempPath($tempFileMetadata['temporary_path']);
        $pendingUpload->setOriginalFilename($tempFileMetadata['original_filename']);
        $pendingUpload->setClientMimeType($tempFileMetadata['client_mime_type']);
        $pendingUpload->setFileSizeBytes($tempFileMetadata['size_bytes']);
        $pendingUpload->setCreatedAt(date('Y-m-d H:i:s'));
        $pendingUpload->setExpiresAt(date('Y-m-d H:i:s', strtotime('+1 hour'))); // ✅ Auto-expire

        $pendingData = $pendingUpload->toArray();
        unset($pendingData['id']);
        $pendingId = $this->pendingUploadRepository->insertFields($pendingData);

        $this->logger->info('Pending upload record created', [
            'upload_token' => $uploadToken,
            'pending_id' => $pendingId,
        ]);

        return [
            'upload_token' => $uploadToken,
            'pending_id' => $pendingId,
        ];
    }



    /**
     * ✅ HIGH-LEVEL: Cancel user's pending upload with validation.
     *
     * @throws ImageUploadException If token invalid or not owned by user
     */
    public function cancelPendingUpload(string $uploadToken, int $userId, int $storeId): void
    {
        // ✅ Validate token belongs to current user/session
        $image = $this->imageRepository->findByUploadToken($uploadToken);

        if (!$image) {
            $this->logger->error('Invalid upload token', ['upload_token' => $uploadToken]);
            throw new ImageUploadException('Invalid or expired upload token.');
        }

        // ✅ Verify ownership
        if ($image->getUserId() !== $userId || $image->getStoreId() !== $storeId) {
            $this->logger->error('User attempted to cancel another user\'s upload', [
                'upload_token' => $uploadToken,
                'expected_user_id' => $userId,
                'actual_user_id' => $image->getUserId(),
            ]);
            throw new ImageUploadException('You do not have permission to cancel this upload.');
        }

        // ✅ Verify image is still pending
        if ($image->getStatus() !== 'P') {
            $this->logger->error('Attempted to cancel non-pending image', [
                'upload_token' => $uploadToken,
                'status' => $image->getStatus(),
            ]);
            throw new ImageUploadException('This upload cannot be cancelled (already processed).');
        }

        // ✅ Delegate to cleanup service (low-level operation)
        $this->imageCleanupService->cleanupSinglePendingImage($uploadToken);

        $this->logger->info('User cancelled pending upload', [
            'upload_token' => $uploadToken,
            'user_id' => $userId,
        ]);
    }

}
