<?php

declare(strict_types=1);

namespace Core\Form\Upload;

use Core\Storage\StorageProviderInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;

/**
 * File Upload Service
 *
 * Handles file uploads with content-based hashing for deduplication.
 *
 * Features:
 * - SHA-256 hash generation for deduplication and integrity verification
 * - Content-based MIME type detection using finfo (more secure than client-provided)
 * - Support for single and multiple file uploads
 * - Configurable upload rules per field (max_size, mime_types, subdir)
 * - Graceful error handling (returns null on failure, logs warnings)
 * - Delegates storage to StorageProviderInterface (local, S3, GCS, etc.)
 *
 * This service follows SRP: It ONLY handles file upload mechanics.
 * Validation logic should be in FileValidator (separate concern).
 */
class FileUploadService implements FileUploadServiceInterface
{
    private const DEFAULT_MAX_SIZE = 5242880; // 5MB
    private const DEFAULT_SUBDIR = 'uploads/originals';

    public function __construct(
        private StorageProviderInterface $storage,
        private ?LoggerInterface $logger = null,
        private int $defaultMaxSize = self::DEFAULT_MAX_SIZE
    ) {}

    /**
     * {@inheritDoc}
     */
    public function handleFiles(array $uploadedFiles, array $fieldDefinitions): array
    {
        $results = [];

        foreach ($uploadedFiles as $fieldName => $fileOrArray) {
            // ✅ Handle multiple file uploads (from your implementation)
            if (is_array($fileOrArray)) {
                $storedList = [];
                foreach ($fileOrArray as $file) {
                    if (!$file instanceof UploadedFileInterface) {
                        continue;
                    }

                    $field = $fieldDefinitions[$fieldName] ?? null;
                    if (!$field instanceof \Core\Form\Field\FieldInterface) {
                        continue;
                    }

                    $rules = $field->getOptions()['upload'] ?? [];
                    $meta = $this->processSingleFile($file, $rules);

                    if ($meta !== null) {
                        $storedList[] = $meta;
                    }
                }

                if (!empty($storedList)) {
                    $results[$fieldName] = $storedList;
                }
                continue;
            }

            // ✅ Handle single file upload
            if (!$fileOrArray instanceof UploadedFileInterface) {
                continue;
            }

            $field = $fieldDefinitions[$fieldName] ?? null;
            if (!$field instanceof \Core\Form\Field\FieldInterface) {
                continue;
            }

            $rules = $field->getOptions()['upload'] ?? [];
            $meta = $this->processSingleFile($fileOrArray, $rules);

            if ($meta !== null) {
                $results[$fieldName] = $meta;
            }
        }

        return $results;
    }

    /**
     * Process a single uploaded file.
     *
     * @param UploadedFileInterface $file PSR-7 uploaded file
     * @param array<string, mixed> $rules Upload rules from field config
     * @return array{key: string, original_name: string, mime: string, size: int, hash: string}|null
     */
    private function processSingleFile(UploadedFileInterface $file, array $rules = []): ?array
    {
        // ✅ Basic size validation (from your implementation)
        $size = $file->getSize() ?? 0;
        $max = (int) ($rules['max_size'] ?? $this->defaultMaxSize);

        if ($size > $max) {
            $this->logger?->warning('Uploaded file exceeds max size', [
                'size' => $size,
                'max' => $max,
            ]);
            return null;
        }

        // ✅ Content-based MIME detection (from your implementation - more secure)
        $effectiveMime = $this->sniffMime($file);
        $allowed = $rules['mime_types'] ?? [];

        if (!empty($allowed) && !in_array($effectiveMime, $allowed, true)) {
            $this->logger?->warning('Uploaded file MIME type not allowed', [
                'detected' => $effectiveMime,
                'allowed' => $allowed,
            ]);
            return null;
        }

        // ✅ SHA-256 hash generation (from my implementation - for deduplication)
        try {
            $hash = $this->generateContentHash($file);
        } catch (\Throwable $e) {
            $this->logger?->error('Failed generating file hash: ' . $e->getMessage());
            return null;
        }

        // ✅ Build storage key with content hash (hybrid approach)
        $extension = $this->getExtensionFromMimeType($effectiveMime);
        $subdir = rtrim($rules['subdir'] ?? self::DEFAULT_SUBDIR, '/');
        $storageKey = "{$subdir}/{$hash}.{$extension}";

        // ✅ Check if file already exists (deduplication optimization)
        if ($this->storage->exists($storageKey)) {
            $this->logger?->info('File already exists (deduplication)', [
                'key' => $storageKey,
                'hash' => $hash,
            ]);

            // Return existing file metadata (no need to upload again)
            return [
                'key' => $storageKey,
                'original_name' => (string) $file->getClientFilename(),
                'mime' => $effectiveMime,
                'size' => $size,
                'hash' => $hash,
            ];
        }

        // ✅ Upload file to storage (delegate to StorageProviderInterface)
        $stream = $file->getStream();

        try {
            if ($stream->isSeekable()) {
                $stream->rewind();
            }
        } catch (\Throwable $e) {
            // Ignore rewind errors
        }

        try {
            $storedKey = $this->storage->put($stream, $storageKey, array_merge($rules, [
                'mime_type' => $effectiveMime,
                'original_name' => $file->getClientFilename(),
                'hash' => $hash,
            ]));

            $this->logger?->info('File uploaded successfully', [
                'key' => $storedKey,
                'hash' => $hash,
            ]);
        } catch (\Throwable $e) {
            $this->logger?->error('Failed storing uploaded file: ' . $e->getMessage());
            return null;
        }

        // ✅ Return file metadata for database storage
        return [
            'key' => $storedKey,
            'original_name' => (string) $file->getClientFilename(),
            'mime' => $effectiveMime,
            'size' => $size,
            'hash' => $hash, // ✅ Store hash for future verification
        ];
    }

    /**
     * Generate SHA-256 hash of file content.
     *
     * @param UploadedFileInterface $file
     * @return string 64-character hex hash
     * @throws \RuntimeException If hash generation fails
     */
    private function generateContentHash(UploadedFileInterface $file): string
    {
        $stream = $file->getStream();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        $content = $stream->getContents();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        return hash('sha256', $content);
    }

    /**
     * Detect MIME type from file content using finfo.
     *
     * This is more secure than trusting getClientMediaType() which can be spoofed.
     *
     * @param UploadedFileInterface $file
     * @return string Detected MIME type
     */
    private function sniffMime(UploadedFileInterface $file): string
    {
        $stream = $file->getStream();

        try {
            if ($stream->isSeekable()) {
                $stream->rewind();
            }
        } catch (\Throwable $e) {
            // Ignore rewind errors
        }

        // ✅ Create temporary file for finfo analysis
        $tmp = @tmpfile();
        if ($tmp === false) {
            return $file->getClientMediaType() ?? '';
        }

        try {
            // Copy stream to tmpfile
            while (!$stream->eof()) {
                $chunk = $stream->read(8192);
                if ($chunk === '') {
                    break;
                }
                fwrite($tmp, $chunk);
            }

            // Get tmpfile path
            $meta = stream_get_meta_data($tmp);
            $path = $meta['uri'] ?? null;

            if ($path) {
                try {
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mime = (string) $finfo->file($path);
                    return $mime ?: ($file->getClientMediaType() ?? '');
                } catch (\Throwable $e) {
                    $this->logger?->warning('finfo failed: ' . $e->getMessage());
                }
            }
        } finally {
            fclose($tmp);
        }

        // ✅ Rewind stream for later use by storage provider
        try {
            if ($stream->isSeekable()) {
                $stream->rewind();
            }
        } catch (\Throwable $e) {
            // Ignore rewind errors
        }

        return $file->getClientMediaType() ?? '';
    }

    /**
     * Get file extension from MIME type.
     *
     * @param string $mimeType
     * @return string Extension (e.g., 'jpg', 'png')
     */
    private function getExtensionFromMimeType(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            'image/svg+xml' => 'svg',
            'application/zip' => 'zip',
            default => 'bin',
        };
    }
}
