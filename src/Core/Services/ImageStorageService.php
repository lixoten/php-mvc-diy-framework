<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Interfaces\ConfigInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

// use SplFileInfo; // Not used in this version, but was in previous version. Keep or remove.

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
        // Assume these are configured in a config file, e.g., `config/app.php` or `config/storage.php`
        //$publicHtmlRootConfig = $this->configService->get('storage.multi_tenant_images.public_html_root');
        //$storageRootConfig    = $this->configService->get('storage.multi_tenant_images.storage_root');

        $this->publicHtmlRoot = $publicHtmlRoot;
        $this->storageRoot    = $storageRoot;

        $this->publicBaseUrl  = $this->configService->get('storage.multi_tenant_images.public_base_url');

        // Load image presets from storage.php
        $this->presets = $this->configService->get('storage.image_presets', [
            'thumbs' => ['width' => 150, 'height' => 150, 'quality' => 85, 'crop' => true],
            'web' => ['width' => 800, 'height' => null, 'quality' => 90, 'crop' => false],
        ]);


        // // ✅ FIX: Resolve relative paths to absolute paths
        // // Use getcwd() as the base for resolving relative paths, as it should be the project root
        // // when index.php in public_html is the entry point.
        // $projectRoot = getcwd();
        // if ($projectRoot === false) {
        //     // Fallback: If getcwd() somehow fails, manually derive from __DIR__.
        //     // This assumes ImageStorageService.php is 3 levels deep from the project root (src/Core/Services).
        //     $projectRoot = dirname(__DIR__, 3); // d:\xampp\htdocs\my_projects\mvclixo
        // }

        // // Apply path resolution to ensure absolute paths are used for filesystem checks
        // $this->publicHtmlRoot = $this->resolvePath($publicHtmlRootConfig, $projectRoot);
        // $this->storageRoot = $this->resolvePath($storageRootConfig, $projectRoot);



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

        // ✅ AUTO-CREATE: Private storage root (safe to auto-create)
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


    // /**
    //  * Resolves a given path to an absolute path, using a base path if the configured path is relative.
    //  *
    //  * @param string $configuredPath The path from configuration (can be absolute or relative).
    //  * @param string $basePath The base path (e.g., project root) to resolve relative paths against.
    //  * @return string The resolved absolute path.
    //  * @throws \InvalidArgumentException If the configured path is empty.
    //  */
    // private function resolvePath(string $configuredPath, string $basePath): string
    // {
    //     if (empty($configuredPath)) {
    //         throw new \InvalidArgumentException("Configured path cannot be empty.");
    //     }

    //     // Check if path is already absolute (starts with '/' or 'X:\' on Windows)
    //     if (preg_match('/^(?:[A-Za-z]:\\\\|\/)/', $configuredPath)) {
    //         return rtrim($configuredPath, '/\\');
    //     }

    //     // If relative, resolve against the provided base path
    //     return rtrim(rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR . ltrim($configuredPath, '/\\'), '/\\');
    // }



    /** {@inheritdoc} */
    public function getUrl(string $hash, int $storeId, string $preset = 'web', ?string $extension = null): string
    {
        // For static assets or default images not managed by the multi-tenant structure,
        // we might want a different logic here, or a separate formatter.
        // For now, assume all calls are for multi-tenant, preset-based images.

        if (empty($hash)) {
            // Or handle a default image URL here if 'default_image' option is not used by formatter
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
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        return $path;
    }


    /**
     * Build absolute filesystem path for given hash/preset/extension WITHOUT creating directories.
     *
     * Safe for probing (getUrls) because it has no side-effects.
     */
    // private function buildFilePath(string $hash, int $storeId, string $preset = 'web', ?string $extension = null): string
    // {
    //     $base = ($preset === 'original') ? $this->storageRoot : $this->publicHtmlRoot;
    //     $directory = sprintf('%s/store/%d/images/%s', rtrim($base, '/'), $storeId, $preset);
    //     $finalExtension = $this->determineFinalExtension($preset, $extension);
    //     return sprintf('%s/%s.%s', $directory, $hash, $finalExtension);
    // }
    private function buildFilePath(string $hash, int $storeId, string $preset = 'web', ?string $extension = null): string
    {
        $finalExtension = $this->determineFinalExtension($extension);

        if ($preset === 'original') {
            // ✅ Originals: storage_root/store/{storeId}/originals/{hash}.{ext}
            return sprintf(
                '%s/store/%d/originals/%s.%s',
                rtrim($this->storageRoot, '/'),
                $storeId,
                $hash,
                $finalExtension
            );
        }

        // ✅ Public presets: public_html_root/store/{storeId}/images/{preset}/{hash}.{ext}
        return sprintf(
            '%s/store/%d/images/%s/%s.%s',
            rtrim($this->publicHtmlRoot, '/'),
            $storeId,
            $preset,
            $hash,
            $finalExtension
        );
    }


    /**
     * Return associative array of available extension => public URL for a given image/preset.
     *
     * If no files are found the method MUST return an empty array.
     *
     * @param string $hash
     * @param int $storeId
     * @param string $preset
     * @return array<string,string>
     */
    public function getUrls(string $hash, int $storeId, string $preset = 'web'): array
    {
        $cacheKey = implode('|', [$storeId, $preset, $hash]);
        if (isset($this->getUrlsCache[$cacheKey])) {
            return $this->getUrlsCache[$cacheKey];
        }

        $preferred = $this->configService->get('storage.image_generation.preferred_formats') // ✅ Changed config path
            ?? $this->configService->get('image_generation.preferred_formats')
            ?? ['avif', 'webp', 'jpg'];

        $found = [];
        foreach ($preferred as $ext) {
            $path = $this->buildFilePath($hash, $storeId, $preset, $ext);
            if (file_exists($path)) {
                $found[$ext] = $this->getUrl($hash, $storeId, $preset, $ext);
            }
        }

        // Cache result (possibly empty)
        $this->getUrlsCache[$cacheKey] = $found;
        return $found;
    }



    /** {@inheritdoc} */
    public function upload(UploadedFileInterface $file, int $storeId): array // ✅ RETURN ARRAY
    {
        if ($file->getError() !== UPLOAD_ERR_OK) {
            $this->logger->error("Image upload failed with error code: {$file->getError()}");
            throw new RuntimeException('Image upload failed.');
        }

        // Basic validation (real-world needs more: MIME type, file size limits, image dimensions, security scanning)
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif']; // ✅ Added WebP and AVIF
        $clientMediaType = $file->getClientMediaType();

        if (!in_array($clientMediaType, $allowedMimeTypes, true)) {
            $this->logger->warning("Attempted upload of disallowed MIME type: {$clientMediaType}");
            throw new RuntimeException('Only JPG, PNG, GIF images are allowed.');
        }

        // ✅ Determine extension based on MIME type or original filename
        $extension = match ($clientMediaType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/avif' => 'avif',
            default => pathinfo($file->getClientFilename() ?? 'file.jpg', PATHINFO_EXTENSION), // Fallback
        };
        // Ensure extension is clean
        $extension = strtolower(ltrim($extension, '.'));

        // Generate content-based SHA-256 hash (deduplication)
        $hash = $this->generateHash($file);

        // ✅ Check if original already exists (deduplication)
        $originalPath = $this->getPath($hash, $storeId, 'original', $extension);


        // // If already exists, return metadata from existing file
        // if (file_exists($originalPath)) {
        //     $imageInfo = @getimagesize($originalPath);
        //     return [
        //         'hash' => $hash,
        //         'extension' => $extension,
        //         'original_filename' => $file->getClientFilename(),
        //         'mime_type' => $clientMediaType,
        //         'file_size_bytes' => filesize($originalPath),
        //         'width' => $imageInfo[0] ?? null,
        //         'height' => $imageInfo[1] ?? null,
        //     ];
        // }


        if (file_exists($originalPath)) {
            $this->logger->info('Image already exists (deduplication)', [
                'hash' => $hash,
                'store_id' => $storeId,
                'original_name' => $file->getClientFilename(),
            ]);

            // ✅ Image already exists - no need to upload again
            // return ['hash' => $hash, 'extension' => $extension];
            $imageInfo = @getimagesize($originalPath);

            return [
                'filename' => $hash,
                'extension' => $extension,
                'original_filename' => $file->getClientFilename(),
                'mime_type' => $clientMediaType,
                'file_size_bytes' => filesize($originalPath),
                'width' => $imageInfo[0] ?? null,
                'height' => $imageInfo[1] ?? null,
            ];
        }

        // ✅ Image doesn't exist yet - get paths for all presets
        $webPath = $this->getPath($hash, $storeId, 'web', $extension);
        $thumbsPath = $this->getPath($hash, $storeId, 'thumbs', $extension);

        try {
            // ✅ Store original in private storage (no processing)
            $file->moveTo($originalPath);
            $this->logger->info("Original image saved", [
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
            $this->logger->error("Error processing uploaded image: {$e->getMessage()}");

            // ✅ Clean up any partially moved files
            @unlink($originalPath);

            // Clean up all generated formats
            foreach (['web', 'thumbs'] as $preset) {
                foreach ($preferredFormats as $format) {
                    @unlink($this->getPath($hash, $storeId, $preset, $format));
                }
            }

            throw new RuntimeException('Failed to process image upload.', 0, $e);
        }
        $imageInfo = @getimagesize($originalPath);

        return [
            'hash' => $hash,
            'extension' => $extension,
            'original_filename' => $file->getClientFilename(),
            'mime_type' => $clientMediaType,
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
            ?? $this->configService->get('image_generation.preferred_formats')
            ?? ['avif', 'webp', 'jpg'];

        foreach ($presets as $preset) {
            $extensions = $extension !== null ? [$extension] : $preferred;
            foreach ($extensions as $ext) {
                $path = $this->getPath($hash, $storeId, $preset, $ext);
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


    /**
     * Determine final extension to use given a preset and optional explicit extension.
     */
    private function determineFinalExtension(?string $extension = null): string
    {
        if ($extension !== null) {
            return strtolower(ltrim($extension, '.'));
        }

        // ✅ Get preferred formats from config
        $preferredFormats = $this->configService->get('storage.image_generation.preferred_formats', ['avif', 'webp', 'jpg']);

        // ✅ Return first preferred format as default
        return strtolower(ltrim($preferredFormats[0] ?? 'jpg', '.'));
    }

    /**
     * Remove cached getUrls entries for a given store/hash (used after delete).
     */
    private function clearGetUrlsCacheFor(int $storeId, string $hash): void
    {
        foreach (array_keys($this->getUrlsCache) as $key) {
            if (str_contains($key, "{$storeId}|") && str_ends_with($key, "|{$hash}")) {
                unset($this->getUrlsCache[$key]);
            }
        }
    }



    /**
     * Generate SHA-256 hash from file content for deduplication.
     *
     * This method reads the uploaded file's content and generates a content-based hash.
     * Benefits:
     * - Deduplication: Same file uploaded multiple times = same hash = no duplicate storage
     * - Integrity verification: Can re-hash file later to verify it hasn't been corrupted
     * - Predictable filenames: Hash-based filenames are deterministic
     *
     * @param UploadedFileInterface $file Uploaded file (PSR-7)
     * @return string 64-character SHA-256 hash (e.g., "abc123def456...")
     * @throws RuntimeException If stream cannot be read
     */
    private function generateHash(UploadedFileInterface $file): string
    {
        $stream = $file->getStream();

        // ✅ Rewind stream to beginning (in case it was read before)
        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        // ✅ Read entire file content
        $content = $stream->getContents();

        // ✅ Rewind again for later use by moveTo() or other operations
        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        // ✅ Generate SHA-256 hash of content
        return hash('sha256', $content);
    }
}
