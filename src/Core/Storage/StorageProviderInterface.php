<?php

declare(strict_types=1);

namespace Core\Storage;

use Psr\Http\Message\StreamInterface;

/**
 * Storage backend adapter (local disk, S3, GCS, etc.)
 */
interface StorageProviderInterface
{
    /**
     * Store stream under given key and return stored path/identifier.
     *
     * @param StreamInterface $stream
     * @param string $key
     * @param array<string,mixed> $options
     * @return string Stored key or path
     */
    public function put(StreamInterface $stream, string $key, array $options = []): string;

    /**
     * Delete stored object identified by key.
     *
     * @param string $key
     * @return bool True if deleted or not present
     */
    public function delete(string $key): bool;

    /**
     * Return a public or signed URL for the given key.
     *
     * @param string $key
     * @param array<string,mixed> $options
     * @return string
     */
    public function url(string $key, array $options = []): string;
}
