<?php

declare(strict_types=1);

namespace Core\Form\Upload;

use Core\Storage\StorageProviderInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;

/**
 * High-level file upload service.
 */
class FileUploadService implements FileUploadServiceInterface
{
    private readonly StorageProviderInterface $storage;
    private ?LoggerInterface $logger;
    private int $defaultMaxSize = 5242880; // 5 MB

    public function __construct(StorageProviderInterface $storage, ?LoggerInterface $logger = null)
    {
        $this->storage = $storage;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function handleFiles(array $uploadedFiles, array $fieldDefinitions): array
    {
        $results = [];

        foreach ($uploadedFiles as $fieldName => $fileOrArray) {
            if (is_array($fileOrArray)) {
                $storedList = [];
                foreach ($fileOrArray as $file) {
                    if (! $file instanceof UploadedFileInterface) {
                        continue;
                    }
                    $rules = $fieldDefinitions[$fieldName]['upload'] ?? [];
                    $meta = $this->processSingleFile($file, $rules);
                    if ($meta !== null) {
                        $storedList[] = $meta;
                    }
                }
                if (! empty($storedList)) {
                    $results[$fieldName] = $storedList;
                }
                continue;
            }

            if (! $fileOrArray instanceof UploadedFileInterface) {
                continue;
            }

            $field = $fieldDefinitions[$fieldName] ?? null;
            if (!$field instanceof \Core\Form\Field\FieldInterface) {
                continue;
            }
            $options = $field->getOptions();
            $rules = $options['upload'] ?? [];
            $meta = $this->processSingleFile($fileOrArray, $rules);
            if ($meta !== null) {
                $results[$fieldName] = $meta;
            }
        }

        return $results;
    }

    private function processSingleFile(UploadedFileInterface $file, array $rules = []): ?array
    {
        $size = $file->getSize() ?? 0;
        $max = (int) ($rules['max_size'] ?? $this->defaultMaxSize);
        if ($size > $max) {
            $this->logger?->warning('Uploaded file exceeds max size.');
            return null;
        }

        $effectiveMime = $this->sniffMime($file);
        $allowed = $rules['mime_types'] ?? [];
        if (! empty($allowed) && ! in_array($effectiveMime, $allowed, true)) {
            $this->logger?->warning('Uploaded file mime type not allowed.');
            return null;
        }

        try {
            $key = $this->buildKey($file, $rules);
        } catch (\Throwable $e) {
            $this->logger?->error('Failed generating file key: ' . $e->getMessage());
            return null;
        }

        $stream = $file->getStream();
        try {
            if ($stream->isSeekable()) {
                $stream->rewind();
            }
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            $storedKey = $this->storage->put($stream, $key, $rules);
        } catch (\Throwable $e) {
            $this->logger?->error('Failed storing uploaded file: ' . $e->getMessage());
            return null;
        }

        return [
            'key' => $storedKey,
            'original_name' => (string) $file->getClientFilename(),
            'mime' => $effectiveMime ?: ($file->getClientMediaType() ?? ''),
            'size' => $size,
        ];
    }

    private function sniffMime(UploadedFileInterface $file): string
    {
        $stream = $file->getStream();
        try {
            if ($stream->isSeekable()) {
                $stream->rewind();
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $tmp = @tmpfile();
        if ($tmp === false) {
            return $file->getClientMediaType() ?? '';
        }

        try {
            while (! $stream->eof()) {
                $chunk = $stream->read(8192);
                if ($chunk === '') {
                    break;
                }
                fwrite($tmp, $chunk);
            }
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

        try {
            if ($stream->isSeekable()) {
                $stream->rewind();
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return $file->getClientMediaType() ?? '';
    }

    private function buildKey(UploadedFileInterface $file, array $rules = []): string
    {
        $subdir = $rules['subdir'] ?? 'files';
        $original = (string) $file->getClientFilename();
        $ext = pathinfo($original, PATHINFO_EXTENSION);
        try {
            $name = bin2hex(random_bytes(8));
        } catch (\Throwable $e) {
            $name = uniqid('', true);
            $this->logger?->error('Failed generating random filename: ' . $e->getMessage());
        }
        $key = rtrim($subdir, '/') . '/' . $name;
        if ($ext !== '') {
            $key .= '.' . $ext;
        }
        return $key;
    }
}
