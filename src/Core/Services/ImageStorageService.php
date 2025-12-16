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
        private LoggerInterface $logger
    ) {
        // Assume these are configured in a config file, e.g., `config/app.php` or `config/filesystems.php`
        $this->publicHtmlRoot = $this->configService->get('filesystems.public_html_root');
        $this->storageRoot    = $this->configService->get('filesystems.storage_root');
        $this->publicBaseUrl  = $this->configService->get('filesystems.public_base_url.store_images');

        // Basic validation for paths
        if (!is_dir($this->publicHtmlRoot) || !is_writable($this->publicHtmlRoot)) {
            $this->logger->error("ImageStorageService: Public HTML root is invalid or not writable: {$this->publicHtmlRoot}");
            // In a real app, you might throw an exception or handle gracefully
        }
        if (!is_dir($this->storageRoot) || !is_writable($this->storageRoot)) {
            $this->logger->error("ImageStorageService: Storage root is invalid or not writable: {$this->storageRoot}");
            // In a real app, you might throw an exception or handle gracefully
        }
    }

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

        $finalExtension = $this->determineFinalExtension($preset, $extension);

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
    private function buildFilePath(string $hash, int $storeId, string $preset = 'web', ?string $extension = null): string
    {
        $base = ($preset === 'original') ? $this->storageRoot : $this->publicHtmlRoot;
        $directory = sprintf('%s/store/%d/images/%s', rtrim($base, '/'), $storeId, $preset);
        $finalExtension = $this->determineFinalExtension($preset, $extension);
        return sprintf('%s/%s.%s', $directory, $hash, $finalExtension);
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

        $preferred = $this->configService->get('filesystems.image_generation.preferred_formats')
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
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
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
            default => pathinfo($file->getClientFilename() ?? 'file.jpg', PATHINFO_EXTENSION), // Fallback
        };
        // Ensure extension is clean
        $extension = strtolower(ltrim($extension, '.'));

        // Generate a unique hash for the filename (e.g., SHA256 of file content or unique ID)
        // Using uniqid as hash_file on stream can be problematic or slow for large files
        $hash = uniqid('img_', true);

        // Ensure directories exist and get paths with the correct extension
        $originalPath = $this->getPath($hash, $storeId, 'original', $extension);
        $webPath = $this->getPath($hash, $storeId, 'web', $extension);
        $thumbsPath = $this->getPath($hash, $storeId, 'thumbs', $extension);

        try {
            // Store original in private storage
            $file->moveTo($originalPath);
            $this->logger->info("Original image saved to: {$originalPath}");

            // --- Simplified Preset Generation (Placeholder) ---
            // In a real application, you'd use an image manipulation library (e.g., Intervention Image)
            // to create resized/optimized versions for 'web' and 'thumbs'.
            // For now, we're just copying, but ideally, you'd convert/resize as needed for each preset.
            copy($originalPath, $webPath);
            $this->logger->info("Web preset (simulated) saved to: {$webPath}");
            copy($originalPath, $thumbsPath);
            $this->logger->info("Thumbs preset (simulated) saved to: {$thumbsPath}");
            // --- End Simplified Preset Generation ---
        } catch (\Throwable $e) {
            $this->logger->error("Error moving/copying uploaded image: {$e->getMessage()}");
            // Clean up any partially moved files
            @unlink($originalPath);
            @unlink($webPath);
            @unlink($thumbsPath);
            throw new RuntimeException('Failed to process image upload.', 0, $e);
        }

        return ['hash' => $hash, 'extension' => $extension]; // ✅ Return hash and extension
    }

    /** {@inheritdoc} */
    public function delete(string $hash, int $storeId, ?string $extension = null): bool
    {
        $deletedCount = 0;
        $presets = ['original', 'web', 'thumbs']; // All known presets

        $preferred = $this->configService->get('filesystems.image_generation.preferred_formats')
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
    private function determineFinalExtension(string $preset, ?string $extension = null): string
    {
        if ($extension !== null) {
            return strtolower(ltrim($extension, '.'));
        }

        $cfg = $this->configService->get("filesystems.default_image_extension.{$preset}");
        if (!empty($cfg)) {
            return strtolower(ltrim((string)$cfg, '.'));
        }

        return 'jpg';
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
}
