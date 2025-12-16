<?php

declare(strict_types=1);

namespace Core\Services;

use Psr\Http\Message\UploadedFileInterface;

/**
 * Interface for image storage operations
 *
 * Handles image upload, preset generation, path resolution, and deletion.
 */
interface ImageStorageServiceInterface
{
    /**
     * Get public URL for an image hash, store, and preset.
     *
     * @param string $hash Image hash (e.g., 'a1b2c3d4e5')
     * @param int $storeId Store ID for multi-tenant path resolution
     * @param string $preset Preset name ('thumbs', 'web', 'original')
     * @param string|null $extension Optional explicit extension (e.g., 'jpg', 'webp'). If null, implementation decides.
     * @return string Public URL (e.g., '/store/555/images/thumbs/a1b2c3d4e5.jpg')
     */
    public function getUrl(string $hash, int $storeId, string $preset = 'web', ?string $extension = null): string;

    /**
     * Get filesystem path for image storage operations.
     *
     * @param string $hash Image hash
     * @param int $storeId Store ID
     * @param string $preset Preset name ('thumbs', 'web', 'original')
     * @param string|null $extension Optional explicit extension. If null, implementation decides.
     * @return string Absolute filesystem path
     */
    public function getPath(string $hash, int $storeId, string $preset = 'web', ?string $extension = null): string;


    /**
     * Upload and process an image file.
     * Returns an array containing the generated hash and the determined extension.
     *
     * @param UploadedFileInterface $file Uploaded file (PSR-7)
     * @param int $storeId Target store ID
     * @return array<string, string> An associative array with 'hash' and 'extension'
     * @throws \RuntimeException If upload fails or file processing encounters an error.
     */
    public function upload(UploadedFileInterface $file, int $storeId): array;

    /**
     * Return associative array of available extensions => public URL for a given image/preset.
     *
     * Example: ['avif' => '/store/1/images/full/abc.avif', 'webp' => '/store/1/images/full/abc.webp', 'jpg' => '/store/1/images/full/abc.jpg']
     *
     * If no files are found on disk for the given hash/preset, the method MUST return an empty array.
     *
     * @param string $hash
     * @param int $storeId
     * @param string $preset
     * @return array<string,string> Empty array when no files found.
     */
    public function getUrls(string $hash, int $storeId, string $preset = 'web'): array;


    /**
     * Delete an image and all its associated presets.
     *
     * @param string $hash Image hash to delete
     * @param int $storeId Store ID
     * @param string|null $extension
     * @return bool True if at least one file was deleted successfully, false otherwise.
     */
    public function delete(string $hash, int $storeId, ?string $extension = null): bool;


    /**
     * Check if an image preset exists.
     *
     * @param string $hash Image hash
     * @param int $storeId Store ID
     * @param string $preset Preset to check ('thumbs', 'web', 'original')
     * @param string|null $extension
     * @return bool True if the image preset exists at the given path.
     */
    public function exists(string $hash, int $storeId, string $preset = 'web', ?string $extension = null): bool;
}
