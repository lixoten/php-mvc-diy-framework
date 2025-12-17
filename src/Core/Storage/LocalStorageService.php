<?php

declare(strict_types=1);

namespace Core\Storage;

use Psr\Http\Message\StreamInterface;

/**
 * Local filesystem storage provider.
 */
class LocalStorageService implements StorageProviderInterface
{
    private readonly string $basePath;
    private readonly string $baseUrl;

    public function __construct(string $basePath, string $baseUrl = '')
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function put(StreamInterface $stream, string $key, array $options = []): string
    {
        $key = ltrim($key, '/');
        $target = $this->basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $key);
        $dir = dirname($target);

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new \RuntimeException("Failed to create directory: {$dir}");
            }
        }

        $handle = fopen($target, 'wb');
        if ($handle === false) {
            throw new \RuntimeException("Cannot open target for writing: {$target}");
        }

        try {
            if (method_exists($stream, 'isSeekable') && $stream->isSeekable()) {
                $stream->rewind();
            }
        } catch (\Throwable $e) {
            // ignore
        }

        while (!$stream->eof()) {
            $chunk = $stream->read(8192);
            if ($chunk === '') {
                break;
            }
            $written = fwrite($handle, $chunk);
            if ($written === false) {
                fclose($handle);
                throw new \RuntimeException("Failed writing to {$target}");
            }
        }

        fclose($handle);

        return str_replace(DIRECTORY_SEPARATOR, '/', $key);
    }

    public function delete(string $key): bool
    {
        $path = $this->basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($key, '/'));
        if (!file_exists($path)) {
            return true;
        }
        return unlink($path);
    }

    public function url(string $key, array $options = []): string
    {
        $key = ltrim($key, '/');
        if ($this->baseUrl === '') {
            return $key;
        }
        return $this->baseUrl . '/' . $key;
    }

    /**
     * {@inheritDoc}
     */
    public function exists(string $key): bool
    {
        $key = ltrim($key, '/');
        $path = $this->basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $key);
        return file_exists($path) && is_file($path);
    }

}
