<?php

declare(strict_types=1);

namespace Core\Formatters;

use Core\Services\ThemeServiceInterface;
use Core\Context\CurrentContext;
use Core\Interfaces\ConfigInterface;
use Core\Services\ImageStorageServiceInterface;

/**
 * Image Link Formatter
 *
 * Renders an image thumbnail with optional link wrapper.
 *
 * Supported options:
 * - 'preset': Image preset ('thumbs', 'web', 'original') for ImageStorageService
 * - 'base_path': Fallback for static images (e.g., '/assets/images/') if 'preset' not used
 * - 'default_image': Fallback image if value is null/empty (e.g., '/assets/images/default-avatar.png')
 * - 'alt_field': Record field to use for alt text (e.g., 'title', 'generic_text')
 * - 'link_to': URL pattern for wrapping image in link (e.g., '/testy/view/{id}')
 * - 'width': Image width (default: null, uses CSS)
 * - 'height': Image height (default: null, uses CSS)
 * - 'css_class': Custom CSS class for <img> tag
 *
 * @package Core\Formatters
 */
class ImageLinkFormatter extends AbstractFormatter
{
    /**
     * @param ThemeServiceInterface $themeService Service for retrieving theme-specific CSS classes.
     * @param ImageStorageServiceInterface $imageStorageService Service for resolving image paths and URLs.
     * @param CurrentContext $currentContext Service for retrieving the current store context.
     */
    public function __construct(
        private ThemeServiceInterface $themeService,
        private ImageStorageServiceInterface $imageStorageService,
        private CurrentContext $currentContext,
        private ConfigInterface $configService
    ) {
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'image_link';
    }

    /** {@inheritdoc} */
    public function supports(mixed $value): bool
    {
        // Supports strings (filenames/hashes) and null (will use default image)
        return is_string($value) || $value === null;
    }

    /**
     * Transform image value (hash or hash.ext) into HTML.
     *
     * @param mixed $value Image hash or hash.ext
     * @param array<string,mixed> $options Formatter options
     *   - preset: 'thumbs'|'web'|...
     *   - default_image: fallback URL
     *   - alt_field: record field name to use for alt text
     *   - link_to: optional link pattern '/testy/view/{id}'
     *   - width / height: optional overrides
     * @param array<string,mixed> $record Full record (for alt/link replacements)
     * @return string HTML snippet (<img> or <picture>)
     */
    public function transform(mixed $value, array $options = [], mixed $originalValue = null): string
    {
        // Merge defaults and read record from options (AbstractListRenderer injects 'record' into options)
        $options = $this->mergeOptions($options);
        $record = $options['record'] ?? [];
        $defaultImage = $options['default_image'] ?? '';

        if (empty($value)) {
            return $this->wrapWithLink($defaultImage, $options, true, $record);
        }

        // If value contains an extension (hash.ext), use that explicitly
        $hash = (string)$value;
        $pathInfo = pathinfo($hash);
        $explicitExt = null;
        if (!empty($pathInfo['extension']) && $pathInfo['filename'] !== '') {
            $explicitExt = strtolower(ltrim((string)$pathInfo['extension'], '.'));
            $hash = (string)$pathInfo['filename'];
        }

        $preset = (string)($options['preset'] ?? 'thumbs');
        $storeId = $options['store_id'] ?? $this->currentContext->getStoreId();
        if ($storeId === null) {
            return $this->wrapWithLink($defaultImage, $options, true, $record);
        }

        // If explicit extension provided, prefer a single URL (fast path)
        if ($explicitExt !== null) {
            // ✅ Use getUrl with explicit extension
            $url = $this->imageStorageService->getUrl($hash, (int)$storeId, $preset, $explicitExt);
            return $this->wrapWithLink($this->buildImgTag($url, $options, $record), $options, false, $record);
        }

        // Probe available formats (getUrls handles all discovered formats)
        $urls = $this->imageStorageService->getUrls($hash, (int)$storeId, $preset);

        if (empty($urls)) {
            // ✅ No formats found - use default image
            return $this->wrapWithLink($defaultImage, $options, true, $record);
        }

        // If only one format found, render simple <img>
        if (count($urls) === 1) {
            $url = array_values($urls)[0];
            return $this->wrapWithLink($this->buildImgTag($url, $options, $record), $options, false, $record);
        }

        // Multiple formats -> build <picture>
        $sources = '';
        $mimeMap = [
            'avif' => 'image/avif',
            'webp' => 'image/webp',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
        ];

        // ✅ Use preferred_formats from config (don't force order, just use what exists)
        $preferred = $this->configService->get('storage.image_generation.preferred_formats')
            ?? array_keys($urls); // Fallback to discovered formats

        foreach ($preferred as $ext) {
            if (isset($urls[$ext])) {
                $type = $mimeMap[$ext] ?? 'image/' . $ext;
                $sources .= sprintf(
                    '<source srcset="%s" type="%s">',
                    htmlspecialchars($urls[$ext]),
                    htmlspecialchars($type)
                );
            }
        }

        // Ensure there's a fallback <img> - prefer jpg/png if available, otherwise use first
        $fallbackExt = null;
        foreach (['jpg', 'jpeg', 'png'] as $e) {
            if (isset($urls[$e])) {
                $fallbackExt = $e;
                break;
            }
        }
        if ($fallbackExt === null) {
            $fallbackUrl = reset($urls);
        } else {
            $fallbackUrl = $urls[$fallbackExt];
        }

        $imgTag = $this->buildImgTag($fallbackUrl, $options, $record);
        $picture = sprintf('<picture>%s%s</picture>', $sources, $imgTag);

        return $this->wrapWithLink($picture, $options, false, $record);
    }

    private function buildImgTag(string $url, array $options, array $record = []): string
    {
        $preset = (string)($options['preset'] ?? 'thumbs');
        $width = $options['width'] ?? $this->configService->get("storage.image_presets.{$preset}.width");
        $height = $options['height'] ?? $this->configService->get("storage.image_presets.{$preset}.height");

        $alt = (string)($options['alt_text'] ?? ($record[$options['alt_field']] ?? 'Image'));
        $class = $options['css_class'] ?? $this->themeService->getElementClass('image_link.img') ?? '';

        $attrs = [
            'src' => (string)$url,
            'alt' => (string)$alt,
        ];
        if ($class !== '') {
            $attrs['class'] = (string)$class;
        }
        if ($width !== null) {
            $attrs['width'] = (int)$width;
        }
        if ($height !== null) {
            $attrs['height'] = (int)$height;
        }

        $attrString = '';
        foreach ($attrs as $k => $v) {
            $attrString .= sprintf(' %s="%s"', $k, htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE));
        }

        return sprintf('<img%s>', $attrString);
    }


    /**
     * Wraps the inner HTML (an <img> or <picture> tag) with an optional <a> link.
     *
     * If $isUrlFallback is true, it means $innerHtmlOrUrl is a raw URL (e.g., a default image URL)
     * which needs to be converted into an <img> tag before potential wrapping.
     *
     * @param string $innerHtmlOrUrl The HTML content (<img> or <picture>) or a raw URL if $isUrlFallback is true.
     * @param array<string, mixed> $options Formatter options, potentially containing 'link_to', 'link_css_class'.
     * @param bool $isUrlFallback True if $innerHtmlOrUrl is a raw URL needing <img> tag creation.
     * @param array<string, mixed> $record The full record data, used for resolving '{id}' in 'link_to' URL patterns.
     * @return string The final HTML string, potentially wrapped in an <a> tag.
     */
    private function wrapWithLink(
        string $innerHtmlOrUrl,
        array $options,
        bool $isUrlFallback,
        array $record = [],
    ): string {
        // If caller passed a raw URL as fallback (isUrlFallback=true), build an <img> tag around it
        $content = $isUrlFallback ? $this->buildImgTag($innerHtmlOrUrl, $options, $record) : $innerHtmlOrUrl;

        if (!empty($options['link_to'])) {
            $linkUrl = str_replace('{id}', (string)($record['id'] ?? ''), (string)$options['link_to']);
            $linkClass = $options['link_css_class'] ?? $this->themeService->getElementClass('image_link.a') ?? '';
            return sprintf(
                '<a href="%s" class="%s">%s</a>',
                htmlspecialchars($linkUrl, ENT_QUOTES | ENT_SUBSTITUTE),
                htmlspecialchars((string)$linkClass, ENT_QUOTES | ENT_SUBSTITUTE),
                $content
            );
        }

        return $content;
    }

    /** {@inheritdoc} */
    protected function isSafeHtml(): bool
    {
        // This formatter produces safe HTML with proper escaping
        return true;
    }

    /** {@inheritdoc} */
    protected function getDefaultOptions(): array
    {
        return [
            'preset' => null, // 'thumbs', 'web', 'original' - if used, overrides 'base_path'
            'base_path' => '/uploads/profiles/', // Fallback if 'preset' is null/not used (for static images)
            'default_image' => '/assets/images/default-avatar.png', // Fallback image if value is empty
            'alt_field' => 'title', // Default field for alt text
            'link_to' => null, // No link by default
            'width' => null, // Let CSS handle sizing
            'height' => null,
            'css_class' => null, // Will use ThemeService default
            'store_id' => null, // Can be explicitly passed, otherwise derived from CurrentContext
        ];
    }
}
