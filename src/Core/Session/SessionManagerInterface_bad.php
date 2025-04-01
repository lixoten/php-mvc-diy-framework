<?php

declare(strict_types=1);

namespace Core\Session;

interface SessionManagerInterface
{
    /**
     * Start the session
     *
     * @return bool True if session started successfully
     */
    public function start(): bool;

    /**
     * Check if the session has been started
     *
     * @return bool True if session is active
     */
    public function isStarted(): bool;

    /**
     * Regenerate the session ID
     *
     * @param bool $deleteOldSession Whether to delete the old session
     * @return bool True if regenerated successfully
     */
    public function regenerateId(bool $deleteOldSession = false): bool;

    /**
     * Get a value from the session
     *
     * @param string $key The key to retrieve
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The session value or default
     */
    public function get(string $key, $default = null);

    /**
     * Set a value in the session
     *
     * @param string $key The key to set
     * @param mixed $value The value to store
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
     * Remove a value from the session
     *
     * @param string $key The key to remove
     */
    public function remove(string $key): void;

    /**
     * Clear all session data
     */
    public function clear(): void;

    /**
     * Destroy the session
     */
    public function destroy(): void;
}