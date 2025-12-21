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
            $image->setStatus(ImageStatus::from($formData['status'])); // Should default to PENDING or similar for new records
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

            // ✅ Image-specific validation (MIME type check)
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
            if (!in_array($mimeType, $allowedMimeTypes, true)) {
                $this->logger->warning("Attempted upload of disallowed MIME type: {$mimeType}", ['temp_path' => $tempPath]);
                throw new ImageUploadException("Only JPG, PNG, GIF, WebP, AVIF images are allowed. Provided: {$mimeType}");
            }

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
     * Processes form data to create or update an Image record in the database,
     * incorporating metadata from file uploads. This method encapsulates the business rule
     * of updating all file-specific fields upon image upload.
     *
     * @param array<string, mixed> $formData The sanitized and normalized form data,
     *                                       including '_image_metadata_{fieldName}' keys.
     * @param int|null $imageId The ID of the image to update, or null to create a new one.
     * @return int The ID of the created or updated image.
     * @throws NotFoundException If imageId is provided but the image does not exist.
     */
    public function oldprocessImageData(array $formData, ?int $imageId = null): int
    {
        $image = null;
        $now = date('Y-m-d H:i:s'); // Use a consistent timestamp for DB

        if ($imageId !== null) {
            $image = $this->imageRepository->findById($imageId);
            if (!$image) {
                throw new NotFoundException("Image with ID {$imageId} not found.");
            }
            $image->setUpdatedAt($now); // ✅ Update timestamp for existing record
        } else {
            // Create a new Image entity
            $image = new Image();
            // Default status for new image, can be PENDING if no file uploaded yet
            // $image->setStatus(ImageStatus::PENDING->value);
            $image->setStatus(ImageStatus::from($formData['status']));

            // Set initial user_id and store_id from form data or context
            $image->setUserId($formData['user_id'] ?? null);
            $image->setStoreId($formData['store_id'] ?? null);
        }

        // Apply scalar fields from form data
        $image->setTitle($formData['title'] ?? $image->getTitle());
        $image->setSlug($formData['slug'] ?? $image->getSlug());
        $image->setDescription($formData['description'] ?? $image->getDescription());
        $image->setAltText($formData['alt_text'] ?? $image->getAltText());
        $image->setLicense($formData['license'] ?? $image->getLicense());

        // Focal point is optional, can be set from form data if available
        // $image->setFocalPoint($formData['focal_point'] ?? null);
        // ✅ Handle focal_point: Ensure it's a JSON string or null if provided as array
        if (isset($formData['focal_point'])) {
            $focalPoint = is_array($formData['focal_point']) ? json_encode($formData['focal_point']) : $formData['focal_point'];
            $image->setFocalPoint($focalPoint);
        } else {
            $image->setFocalPoint(null); // Ensure it's null if not provided
        }

        // --- Handle uploaded image metadata (your core business rule) ---
        // Assume 'filename' is the primary field for the image file
        if (isset($formData['image_metadata'])) {
            $uploadedMetadata = $formData['image_metadata'];

            $image->setFilename($uploadedMetadata['hash']);
            $image->setOriginalFilename($uploadedMetadata['original_filename']);
            $image->setMimeType($uploadedMetadata['mime_type']);
            $image->setFileSizeBytes($uploadedMetadata['file_size_bytes']);
            $image->setWidth($uploadedMetadata['width']);
            $image->setHeight($uploadedMetadata['height']);
            $image->setIsOptimized(true); // Assuming file upload implies optimization
            // $image->setStatus(ImageStatus::ACTIVE->value); // Mark as active after file data is present
            $image->setChecksum($formData['checksum'] ?? null); // If provided by form/upload
        } else {
            // If no new file was uploaded, check if the record is still pending
            // This ensures file-dependent fields are null only if no file has ever been attached.
            // if ($image->getStatus() === ImageStatus::PENDING->value) {
            //     $image->setFilename(null);
            //     $image->setOriginalFilename(null);
            //     $image->setMimeType(null);
            //     $image->setFileSizeBytes(null); // Set to null if your DB field is nullable
            //     $image->setWidth(null); // Set to null if your DB field is nullable
            //     $image->setHeight(null); // Set to null if your DB field is nullable
            //     $image->setIsOptimized(false);
            //     $image->setChecksum(null);
            // }
            // If status is ACTIVE and no new upload, retain existing file info and do not nullify
        }


        // Convert entity to array for persistence
        $imageData = $image->toArray();

        if ($imageId !== null) {
            // Use updateFields() with the entity's ID and data
            unset($imageData['id']);

            $this->imageRepository->updateFields($imageId, $imageData);
            return $imageId;
        // } else {
        //     // Use insertFields() to create a new record
        //     $newId = $this->imageRepository->insertFields($imageData);
        //     return $newId;
        // }
        } else {
            // ✅ For create, 'id' should not be in the array, as it's auto-incrementing.
            unset($imageData['id']);
            $newId = $this->imageRepository->insertFields($imageData);
            $image->setId($newId); // Set ID on entity for consistency
            return $newId;
        }



        // if ($imageId !== null) {
        //     $this->imageRepository->update($image);
        // } else {
        //     $this->imageRepository->create($image);
        // }

        // if ($id) {
        //     // Update existing record
        //     return $this->imageRepository->updateFields($id, $formData) ? $id : null;
        // } else {
        //     // Create new record
        //     return $this->imageRepository->insertFields($formData);
        // }

        return $image->getId();
    }



//     /**
//      * Create a new image
//      */
//     public function createImage(array $data): Image
//     {
//         $image = new Image();
//         $image->setTitle($data['title']);
//         // $image->setEmail($data['email']);
//         // $image->setPasswordHash(password_hash($data['password'], PASSWORD_DEFAULT));
//         // $image->setRoles(['image']);
//         $image->setStatus(ImageStatus::PENDING);

//         // Generate activation token
//         // $tokenData = $this->tokenService->generateWithExpiry(24 * 3600); // 24 hours
//         // $image->setActivationToken($tokenData['token']);
//         // $image->setActivationTokenExpiry($tokenData['expires_at']);

//         // Save image to repository
//         return $this->imageRepository->create($image);
//     }

//     /** used
//      * Fetch a image by ID
//      */
//     public function getAllImagesWithFields($listFields, $sortField, $sortDirection, $limit, $offset): array
//     {
//         $rrr = $this->imageRepository->findAllWithFields(
//             $listFields,
//             [$sortField => $sortDirection],
//             $limit,
//             $offset
//         );
//         return $rrr;
//     }

//     /** used
//      * Fetch a image by ID
//      */
//     public function countAllImages(): int
//     {
//         $rrr = $this->imageRepository->countAll();
//         return $rrr;
//     }


//     /** used
//      * Fetch a image by ID
//      */
//     public function countByUserId(int $userId): int
//     {
//         $rrr = $this->imageRepository->countByUserId($userId);
//         return $rrr;
//     }

//     /** used
//      * Fetch a image by ID
//      */
//     public function countByStoreId(int $storeId): int
//     {
//         $rrr = $this->imageRepository->countByStoreId($storeId);
//         return $rrr;
//     }


//     /**
//      * Fetch a image by ID
//      */
//     public function getImageByUserIdWithFields(int $userId, array $fields): ?array
//     {
//         return $this->imageRepository->findByUserIdWithFields($userId, $fields);
//     }

//     /**
//      * Fetch a image by ID
//      */
//     public function getImageByStoreIdWithFields(int $storeId, array $fields): ?array
//     {
//         return $this->imageRepository->findByStoreIdWithFields($storeId, $fields);
//     }

//     /**
//      * Fetch a image by ID
//      */
//     public function getImageByIdWithFields(int $imageId, array $fields): ?array
//     {
//         return $this->imageRepository->findByIdWithFields($imageId, $fields);
//     }

//     /**
//      * Fetch a image by ID
//      */
//     public function getImageById(int $imageId): ?Image
//     {
//         return $this->imageRepository->findById($imageId);
//     }

//     /**
//      * Fetch a image by imagename
//      */
//     public function getImageByImagename(string $imagename): ?Image
//     {
//         return $this->imageRepository->findByImagename($imagename);
//     }

//     /**
//      * Fetch a image by email
//      */
//     public function getImageByEmail(string $email): ?Image
//     {
//         return $this->imageRepository->findByEmail($email);
//     }

//     /**
//      * Fetch a image by activation token
//      */
//     public function getImageByActivationToken(string $token): ?Image
//     {
//         return $this->imageRepository->findByActivationToken($token);
//     }

//     /**
//      * Fetch a image by reset token
//      */
//     public function getImageByResetToken(string $token): ?Image
//     {
//         return $this->imageRepository->findByResetToken($token);
//     }

//     /**
//      * Update an existing image
//      */
//     public function updateImage(Image $image): bool
//     {
//         return $this->imageRepository->update($image);
//     }

//     /**
//      * Update an existing image
//      */
//     public function updateImageWithFields(int $imageId, array $data): bool
//     {
//         return $this->imageRepository->updateFields($imageId, $data);
//         // return $this->imageRepository->update($image);
//     }


//    /**
//      * Update an existing image
//      */
//     public function insertFields(array $data): int
//     {
//         return $this->imageRepository->insertFields($data);
//         // return $this->imageRepository->update($image);
//     }

//     /**
//      * Delete a image by ID
//      */
//     public function deleteImage(int $imageId): bool
//     {
//         return $this->imageRepository->delete($imageId);
//     }
}
