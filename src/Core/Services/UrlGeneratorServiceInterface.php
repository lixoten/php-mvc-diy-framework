<?php

declare(strict_types=1);

namespace Core\Services;

use App\Enums\Url;

/**
 * Generates contextual URLs by combining parent entity context with canonical URL enums.
 *
 * This service is responsible for building complete, routable URLs that respect
 * the current parent entity context (e.g., /testy/3/image/edit/2 vs /image/edit/2).
 */
interface UrlGeneratorServiceInterface
{
    /**
     * Generates a contextual URL by combining the contextual base with a canonical URL enum.
     *
     * If CurrentContext has a contextual base (e.g., /testy/3), this method will
     * prepend that prefix to the canonical path from the URL enum.
     *
     * Examples:
     * - CurrentContext contextual base: '/testy/3'
     * - $urlEnum = Url::CORE_IMAGE_EDIT, $params = ['id' => 2]
     * - Returns: '/testy/3/image/edit/2'
     *
     * - CurrentContext contextual base: '' (empty, top-level)
     * - $urlEnum = Url::CORE_IMAGE_EDIT, $params = ['id' => 2]
     * - Returns: '/image/edit/2'
     *
     * @param Url $urlEnum The canonical URL enum (e.g., CORE_IMAGE_EDIT)
     * @param array<string, mixed> $params Route parameters (e.g., ['id' => 2])
     * @return string The fully formed contextual URL
     */
    public function generateUrl(Url $urlEnum, array $params = []): string;
}
