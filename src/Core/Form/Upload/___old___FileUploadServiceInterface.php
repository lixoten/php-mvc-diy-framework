<?php

declare(strict_types=1);

namespace Core\Form\Upload;

use Core\Form\Field\FieldInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * File Upload Service Interface
 *
 * Defines the contract for handling file uploads in forms with hash-based storage.
 *
 * This service is responsible for:
 * - Validating uploaded files (size, MIME type)
 * - Generating content-based SHA-256 hashes for deduplication
 * - Delegating file storage to StorageProviderInterface
 * - Returning file metadata for database persistence
 *
 * Design Principles:
 * - Single Responsibility: ONLY handles file upload mechanics
 * - Dependency Inversion: Depends on StorageProviderInterface abstraction
 * - Open/Closed: Storage backend can be swapped without changing this interface
 *
 * @package Core\Form\Upload
 */
interface FileUploadServiceInterface
{
    /**
     * Handle multiple file uploads from a form submission.
     *
     * This method processes all uploaded files from a PSR-7 request, validates them
     * against field-specific upload rules, generates content-based hashes, and
     * delegates storage to the configured StorageProviderInterface.
     *
     * Supports both single and multiple file uploads per field:
     * - Single: $uploadedFiles['profile_picture'] => UploadedFileInterface
     * - Multiple: $uploadedFiles['product_images'] => [UploadedFileInterface, ...]
     *
     * File metadata is returned in one of two formats:
     * - Single file: ['field_name' => ['key' => '...', 'hash' => '...', ...]]
     * - Multiple files: ['field_name' => [['key' => '...', ...], ['key' => '...', ...]]]
     *
     * @param array<string, UploadedFileInterface|array<UploadedFileInterface>> $uploadedFiles
     *        Uploaded files from PSR-7 request (via $request->getUploadedFiles())
     *
     * @param array<string, FieldInterface> $fieldDefinitions
     *        Form field definitions containing upload rules (from FormInterface::getFields())
     *        Expected structure per field:
     *        ```php
     *        [
     *            'profile_picture' => FieldInterface {
     *                getOptions() => [
     *                    'upload' => [
     *                        'max_size' => 2097152,           // Max file size in bytes (default: 5MB)
     *                        'mime_types' => ['image/jpeg', 'image/png'], // Allowed MIME types
     *                        'subdir' => 'profiles',          // Storage subdirectory (default: 'uploads/originals')
     *                    ]
     *                ]
     *            }
     *        ]
     *        ```
     *
     * @return array<string, array<string, mixed>|array<array<string, mixed>>>
     *         File metadata for database storage, keyed by field name.
     *
     *         Single file return structure:
     *         ```php
     *         [
     *             'profile_picture' => [
     *                 'key' => 'uploads/originals/abc123def456...xyz.jpg', // Storage key (path)
     *                 'original_name' => 'my-photo.jpg',                   // Original filename
     *                 'mime' => 'image/jpeg',                              // Detected MIME type (via finfo)
     *                 'size' => 524288,                                    // File size in bytes
     *                 'hash' => 'abc123def456...xyz',                      // SHA-256 content hash
     *             ]
     *         ]
     *         ```
     *
     *         Multiple files return structure:
     *         ```php
     *         [
     *             'product_images' => [
     *                 ['key' => 'uploads/originals/...jpg', 'hash' => '...', ...],
     *                 ['key' => 'uploads/originals/...png', 'hash' => '...', ...],
     *             ]
     *         ]
     *         ```
     *
     * @throws FileUploadException If a critical upload error occurs (e.g., storage failure)
     *
     * @example
     * ```php
     * // In FormHandler::handle()
     * $uploadedFiles = $request->getUploadedFiles();
     * $fieldDefinitions = $form->getFields();
     *
     * $fileMetadata = $this->fileUploadService->handleFiles($uploadedFiles, $fieldDefinitions);
     *
     * // Result:
     * // [
     * //     'profile_picture' => ['key' => 'uploads/originals/abc123.jpg', 'hash' => 'abc123...', ...],
     * //     'contract_pdf' => ['key' => 'uploads/originals/def456.pdf', 'hash' => 'def456...', ...],
     * // ]
     * ```
     */
    public function handleFiles(array $uploadedFiles, array $fieldDefinitions): array;
}
