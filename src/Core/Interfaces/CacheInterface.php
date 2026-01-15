<?php

declare(strict_types=1);

namespace Core\Interfaces;

/**
 * ✅ Cache abstraction for MVC LIXO DIY Framework
 *
 * RESPONSIBILITIES:
 * - Store/retrieve/delete cached data
 * - Support TTL (time-to-live) expiration
 * - Check key existence
 * - Clear all cached data
 *
 * IMPLEMENTATIONS:
 * - FileCache (production-ready file-based caching)
 * - NullCache (no-op for development)
 * - MemoryCache (request-scoped in-memory caching for testing)
 *
 * PHILOSOPHY: "Simple, testable, swappable cache abstraction"
 *
 * @package Core\Interfaces
 */
interface CacheInterface
{
    /**
     * Fetch a value from cache.
     *
     * @param string $key Cache key (must be unique)
     * @param mixed $default Default value if key not found or expired
     * @return mixed Cached value or default
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Store a value in cache.
     *
     * @param string $key Cache key (must be unique)
     * @param mixed $value Value to cache (must be serializable)
     * @param int|null $ttl Time-to-live in seconds (null = forever)
     * @return bool True on success, false on failure
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * Delete a value from cache.
     *
     * @param string $key Cache key
     * @return bool True on success (or if key didn't exist), false on failure
     */
    public function delete(string $key): bool;

    /**
     * Check if a key exists in cache (and is not expired).
     *
     * @param string $key Cache key
     * @return bool True if key exists and is valid, false otherwise
     */
    public function has(string $key): bool;

    /**
     * Clear all cached values.
     *
     * @return bool True on success, false on failure
     */
    public function clear(): bool;
}
