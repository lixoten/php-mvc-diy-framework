<?php

declare(strict_types=1);

namespace Core\Services;

use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Image Processing Service
 *
 * Handles image resizing, cropping, and optimization using GD library.
 *
 * Responsibilities:
 * - Resize images to specified dimensions
 * - Crop images (center crop or custom crop)
 * - Compress/optimize JPEG/PNG/WebP images
 * - Strip EXIF metadata for privacy
 * - Convert between image formats
 *
 * This service follows SRP: It ONLY processes images.
 * Storage logic is handled by StorageProviderInterface.
 */
class ImageProcessingService
{
    public function __construct(
        private ?LoggerInterface $logger = null
    ) {
        // ✅ Log warning if AVIF is not supported
        if (!function_exists('imageavif')) {
            $this->logger?->warning('AVIF support not available (requires PHP 8.1+ with libavif)');
        }
    }

    /**
     * Resize and optimize image according to preset configuration.
     *
     * @param string $sourcePath Path to source image file
     * @param string $targetPath Path to save processed image
     * @param array{width: ?int, height: ?int, quality: int, crop: bool} $preset Processing options
     * @param string|null $targetFormat Target format ('jpg', 'webp', 'avif', 'png') or null to keep original
     * @return bool True if successful, false otherwise
     */
    public function processImage(
        string $sourcePath,
        string $targetPath,
        array $preset,
        ?string $targetFormat = null
    ): bool {
        if (!file_exists($sourcePath)) {
            $this->logger?->error("Source image not found: {$sourcePath}");
            return false;
        }

        // ✅ Detect source image type
        $imageInfo = @getimagesize($sourcePath);
        if ($imageInfo === false) {
            $this->logger?->error("Could not read image info: {$sourcePath}");
            return false;
        }

        [$sourceWidth, $sourceHeight, $imageType] = $imageInfo;

        // ✅ Check if AVIF is source format but not supported
        if ($imageType === IMAGETYPE_AVIF && !function_exists('imageavif')) {
            $this->logger?->error("Cannot process AVIF source image: GD extension lacks AVIF support (requires PHP 8.1+ with libavif)", [
                'source_path' => $sourcePath,
            ]);
            return false; // ✅ FAIL FAST: Cannot load AVIF source without support
        }

        // ✅ Check if AVIF is target format but not supported
        if ($targetFormat === 'avif' && !function_exists('imageavif')) {
            $this->logger?->warning("AVIF output requested but not supported. Will fall back to JPEG.", [
                'target_format_requested' => $targetFormat,
            ]);
            // ✅ Let execution continue - fallback to JPEG will happen at line 163-171
            $targetFormat = 'jpg'; // ✅ EXPLICIT FALLBACK
        }

        // ✅ Load source image
        $sourceImage = match ($imageType) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG  => imagecreatefrompng($sourcePath),
            IMAGETYPE_GIF  => imagecreatefromgif($sourcePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
            IMAGETYPE_AVIF => imagecreatefromavif($sourcePath),
            default => false,
        };

        if ($sourceImage === false) {
            $this->logger?->error("Failed to create image resource from source", [
                'source_path' => $sourcePath,
                'image_type' => $imageType,
                'mime_type' => image_type_to_mime_type($imageType),
            ]);
            return false;
        }

        // ✅ Calculate target dimensions
        $targetWidth = $preset['width'] ?? $sourceWidth;
        $targetHeight = $preset['height'] ?? $sourceHeight;

        if ($preset['crop']) {
            // ✅ Crop mode: Resize to exact dimensions (center crop)
            [$targetWidth, $targetHeight, $sourceImage] = $this->centerCrop(
                $sourceImage,
                $sourceWidth,
                $sourceHeight,
                $targetWidth,
                $targetHeight
            );
        } else {
            // ✅ Resize mode: Maintain aspect ratio
            [$targetWidth, $targetHeight] = $this->calculateAspectRatioDimensions(
                $sourceWidth,
                $sourceHeight,
                $targetWidth,
                $targetHeight
            );
        }

        // ✅ Create target image
        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($targetImage === false) {
            imagedestroy($sourceImage);
            $this->logger?->error("Could not create target image resource");
            return false;
        }

        // ✅ Preserve transparency for PNG/WebP
        if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_WEBP) {
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
            $transparent = imagecolorallocatealpha($targetImage, 0, 0, 0, 127);
            if ($transparent !== false) {
                imagefill($targetImage, 0, 0, $transparent);
            }
        }

        // ✅ Resize image
        $success = imagecopyresampled(
            $targetImage,
            $sourceImage,
            0, 0, 0, 0,
            $targetWidth,
            $targetHeight,
            imagesx($sourceImage),
            imagesy($sourceImage)
        );

        imagedestroy($sourceImage);

        if (!$success) {
            imagedestroy($targetImage);
            $this->logger?->error("Failed resizing image");
            return false;
        }

        // ✅ Ensure target directory exists
        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                imagedestroy($targetImage);
                $this->logger?->error("Could not create target directory: {$targetDir}");
                return false;
            }
        }

        // ✅ Determine target format
        $outputType = match ($targetFormat) {
            'jpg', 'jpeg' => IMAGETYPE_JPEG,
            'png' => IMAGETYPE_PNG,
            'webp' => IMAGETYPE_WEBP,
            'avif' => IMAGETYPE_AVIF,
            'gif' => IMAGETYPE_GIF,
            default => $imageType, // Keep original format if not specified
        };

        // ✅ Save processed image
        // $quality = $preset['quality'] ?? 90;

        // ✅ Expose configuration bug if quality is missing
        if (!isset($preset['quality'])) {
            throw new RuntimeException("Image preset missing 'quality' parameter");
        }
        $quality = $preset['quality'];
        $saved = match ($outputType) {
            IMAGETYPE_JPEG => imagejpeg($targetImage, $targetPath, $quality),
            IMAGETYPE_PNG  => imagepng($targetImage, $targetPath, (int) round((100 - $quality) / 10)),
            IMAGETYPE_GIF  => imagegif($targetImage, $targetPath),
            IMAGETYPE_WEBP => imagewebp($targetImage, $targetPath, $quality),
            IMAGETYPE_AVIF => imageavif($targetImage, $targetPath, $quality),
            default => false,
        };

        imagedestroy($targetImage);

        if (!$saved) {
            $this->logger?->error("Failed saving processed image: {$targetPath}");
            return false;
        }

        $this->logger?->debug("Image processed successfully", [
            'source' => $sourcePath,
            'target' => $targetPath,
            'source_dimensions' => "{$sourceWidth}x{$sourceHeight}",
            'target_dimensions' => "{$targetWidth}x{$targetHeight}",
            'quality' => $quality,
            'format' => $targetFormat ?? 'original',
        ]);

        return true;
    }

    /**
     * Center crop image to exact dimensions.
     *
     * @return array{int, int, \GdImage}
     */
    private function centerCrop(
        \GdImage $sourceImage,
        int $sourceWidth,
        int $sourceHeight,
        int $targetWidth,
        int $targetHeight
    ): array {
        // Calculate aspect ratios
        $sourceRatio = $sourceWidth / $sourceHeight;
        $targetRatio = $targetWidth / $targetHeight;

        if ($sourceRatio > $targetRatio) {
            // ✅ Source is wider - crop width
            $cropHeight = $sourceHeight;
            $cropWidth = (int) round($sourceHeight * $targetRatio);
            $cropX = (int) round(($sourceWidth - $cropWidth) / 2);
            $cropY = 0;
        } else {
            // ✅ Source is taller - crop height
            $cropWidth = $sourceWidth;
            $cropHeight = (int) round($sourceWidth / $targetRatio);
            $cropX = 0;
            $cropY = (int) round(($sourceHeight - $cropHeight) / 2);
        }

        // ✅ Create cropped image
        $croppedImage = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($croppedImage === false) {
            return [$targetWidth, $targetHeight, $sourceImage];
        }

        imagecopyresampled(
            $croppedImage,
            $sourceImage,
            0, 0,
            $cropX, $cropY,
            $targetWidth, $targetHeight,
            $cropWidth, $cropHeight
        );

        imagedestroy($sourceImage);

        return [$targetWidth, $targetHeight, $croppedImage];
    }

    /**
     * Calculate dimensions maintaining aspect ratio.
     *
     * @return array{int, int}
     */
    private function calculateAspectRatioDimensions(
        int $sourceWidth,
        int $sourceHeight,
        ?int $targetWidth,
        ?int $targetHeight
    ): array {
        // ✅ If both dimensions specified, maintain aspect ratio by limiting to max
        if ($targetWidth !== null && $targetHeight !== null) {
            $widthRatio = $targetWidth / $sourceWidth;
            $heightRatio = $targetHeight / $sourceHeight;
            $ratio = min($widthRatio, $heightRatio);

            return [
                (int) round($sourceWidth * $ratio),
                (int) round($sourceHeight * $ratio),
            ];
        }

        // ✅ If only width specified, calculate height
        if ($targetWidth !== null) {
            $ratio = $targetWidth / $sourceWidth;
            return [
                $targetWidth,
                (int) round($sourceHeight * $ratio),
            ];
        }

        // ✅ If only height specified, calculate width
        if ($targetHeight !== null) {
            $ratio = $targetHeight / $sourceHeight;
            return [
                (int) round($sourceWidth * $ratio),
                $targetHeight,
            ];
        }

        // ✅ No dimensions specified - keep original
        return [$sourceWidth, $sourceHeight];
    }
}
