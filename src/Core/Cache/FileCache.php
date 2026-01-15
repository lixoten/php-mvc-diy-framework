<?php

declare(strict_types=1);

namespace Core\Cache;

use Core\Interfaces\CacheInterface;

/**
 * ✅ 100% DIY File-Based Cache (Production-Ready)
 *
 * RESPONSIBILITIES:
 * - Store serialized data in filesystem
 * - Handle TTL (time-to-live) expiration automatically
 * - Clean up expired cache files (probabilistic cleanup)
 *
 * DOES NOT:
 * - Require external services (Redis, Memcached)
 * - Require Composer packages (symfony/cache, etc.)
 * - Require PHP extensions (APCu, Redis)
 *
 * USES ONLY:
 * - PHP built-in functions (file_get_contents, file_put_contents, unserialize, serialize)
 * - Standard filesystem operations
 *
 * PHILOSOPHY: "Simple, reliable, zero-dependency file caching"
 *
 * @package Core\Cache
 */
class FileCache implements CacheInterface
{
    private string $cacheDirectory;
    private int $cleanupProbability;

    /**
     * @param string $cacheDirectory Directory to store cache files
     * @param int $cleanupProbability Probability (1/N) to trigger cleanup on get() (default: 100 = 1%)
     */
    public function __construct(
        string $cacheDirectory,
        int $cleanupProbability = 100
    ) {
        $this->cacheDirectory = rtrim($cacheDirectory, DIRECTORY_SEPARATOR);
        $this->cleanupProbability = $cleanupProbability;

        // ✅ Ensure cache directory exists
        if (!is_dir($this->cacheDirectory)) {
            mkdir($this->cacheDirectory, 0755, true);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // ✅ Probabilistic cleanup: 1% chance to trigger cleanup on each get()
        if (random_int(1, $this->cleanupProbability) === 1) {
            $this->cleanupExpiredFiles();
        }

        $filePath = $this->getFilePath($key);

        if (!file_exists($filePath)) {
            return $default;
        }

        $data = unserialize(file_get_contents($filePath));

        // ✅ Check TTL expiration
        if ($data['expires_at'] !== null && time() > $data['expires_at']) {
            $this->delete($key);
            return $default;
        }

        return $data['value'];
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $filePath = $this->getFilePath($key);
        $expiresAt = $ttl !== null ? time() + $ttl : null;

        $data = [
            'value' => $value,
            'expires_at' => $expiresAt,
            'created_at' => time(),
        ];

        return file_put_contents($filePath, serialize($data)) !== false;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key): bool
    {
        $filePath = $this->getFilePath($key);

        if (!file_exists($filePath)) {
            return true; // ✅ Already deleted (idempotent)
        }

        return unlink($filePath);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $key): bool
    {
        $filePath = $this->getFilePath($key);

        if (!file_exists($filePath)) {
            return false;
        }

        $data = unserialize(file_get_contents($filePath));

        // ✅ Check if expired
        if ($data['expires_at'] !== null && time() > $data['expires_at']) {
            $this->delete($key);
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): bool
    {
        $files = glob($this->cacheDirectory . DIRECTORY_SEPARATOR . '*.cache');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Clean up expired cache files.
     *
     * ✅ Called probabilistically during get() operations (1% chance by default)
     * ✅ Can also be called manually via cron job or CLI command
     *
     * @return int Number of files deleted
     */
    public function cleanupExpiredFiles(): int
    {
        $files = glob($this->cacheDirectory . DIRECTORY_SEPARATOR . '*.cache');
        $deletedCount = 0;

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $data = @unserialize(file_get_contents($file));

            // ✅ Skip invalid cache files
            if ($data === false || !isset($data['expires_at'])) {
                continue;
            }

            // ✅ Delete if expired
            if ($data['expires_at'] !== null && time() > $data['expires_at']) {
                unlink($file);
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Get filesystem path for cache key.
     *
     * ✅ Uses MD5 hash to avoid filesystem issues with special characters
     * ✅ Format: {cacheDirectory}/{md5_hash}.cache
     *
     * @param string $key Cache key
     * @return string Full file path
     */
    private function getFilePath(string $key): string
    {
        $hash = md5($key);
        return $this->cacheDirectory . DIRECTORY_SEPARATOR . $hash . '.cache';
    }
}