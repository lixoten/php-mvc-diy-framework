<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Session\SessionManagerInterface;

/**
 * Manages return URLs for CRUD operations using session storage.
 *
 * Each record ID has its own session key to avoid collisions across multiple tabs.
 * Example: `return_url_for_record_5` stores the return URL for record ID 5.
 *
 * @see ReturnUrlManagerServiceInterface
 */
class ReturnUrlManagerService implements ReturnUrlManagerServiceInterface
{
    private const SESSION_PREFIX = 'return_url_for_record_';

    /**
     * @param SessionManagerInterface $sessionManager The session manager for persistence.
     */
    public function __construct(
        private readonly SessionManagerInterface $sessionManager
    ) {
    }

    /**
     * Stores the return URL for a specific record ID.
     *
     * @param int $recordId The record being edited/viewed.
     * @param string $url The URL to return to.
     * @return void
     */
    public function setReturnUrl(int $recordId, string $url): void
    {
        $key = self::SESSION_PREFIX . $recordId;
        $this->sessionManager->set($key, $url);
    }

    /**
     * Retrieves and removes the return URL for a specific record ID.
     *
     * @param int $recordId The record being saved.
     * @return string|null The stored return URL, or null if not found.
     */
    public function getAndClearReturnUrl(int $recordId): ?string
    {
        $key = self::SESSION_PREFIX . $recordId;
        $url = $this->sessionManager->get($key);

        // Remove from session after retrieval
        $this->sessionManager->remove($key);

        return $url;
    }

    /**
     * Peeks at the return URL without removing it.
     *
     * @param int $recordId The record ID.
     * @return string|null The stored return URL, or null if not found.
     */
    public function peekReturnUrl(int $recordId): ?string
    {
        $key = self::SESSION_PREFIX . $recordId;
        return $this->sessionManager->get($key);
    }
}
