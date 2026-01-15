<?php

declare(strict_types=1);

namespace Core\Cache;

use Core\Interfaces\CacheInterface;

/**
 * ✅ No-Op Cache Implementation (Development Mode)
 *
 * PURPOSE:
 * - Disable caching during development (always fresh data)
 * - Satisfy CacheInterface type hint without side effects
 * - Always return cache "miss" (forces fresh data load on every request)
 *
 * USE CASES:
 * - Development environment (always fresh config, no stale cache bugs)
 * - Testing (predictable behavior, no cache pollution between tests)
 * - Debugging cache-related issues (isolate cache vs. non-cache behavior)
 *
 * DOES NOT:
 * - Write any files to disk
 * - Consume memory (beyond method call overhead)
 * - Affect application logic (transparent no-op)
 *
 * PHILOSOPHY: "Pretend to cache, but do nothing—perfect for development"
 *
 * @package Core\Cache
 */
class NullCache implements CacheInterface
{
    /**
     * {@inheritDoc}
     *
     * ✅ Always returns $default (cache always "misses")
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $default;
    }

    /**
     * {@inheritDoc}
     *
     * ✅ Always returns true (pretends to succeed, but does nothing)
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * ✅ Always returns true (pretends to succeed, but does nothing)
     */
    public function delete(string $key): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * ✅ Always returns false (no cache ever exists)
     */
    public function has(string $key): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * ✅ Always returns true (pretends to succeed, but does nothing)
     */
    public function clear(): bool
    {
        return true;
    }
}