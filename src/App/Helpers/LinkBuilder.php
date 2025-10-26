<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Enums\Url;

/**
 * A helper class for generating HTML anchor (<a>) tags.
 *
 * This class provides static methods to create links from Url enum cases,
 * simplifying the process of generating consistent and safe links in views.
 * It supports text links, links with icons, and creation from a data array.
 */
class LinkBuilder
{
    /**
     * Generates a simple HTML text link.
     *
     * @param Url $url The URL enum case for the link's destination.
     * @param array $params Optional parameters for a parameterized URL (e.g., ['id' => 123]).
     * @param string|null $text Optional custom link text. If null, the URL's default label is used.
     * @param array $attributes Optional additional HTML attributes for the <a> tag (e.g., ['class' => 'nav-link']).
     * @return string The generated HTML <a> tag.
     *
     * @example
     * // Generates: <a href="/about" class="nav-link">About Us</a>
     * echo LinkBuilder::generateTextLink(Url::CORE_ABOUT, [], 'About Us', ['class' => 'nav-link']);
     */
    public static function generateTextLink(
        Url $url,
        array $params = [],
        ?string $text = null,
        array $attributes = []
    ): string {
        $href = $url->url($params);
        $linkText = $text ?? $url->label();

        $attrString = self::buildAttributes($attributes);

        return sprintf(
            '<a href="%s"%s>%s</a>',
            htmlspecialchars($href),
            $attrString,
            htmlspecialchars($linkText)
        );
    }


    /**
     * Generates an HTML link with a Font Awesome icon.
     *
     * Renders a link with an icon if one is defined in the Url enum.
     * If no icon is defined, it produces a text-only link.
     *
     * @param Url $url The URL enum case for the link's destination.
     * @param array $params Optional parameters for a parameterized URL.
     * @param string|null $text Optional custom link text. If null, the URL's default label is used.
     * @param array $attributes Optional additional HTML attributes for the <a> tag.
     * @return string The generated HTML <a> tag, with an <i> tag if an icon is available.
     *
     * @example
     * // Generates: <a href="/admin/dashboard" class="btn"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
     * echo LinkBuilder::generateIconLink(Url::ADMIN_DASHBOARD, [], 'Dashboard', ['class' => 'btn']);
     */
    public static function generateIconLink(
        Url $url,
        array $params = [],
        ?string $text = null,
        array $attributes = [],
        string $icon = null
    ): string {
        $href = $url->url($params);
        $linkText = $text ?? $url->label();
        // $icon = $url->icon();
        // $icon = $icon;
        $attrString = self::buildAttributes($attributes);

        $iconHtml = '';
        if ($icon) {
            $iconHtml = sprintf('<i class="%s"></i> ', htmlspecialchars($icon));
        }

        return sprintf(
            '<a href="%s"%s>%s%s</a>',
            htmlspecialchars($href),
            $attrString,
            $iconHtml,
            htmlspecialchars($linkText)
        );
    }

    /**
     * Generates an HTML link from a pre-structured data array.
     *
     * This is ideal for links whose data is prepared in a controller and passed to a view.
     *
     * @param array $linkData An associative array containing link properties.
     *   - 'href' (string) The destination URL.
     *   - 'text' (string) The link text.
     *   - 'icon' (string|null) The full Font Awesome icon class (e.g., 'fas fa-home').
     *   - 'attributes' (array) Optional additional HTML attributes.
     *   - 'show_icon' (bool) Optional. If false, the icon will be hidden even if provided. Defaults to true.
     * @return string The generated HTML <a> tag.
     *
     * @example
     * $linkData = [
     *   'href' => '/posts/edit/1',
     *   'text' => 'Edit Post',
     *   'icon' => 'fas fa-pencil-alt',
     *   'attributes' => ['class' => 'btn btn-primary']
     * ];
     * // Generates: <a href="/posts/edit/1" class="btn btn-primary"><i class="fas fa-pencil-alt"></i> Edit Post</a>
     * echo LinkBuilder::fromArray($linkData);
     */
    public static function fromArray(array $linkData): string
    {
        // Extract data from the array with safe defaults
        $href = $linkData['href'] ?? '#';
        $text = $linkData['text'] ?? '';
        $iconClass = $linkData['icon'] ?? $linkData['action'] ?? null;
        $attributes = $linkData['attributes'] ?? [];
        $showIcon = $linkData['show_icon'] ?? true;

        // Reuse the attribute builder
        $attrString = self::buildAttributes($attributes);

        if ($iconClass === 'index') {
            $iconClass = 'view';
        }



        // Prepare icon HTML, which will be empty if no icon is provided
        $iconHtml = '';
        if ($showIcon && $iconClass) { // Add the $showIcon check here
            $iconHtml = sprintf('<i class="%s"></i> ', htmlspecialchars($iconClass));
        }

        // Assemble the final link
        return sprintf(
            '<a href="%s"%s>%s%s</a>',
            htmlspecialchars($href),
            $attrString,
            $iconHtml, // This is either the <i> tag or an empty string
            htmlspecialchars($text)
        );
    }


    /**
     * Creates a link styled as a button, automatically adding the 'btn' class.
     *
     * @param Url $url The URL enum case.
     * @param array $params Optional URL parameters.
     * @param string|null $text Custom link text. Uses URL default if null.
     * @param array $attributes Additional HTML attributes.
     * @param bool $showIcon If false, generates a text-only button. Defaults to true.
     * @return string The generated HTML <a> tag.
     *
     * @example
     * // Generates: <a href="/posts/create" class="btn btn-success"><i class="fas fa-plus"></i> New Post</a>
     * echo LinkBuilder::generateButtonLink(Url::STORE_POST_CREATE, [], 'New Post', ['class' => 'btn-success']);
     */
    public static function generateButtonLink(
        Url $url,
        array $params = [],
        ?string $text = null,
        array $attributes = [],
        bool $showIcon = true
    ): string {
        // Build the class string robustly
        $classParts = ['btn'];
        if (!empty($attributes['class'])) {
            $classParts[] = $attributes['class'];
        }
        $attributes['class'] = implode(' ', $classParts);

        // Decide which generator to use based on the $showIcon flag.
        if ($showIcon) {
            // If we want an icon, call the method that tries to create one.
            return self::generateIconLink($url, $params, $text, $attributes);
        } else {
            // If we explicitly DO NOT want an icon, call the text-only method.
            return self::generateTextLink($url, $params, $text, $attributes);
        }
    }


    /**
     * Creates a link styled as a button from a pre-structured data array.
     *
     * This is a convenience method that wraps fromArray(), merging button classes
     * with any existing classes in the data.
     *
     * @param array $linkData An associative array containing link properties (see fromArray).
     * @param string $class The button classes to apply (e.g., 'btn btn-success'). Defaults to 'btn btn-primary'.
     * @return string The generated HTML <a> tag styled as a button.
     *
     * @example
     * $data = ['href' => '/home', 'text' => 'Go Home'];
     * // Generates: <a href="/home" class="btn btn-primary">Go Home</a>
     * echo LinkBuilder::buttonFromData($data);
     */
    public static function buttonFromData(array $linkData, string $class = 'btn btn-primary'): string
    {
        // Get existing classes from the data, if any
        $existingClasses = $linkData['attributes']['class'] ?? '';

        // Build the new class string robustly
        $classParts = [$class];
        if (!empty($existingClasses)) {
            $classParts[] = $existingClasses;
        }

        // Add the merged classes back into the data array
        $linkData['attributes']['class'] = implode(' ', $classParts);

        // Now call fromArray with the modified (and single) array
        return self::fromArray($linkData);
    }


    /**
     * Build HTML attributes string from an associative array.
     * Handles regular key="value" attributes and boolean attributes (e.g., 'disabled').
     *
     * @param array<string, string|bool> $attributes
     * @return string
     */
    private static function buildAttributes(array $attributes): string
    {
        if (empty($attributes)) {
            return '';
        }

        $attrParts = [];
        foreach ($attributes as $key => $value) {
            if (is_bool($value)) {
                if ($value) { // If it's true, add the key
                    $attrParts[] = $key;
                }
                // If false, do nothing
            } else { // For regular attributes
                $attrParts[] = sprintf('%s="%s"', $key, htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'));
            }
        }
        return ' ' . implode(' ', $attrParts); // Prepend one space
    }
}
