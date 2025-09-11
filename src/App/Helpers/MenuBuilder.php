<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Enums\Url;
use App\Helpers\MyLinkHelper as LinkHelper;

class MenuBuilder
{
    /**
     * Create menu item data using new toLinkData format
     */
    public static function create(Url $url, array $params = []): array
    {
        return $url->toLinkData(null, $params);  // ← Use new format
    }

    /**
     * Create multiple menu items using new format
     */
    public static function createMultiple(array $urls): array
    {
        return array_map([self::class, 'create'], $urls);
    }

    /**
     * Render menu items as HTML - Updated for new data format
     */
    public static function renderItems(array $menuItems, string $currentPath, array $options = []): string
    {
        $html = '';
        $showIcons = $options['show_icons'] ?? false;
        $matchPrefix = $options['match_prefix'] ?? false;

        foreach ($menuItems as $item) {
            // Dropdown support
            if (isset($item['items']) && is_array($item['items'])) {
                $html .= '<li class="dropdown">';
                $html .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown">'
                    . htmlspecialchars($item['label']) . ' <span class="caret"></span></a>';
                $html .= '<ul class="dropdown-menu">';
                $html .= self::renderItems($item['items'], $currentPath, $options);
                $html .= '</ul></li>';
                continue;
            }



            // Check if current page matches
            $itemUrl = $item['url'];  // ← Use 'url' from new format
            $isActive = ($currentPath === $itemUrl);

            // Check prefix matching for sub-sections
            if (!$isActive && $matchPrefix) {
                $isActive = strpos($currentPath, $itemUrl . '/') === 0;
            }

            $activeClass = $isActive ? ' class="active"' : '';
            $html .= "<li{$activeClass}>";

            if ($showIcons) {
                // Use LinkHelper::render with new data format
                $html .= LinkHelper::render($item);
            } else {
                // Use LinkHelper::render with new data format
                $html .= LinkHelper::render($item);
            }

            $html .= "</li>";
        }

        return $html;
    }

    /**
     * Render category header
     */
    public static function renderCategory(string $title): string
    {
        return '<li class="menu-category">' . htmlspecialchars($title) . '</li>';
    }
}
