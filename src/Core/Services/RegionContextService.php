<?php

declare(strict_types=1);

namespace Core\Services;

/**
 * RegionContextService
 *
 *
 * Provides the current user's region context for normalization, validation, and formatting.
 */
class RegionContextService
{
    /**
     * Get the current region code (e.g., 'US', 'PT').
     *
     * @return string
     */
    public function getRegion(): string
    {
        // Example: get from session, request attribute, or default to 'US'
        return $_SESSION['user_region'] ?? 'US';
    }
}