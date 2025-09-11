<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Enums\Url;

class MyLinkHelper
{
    /**
     * Create link from data array
     *
     * @param array{url: string, label: string, icon?: string|null} $linkData
     * @param array<string, string> $attributes
     * @return string
     */
    public static function render(array $linkData, array $attributes = []): string
    {
        $href = $linkData['url'];
        $text = $linkData['label'];
        $icon = $linkData['icon'] ?? null;

        $attrString = self::buildAttributes($attributes);

        $html = '<a href="' . htmlspecialchars($href) . '"' . $attrString . '>';

        if ($icon) {
            $html .= '<i class="fa ' . htmlspecialchars($icon) . '"></i> ';
        }

        $html .= htmlspecialchars($text) . '</a>';

        return $html;
    }

    /**
     * Create link directly from URL enum
     *
     * @param Url $url
     * @param string|null $text
     * @param array<string, string|int> $params
     * @param array<string, string> $attributes
     * @return string
     *
     * usage
     * - MyLinkHelper::generateTextLink(Url::USER_LIST)
     * - MyLinkHelper::generateTextLink(Url::USER_EDIT, 'Edit User', ['id' => 123])
     * - MyLinkHelper::generateTextLink(
     *       Url::USER_VIEW,
     *       null,
     *       ['id' => 123],
     *       ['class' => 'btn btn-link', 'target' => '_blank']
     *   )
     */
    public static function generateTextLink(
        Url $url,
        ?string $text = null,
        array $params = [],
        array $attributes = []
    ): string {
        $href = $url->url($params);
        $linkText = $text ?? $url->label();

        $attrString = self::buildAttributes($attributes);

        return '<a href="' . htmlspecialchars($href) . '"' . $attrString . '>' .
               htmlspecialchars($linkText) . '</a>';
    }


    /**
     * Create link directly with icon from URL enum
     *
     * @param Url $url
     * @param string|null $text
     * @param array<string, string|int> $params
     * @param array<string, string> $attributes
     * @return string
     *
     * usage
     * - MyLinkHelper::generateIconLink(Url::USER_LIST)
     * - MyLinkHelper::generateIconLink(Url::USER_EDIT, 'Edit User', ['id' => 123])
     * - MyLinkHelper::generateIconLink(
     *       Url::USER_VIEW,
     *       null,
     *       ['id' => 123],
     *       ['class' => 'btn btn-link', 'target' => '_blank']
     *   )
     */
    public static function generateIconLink(
        Url $url,
        ?string $text = null,
        array $params = [],
        array $attributes = []
    ): string {
        $icon = $url->icon();
        $linkText = $text ?? $url->label();
        $href = $url->url($params);

        if ($icon) {
            $iconHtml = '<i class="fa ' . htmlspecialchars($icon) . '"></i> ';
            $attrString = self::buildAttributes($attributes);

            return '<a href="' . htmlspecialchars($href) . '"' . $attrString . '>' .
                   $iconHtml . htmlspecialchars($linkText) . '</a>';
        } else {
            // No icon, fallback to regular link
            return self::generateTextLink($url, $linkText, $params, $attributes);
        }
    }

    /**
     * Create button-styled link
     */
    public static function generateButtonLink(
        Url $url,
        ?string $text = null,
        string $class = 'btn btn-primary',
        array $params = []
    ): string {
        return self::generateTextLink($url, $text, $params, ['class' => $class]);
    }

    /**
     * Create button from data array
     */
    public static function buttonFromData(array $linkData, string $class = 'btn btn-primary'): string
    {
        return self::render($linkData, ['class' => $class]);
    }


    /**
     * Build HTML attributes string
     *
     * @param array<string, string> $attributes
     * @return string
     */
    private static function buildAttributes(array $attributes): string
    {
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }
        return $attrString;
    }
}
