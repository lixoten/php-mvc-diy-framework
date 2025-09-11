<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

// Dynamic-me
/**
 * Interface for managing generic page definitions.
 */
interface PageRegistryInterface
{
    /**
     * Get metadata for all registered generic pages.
     * Typically returns an array keyed by slug, containing title, etc.
     *
     * @return array<string, array>
     */
    public function getAllPages(): array;

    /**
     * Get metadata for a single page by its slug.
     *
     * @param string $slug The page slug (e.g., 'about', 'terms').
     * @return array|null Page metadata array or null if not found.
     */
    public function getPage(string $slug): ?array;

    /**
     * Check if a page with the given slug exists in the registry.
     *
     * @param string $slug The page slug.
     * @return bool True if the page exists, false otherwise.
     */
    public function hasPage(string $slug): bool;
}
