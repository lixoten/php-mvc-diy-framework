<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Interfaces\ConfigInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use App\Features\Image\Exceptions\ImageUploadException; // ✅ NEW: For image specific upload errors

/**
 * Concrete implementation for image storage operations.
 *
 * Handles image upload, preset generation (simulated), path resolution, and deletion.
 * Uses a multi-tenant file structure:
 * - Public: {PUBLIC_HTML_ROOT}/store/{storeId}/images/{preset}/{hash}.[ext]
 * - Private (Originals): {STORAGE_ROOT}/store/{storeId}/originals/{hash}.[ext]
 *
 * @package Core\Services
 */
class ImageStorageService implements ImageStorageServiceInterface
{
    private string $publicHtmlRoot;
    private string $storageRoot;
    private string $publicBaseUrl; // e.g., /store
    private array $presets;

    /**
     * Simple per-request cache for getUrls results to avoid repeated disk checks.
     *
     * @var array<string, array<string,string>>
     */
    private array $getUrlsCache = [];

    /**
     * @param ConfigInterface $configService Configuration service
     * @param LoggerInterface $logger Logger instance
     */
    public function __construct(
        private ConfigInterface $configService,
        private LoggerInterface $logger,
        private ImageProcessingService $imageProcessingService,
        string $publicHtmlRoot,
        string $storageRoot
    ) {
        $this->publicHtmlRoot = $publicHtmlRoot;
        $this->storageRoot    = $storageRoot;

        $this->publicBaseUrl  = $this->configService->get('storage.multi_tenant_images.public_base_url');

        $this->presets = $this->configService->get('storage.image_presets', [
            'thumbs' => ['width' => 150, 'height' => 150, 'quality' => 85, 'crop' => true],
            'web' => ['width' => 800, 'height' => null, 'quality' => 90, 'crop' => false],
        ]);

        if (!is_dir($this->publicHtmlRoot)) {
            throw new RuntimeException(
                "Public HTML root does not exist: {$this->publicHtmlRoot}. " .
                "This directory must exist and be writable. Create it manually or check your deployment."
            );
        }

        if (!is_writable($this->publicHtmlRoot)) {
            throw new RuntimeException(
                "Public HTML root is not writable: {$this->publicHtmlRoot}. " .
                "Check directory permissions (recommended: 0755 owned by web server user)."
            );
        }

        if (!is_dir($this->storageRoot)) {
            if (!mkdir($this->storageRoot, 0755, true)) {
                throw new RuntimeException(
                    "Failed to create storage root: {$this->storageRoot}. " .
                    "Check parent directory permissions."
                );
            }
            $this->logger->info("Created storage root directory: {$this->storageRoot}");
        }

        if (!is_writable($this->storageRoot)) {
            throw new RuntimeException(
                "Storage root is not writable: {$this->storageRoot}. " .
                "Check directory permissions (recommended: 0755 owned by web server user)."
            );
        }
    }

    /** {@inheritdoc} */
    public function getUrl(string $hash, int $storeId, string $preset = 'web', ?string $extension = null): string
    {
        if (empty($hash)) {
            return '';
        }

        $finalExtension = $this->determineFinalExtension($extension);

        return sprintf(
            '%s/%d/images/%s/%s.%s',
            rtrim($this->publicBaseUrl, '/'),
            $storeId,
            $preset,
            $hash,
            $finalExtension
        );
    }

    /** {@inheritdoc} */
    public function getPath(string $hash, int $storeId, string $preset = 'web', ?string $extension = null): string
    {
        // Build the path (create directory only when needed for writes)
        $path = $this->buildFilePath($hash, $storeId, $preset, $extension);

        $dir = dirname($path);
        // ✅ Ensure directory creation uses 0775 permissions as per best practice
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        return $path;
    }

    private function buildFilePath(string $hash, int $storeId, string $preset = 'web', ?string $extension = null): string
    {
        $finalExtension = $this->determineFinalExtension($extension);

        if ($preset === 'original') {
            return sprintf(
                '%s/store/%d/originals/%s.%s',
                rtrim($this->storageRoot, '/'),
                $storeId,
                $hash,
                $finalExtension
            );
        }

        return sprintf(
            '%s/store/%d/images/%s/%s.%s',
            rtrim($this->publicHtmlRoot, '/'),
            $storeId,
            $preset,
            $hash,
            $finalExtension
        );
    }

    /** {@inheritdoc} */
    public function getUrls(string $hash, int $storeId, string $preset = 'web'): array
    {
        $cacheKey = implode('|', [$storeId, $preset, $hash]);
        if (isset($this->getUrlsCache[$cacheKey])) {
            return $this->getUrlsCache[$cacheKey];
        }

        $preferred = $this->configService->get('storage.image_generation.preferred_formats')
            ?? ['avif', 'webp', 'jpg'];

        $found = [];
        foreach ($preferred as $ext) {
            $path = $this->buildFilePath($hash, $storeId, $preset, $ext);
            if (file_exists($path)) {
                $found[$ext] = $this->getUrl($hash, $storeId, $preset, $ext);
            }
        }

        $this->getUrlsCache[$cacheKey] = $found;
        return $found;
    }

    /**
     * @deprecated Use `uploadFromTemporary` instead. This method is replaced for a more robust flow.
     * @throws \Core\Services\ImageUploadException Always throws, as this method is deprecated.
     */
    public function upload(UploadedFileInterface $file, int $storeId): array // Renamed from original for clarity
    {
        $this->logger->error('Deprecated ImageStorageService::upload() called. Use uploadFromTemporary() via ImageService.');
        throw new ImageUploadException('Direct upload of UploadedFileInterface to ImageStorageService is deprecated. Use ImageService to process temporary uploads.');
    }


    /**
     * Uploads an image from a temporary file path to permanent storage,
     * generates presets, and cleans up the temporary file.
     *
     * @param string $temporaryFilePath The absolute path to the uploaded file in temporary storage.
     * @param int $storeId The ID of the store the image belongs to.
     * @param string $originalMimeType The original MIME type of the uploaded file.
     * @param string $originalFilename The original client filename.
     * @return array<string, mixed> Metadata of the permanently stored image.
     * @throws ImageUploadException If image processing or storage fails.
     */
    public function uploadFromTemporary(
        string $temporaryFilePath,
        int $storeId,
        string $originalMimeType,
        string $originalFilename
    ): array {
        if (!file_exists($temporaryFilePath)) {
            $this->logger->error("Temporary file not found at: {$temporaryFilePath}");
            throw new ImageUploadException('Temporary image file not found.');
        }

        // ✅ Basic validation (MIME type check is typically done in ImageService, but good to have here too)
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
        if (!in_array($originalMimeType, $allowedMimeTypes, true)) {
            @unlink($temporaryFilePath); // Clean up temp file
            $this->logger->warning("Attempted upload of disallowed MIME type: {$originalMimeType}", ['temp_path' => $temporaryFilePath]);
            throw new ImageUploadException('Only JPG, PNG, GIF, WebP, AVIF images are allowed.');
        }

        // ✅ Determine extension based on MIME type
        $extension = match ($originalMimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/avif' => 'avif',
            default => pathinfo($originalFilename, PATHINFO_EXTENSION), // Fallback
        };
        $extension = strtolower(ltrim($extension, '.'));

        try {
            // Generate content-based SHA-256 hash
            $hash = $this->generateHashFromPath($temporaryFilePath);

            // ✅ Check if original already exists (deduplication at physical file level)
            $originalPath = $this->getPath($hash, $storeId, 'original', $extension);
            // important!!! RULE::: Physical image  already exists
            if (file_exists($originalPath)) {
                $this->logger->info('Physical image file already exists (deduplication)', [
                    'hash' => $hash,
                    'store_id' => $storeId,
                    'original_name' => $originalFilename,
                    'temp_path' => $temporaryFilePath,
                ]);

                // ✅ Delete the temporary file, as we don't need it.
                @unlink($temporaryFilePath);

                $imageInfo = @getimagesize($originalPath);
                return [
                    'hash' => $hash,
                    'extension' => $extension,
                    'original_filename' => $originalFilename,
                    'mime_type' => $originalMimeType,
                    'file_size_bytes' => filesize($originalPath),
                    'width' => $imageInfo[0] ?? null,
                    'height' => $imageInfo[1] ?? null,
                ];
            }

            // ✅ Move the temporary file to its final original storage location
            if (!rename($temporaryFilePath, $originalPath)) {
                throw new RuntimeException("Failed to move temporary file from {$temporaryFilePath} to {$originalPath}");
            }
            $this->logger->info("Original image saved from temporary location", [
                'path' => $originalPath,
                'hash' => $hash,
                'store_id' => $storeId,
            ]);

            // ✅ Get preferred output formats from config
            $preferredFormats = $this->configService->get('storage.image_generation.preferred_formats', ['avif', 'webp', 'jpg']);

            // ✅ Generate web preset (800px) in multiple formats
            $webPreset = $this->presets['web'] ?? ['width' => 800, 'height' => null, 'quality' => 90, 'crop' => false];
            foreach ($preferredFormats as $format) {
                $webPath = $this->getPath($hash, $storeId, 'web', $format);
                $webSuccess = $this->imageProcessingService->processImage($originalPath, $webPath, $webPreset, $format);

                if ($webSuccess) {
                    $this->logger->info("Web preset generated", ['path' => $webPath, 'format' => $format]);
                } else {
                    $this->logger->warning("Failed generating web preset in {$format} format", ['path' => $webPath]);
                }
            }

            // ✅ Generate thumbs preset (150x150) in multiple formats
            $thumbsPreset = $this->presets['thumbs'] ?? ['width' => 150, 'height' => 150, 'quality' => 85, 'crop' => true];
            foreach ($preferredFormats as $format) {
                $thumbsPath = $this->getPath($hash, $storeId, 'thumbs', $format);
                $thumbsSuccess = $this->imageProcessingService->processImage($originalPath, $thumbsPath, $thumbsPreset, $format);

                if ($thumbsSuccess) {
                    $this->logger->info("Thumbs preset generated", ['path' => $thumbsPath, 'format' => $format]);
                } else {
                    $this->logger->warning("Failed generating thumbs preset in {$format} format", ['path' => $thumbsPath]);
                }
            }

        } catch (\Throwable $e) {
            $this->logger->error("Error processing image from temporary storage: {$e->getMessage()}", ['exception' => $e, 'temp_path' => $temporaryFilePath]);

            // ✅ Clean up any partially moved/processed files
            @unlink($originalPath); // Attempt to delete the original if it was moved
            foreach (['web', 'thumbs'] as $preset) {
                foreach ($preferredFormats as $format) {
                    @unlink($this->getPath($hash ?? '', $storeId, $preset, $format)); // Use null-safe operator for $hash
                }
            }
            // Ensure temporary file is cleaned up if an error occurred before moving it
            @unlink($temporaryFilePath);

            throw new ImageUploadException('Failed to process image upload from temporary file.', 0, $e);
        }

        // ✅ Get image info after processing is complete
        $imageInfo = @getimagesize($originalPath);

        return [
            'hash' => $hash,
            'extension' => $extension,
            'original_filename' => $originalFilename,
            'mime_type' => $originalMimeType,
            'file_size_bytes' => filesize($originalPath),
            'width' => $imageInfo[0] ?? null,
            'height' => $imageInfo[1] ?? null,
        ];
    }

    /** {@inheritdoc} */
    public function delete(string $hash, int $storeId, ?string $extension = null): bool
    {
        $deletedCount = 0;
        $presets = ['original', 'web', 'thumbs']; // All known presets

        $preferred = $this->configService->get('storage.image_generation.preferred_formats')
            ?? ['avif', 'webp', 'jpg'];

        foreach ($presets as $preset) {
            $extensions = $extension !== null ? [$extension] : $preferred;
            foreach ($extensions as $ext) {
                $path = $this->buildFilePath($hash, $storeId, $preset, $ext);
                if (file_exists($path)) {
                    if (@unlink($path)) {
                        $this->logger->info("Deleted image preset: {$path}");
                        $deletedCount++;
                    } else {
                        $this->logger->error("Failed to delete image preset: {$path}");
                    }
                }
            }
        }

        // Clear any cached discovery for this hash
        $this->clearGetUrlsCacheFor($storeId, $hash);

        return $deletedCount > 0;
    }

    /** {@inheritdoc} */
    public function exists(string $hash, int $storeId, string $preset = 'web', ?string $extension = null): bool
    {
        if ($extension !== null) {
            return file_exists($this->getPath($hash, $storeId, $preset, $extension));
        }

        $urls = $this->getUrls($hash, $storeId, $preset);
        return !empty($urls);
    }

    private function determineFinalExtension(?string $extension = null): string
    {
        if ($extension !== null) {
            return strtolower(ltrim($extension, '.'));
        }

        $preferredFormats = $this->configService->get('storage.image_generation.preferred_formats', ['avif', 'webp', 'jpg']);

        return strtolower(ltrim($preferredFormats[0] ?? 'jpg', '.'));
    }

    private function clearGetUrlsCacheFor(int $storeId, string $hash): void
    {
        foreach (array_keys($this->getUrlsCache) as $key) {
            // Using str_contains and str_ends_with for matching hash in a cache key like 'storeId|preset|hash'
            if (str_contains($key, "{$storeId}|") && str_ends_with($key, "|{$hash}")) {
                unset($this->getUrlsCache[$key]);
            }
        }
    }

    /**
     * Generate SHA-256 hash from a file's content (from a given path) for deduplication.
     *
     * @param string $filePath The absolute path to the file.
     * @return string 64-character SHA-256 hash.
     * @throws ImageUploadException If the file cannot be read.
     */
    private function generateHashFromPath(string $filePath): string
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->logger->error("Cannot generate hash: file not found or not readable.", ['path' => $filePath]);
            throw new ImageUploadException("Cannot read file to generate hash: {$filePath}");
        }

        $hash = hash_file('sha256', $filePath);

        if ($hash === false) {
            $this->logger->error("Failed to generate SHA-256 hash for file.", ['path' => $filePath]);
            throw new ImageUploadException("Failed to generate hash for file: {$filePath}");
        }

        return $hash;
    }
}