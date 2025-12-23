<?php

declare(strict_types=1);

namespace Core\Formatters;

/**
 * Formatter for transforming an image path/filename into a complete URL string.
 * It does NOT generate HTML <img> tags.
 */
// fixme ..removeme is not used
// removeme
class XxxxImageFormatter extends AbstractFormatter
{
    public function getName(): string
    {
        return 'image';
    }

    public function supports(mixed $value): bool
    {
        return is_string($value) || is_null($value);
    }

    /**
     * Transforms an image path/filename into a complete URL string.
     * It does not generate HTML markup.
     *
     * @param mixed $value The image path/filename.
     * @param array<string, mixed> $options Options for formatting (e.g., 'base_url').
     * @return string The complete image URL.
     */
    protected function transform(mixed $value, array $options = []): string
    {
        if (empty($value)) {
            return '';
        }

        $imagePath = (string)$value;

        // Apply base_url if provided in options
        if (isset($options['base_url']) && is_string($options['base_url'])) {
            $baseUrl = rtrim($options['base_url'], '/');
            // Ensure proper path concatenation, handling absolute paths/URLs
            // If imagePath is already absolute or a full URL, return as-is
            if (str_starts_with($imagePath, '/') || str_contains($imagePath, '://')) {
                return $imagePath;
            }
            // Otherwise, prepend base_url
            return $baseUrl . '/' . $imagePath;
        }

        // If no base_url, assume value is already a complete URL or relative path handled by frontend
        return $imagePath;
    }

    /**
     * This formatter returns a plain URL string, not HTML.
     * Therefore, it is NOT inherently safe HTML and should be escaped by AbstractFormatter
     * when placed into HTML attributes or content.
     */
    protected function isSafeHtml(): bool
    {
        return false; // Crucial change: It returns a URL string, which needs htmlspecialchars
    }

    // The inherited `sanitize` method from AbstractFormatter will now correctly
    // apply htmlspecialchars to the URL string returned by `transform`.
}
