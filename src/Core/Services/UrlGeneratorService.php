<?php

declare(strict_types=1);

namespace Core\Services;

use App\Enums\Url;
use Core\Context\CurrentContext;

/**
 * Generates contextual URLs by combining the contextual base with canonical URL enums.
 *
 * This service reads CurrentContext->getContextualBaseUrl() to determine if a
 * parent entity context is active (e.g., /testy/3) and prepends that prefix
 * to canonical URLs from Url enums.
 */
class UrlGeneratorService implements UrlGeneratorServiceInterface
{
    public function __construct(
        private readonly CurrentContext $currentContext
    ) {
    }

    /**
     * Generates a contextual URL by combining the contextual base with a canonical URL enum.
     *
     * @param Url $urlEnum The canonical URL enum (e.g., CORE_IMAGE_EDIT)
     * @param array<string, mixed> $params Route parameters (e.g., ['id' => 2])
     * @return string The fully formed contextual URL
     */
    public function generateUrl(Url $urlEnum, array $params = []): string
    {
        // Get the contextual base URL from CurrentContext (e.g., '/testy/3' or '')
        $contextualBase = $this->currentContext->getContextualBaseUrl();

        // Get the canonical path from the Url enum (e.g., '/image/edit/2')
        $canonicalPath = $urlEnum->url($params, $this->currentContext->getRouteType());

        if ($contextualBase === '') {
            return $canonicalPath;
        }


        // Normalize: if canonicalPath starts with '/{feature}/', strip it to avoid duplication
        $feature = strtolower($this->currentContext->getPageFeature()); // e.g., 'image'
        if (is_string($feature) && $feature !== '') {
            $featurePrefix = '/' . ltrim($feature, '/');
            if (str_starts_with($canonicalPath, $featurePrefix . '/')
                || $canonicalPath === $featurePrefix
            ) {
                $canonicalPath = substr($canonicalPath, strlen($featurePrefix));
                // Ensure leading slash after stripping
                $canonicalPath = '/' . ltrim($canonicalPath, '/');
            }
        }

        // If there's a contextual base, prepend it; otherwise, return canonical path
        if ($contextualBase !== '') {
            return $this->combineUrlParts($contextualBase, $canonicalPath);
        }

        return $canonicalPath;
    }

    /**
     * Combines two URL parts, ensuring no double slashes or missing slashes.
     *
     * @param string $prefix The contextual base prefix (e.g., '/testy/3')
     * @param string $path The canonical path (e.g., '/image/edit/2')
     * @return string The combined URL (e.g., '/testy/3/image/edit/2')
     */
    private function combineUrlParts(string $prefix, string $path): string
    {
        // Remove trailing slash from prefix, leading slash from path
        $prefix = rtrim($prefix, '/');
        $path = ltrim($path, '/');

        return $prefix . '/' . $path;
    }
}