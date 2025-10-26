<?php

declare(strict_types=1);

namespace Core\Formatters;

/**
 * Formatter for rendering image HTML elements.
 */
class ImageFormatter extends AbstractFormatter
{
    public function getName(): string
    {
        return 'image';
    }

    public function supports(mixed $value): bool
    {
        return is_string($value) || is_null($value);
    }

    public function transform(mixed $value, array $options = []): string
    {
        if (empty($value)) {
            return '';
        }

        $baseUrl = $options['base_url'] ?? '/';
        $src = rtrim($baseUrl, '/') . '/' . ltrim((string)$value, '/');
        $alt = $options['alt'] ?? 'Image';
        $class = $options['class'] ?? '';
        $classAttr = $class ? ' class="' . htmlspecialchars($class) . '" ' : '';

        // return '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($alt) . '"' . $classAttr . '>';
           return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' .
               htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '"' . $classAttr . '>';
    }

    /**
     * Image formatter emits safe HTML (attributes already escaped).
     */
    protected function isSafeHtml(): bool
    {
        return true;
    }

    // public function sanitize(mixed $value): string
    // {
    //     // For images, sanitization is handled in format() via htmlspecialchars
    //     return $this->format($value);
    // }
}
