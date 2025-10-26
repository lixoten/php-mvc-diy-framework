<?php

declare(strict_types=1);

namespace Core\Navigation;

use App\ValueObjects\NavigationData;

/**
 * Interface for navigation menu renderers
 *
 * Defines the contract for rendering navigation menus
 * regardless of the underlying CSS framework
 */
interface NavigationRendererInterface
{
    /**
     * Render the main navigation menu
     *
     * @param NavigationData $navigationData The navigation data to render
     * @param string $currentPath The current path to highlight active items
     * @return string The rendered navigation HTML
     */
    public function renderMainNavigation(NavigationData $navigationData, string $currentPath): string;

    /**
     * Render a sub-navigation component
     *
     * @param array $items Sub-navigation items to render
     * @param string $navClass CSS class for the sub-navigation
     * @param string $currentPath Current path to determine active items
     * @return string Rendered HTML
     */
    public function renderSubNavigation(array $items, string $navClass, string $currentPath): string;
}