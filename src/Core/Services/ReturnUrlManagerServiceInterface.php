<?php

declare(strict_types=1);

namespace Core\Services;

/**
 * Manages return URLs for CRUD operations.
 * Stores and retrieves the URL to redirect to after save/cancel/delete.
 *
 * Uses session storage keyed by record ID to avoid collisions across multiple tabs.
 */
interface ReturnUrlManagerServiceInterface
{
    /**
     * Stores the return URL for a specific record ID.
     * Called when entering edit/view/detail pages to capture the caller.
     *
     * @param int $recordId The record being edited/viewed.
     * @param string $url The URL to return to (typically from HTTP_REFERER or current URL).
     * @return void
     */
    public function setReturnUrl(int $recordId, string $url): void;

    /**
     * Retrieves and removes the return URL for a specific record ID.
     * Called after save/cancel to determine where to redirect.
     *
     * ⚠️ This method **removes** the URL from session after retrieval (pop behavior).
     *
     * @param int $recordId The record being saved.
     * @return string|null The stored return URL, or null if not found.
     */
    public function getAndClearReturnUrl(int $recordId): ?string;

    /**
     * Peeks at the return URL without removing it.
     * Useful for displaying a "back" button without consuming the URL.
     *
     * @param int $recordId The record ID.
     * @return string|null The stored return URL, or null if not found.
     */
    public function peekReturnUrl(int $recordId): ?string;
}
