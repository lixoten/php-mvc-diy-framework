<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

// Dynamic-me 2
/**
 * Interface for managing configurations of different dynamic content types.
 */
interface ContentTypeRegistryInterface
{
    /**
     * Check if a content type with the given slug is registered.
     *
     * @param string $slug The content type slug (e.g., 'posts', 'notes').
     * @return bool True if the content type exists, false otherwise.
     */
    public function hasContentType(string $slug): bool;

    /**
     * Get the full configuration array for a single content type by its slug.
     *
     * @param string $slug The content type slug.
     * @return array The configuration array for the content type.
     * @throws \InvalidArgumentException If the content type is not found.
     */
    public function getContentType(string $slug): array;

    /**
     * Get configurations for all registered content types.
     *
     * @return array<string, array> An array where keys are slugs and values are configuration arrays.
     */
    public function getAllContentTypes(): array;
}
