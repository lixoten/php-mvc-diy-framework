<?php

declare(strict_types=1);

namespace Core\Session;

/**
 * Interface for session management functionality
 */
interface SessionManagerInterface
{
    /**
     * Start the session if not already started
     *
     * @return bool True if session started successfully
     */
    public function start(): bool;

    /**
     * Get data from the session
     *
     * @param string $key The key to retrieve
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The session data or default value
     */
    public function get(string $key, $default = null);

    /**
     * Set data in the session
     *
     * @param string $key The key to set
     * @param mixed $value The value to store
     * @return void
     */
    public function set(string $key, $value): void;

    /**
     * Check if a key exists in the session
     *
     * @param string $key The key to check
     * @return bool True if the key exists
     */
    public function has(string $key): bool;

    /**
     * Remove data from the session
     *
     * @param string $key The key to remove
     * @return void
     */
    public function remove(string $key): void;

    /**
     * Clear all session data
     *
     * @return void
     */
    public function clear(): void;

    /**
     * Regenerate the session ID
     *
     * @param bool $deleteOldSession Whether to delete the old session data
     * @return bool True if successful
     */
    public function regenerateId(bool $deleteOldSession = true): bool;

    /**
     * Destroy the session completely
     *
     * @return bool True if successful
     */
    public function destroy(): bool;

    /**
     * Get all session data
     *
     * @return array All session data
     */
    public function all(): array;
}
