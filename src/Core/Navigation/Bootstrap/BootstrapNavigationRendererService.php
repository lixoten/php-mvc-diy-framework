<?php

declare(strict_types=1);

namespace Core\Navigation\Bootstrap;

use Core\Navigation\NavigationRendererInterface;
use App\ValueObjects\NavigationData;
use Core\Services\ThemeServiceInterface;

/**
 * Bootstrap-specific navigation renderer service
 *
 * Renders navigation components using Bootstrap's HTML structure
 * while applying theme-specific visual styling through ThemeService
 */
class BootstrapNavigationRendererService implements NavigationRendererInterface
{
    /**
     * @param ThemeServiceInterface $themeService Theme service for visual styling classes
     */
    public function __construct(
        private ThemeServiceInterface $themeService
    ) {
    }

    /**
     * Render the main navigation menu using Bootstrap structure
     *
     * @param NavigationData $navigationData The navigation data to render
     * @param string $currentPath The current path to highlight active items
     * @return string The rendered navigation HTML
     */
    public function renderMainNavigation(NavigationData $navigationData, string $currentPath): string
    {
        // Get theme-specific styling classes (these will change based on the active visual theme)
        $navbarClass = $this->themeService->getElementClass('navbar')
                                                                    ?: 'navbar navbar-expand-lg navbar-light bg-light';
        $navbarBrandClass = $this->themeService->getElementClass('navbar.brand') ?: 'navbar-brand';
        $navbarTogglerClass = $this->themeService->getElementClass('navbar.toggler') ?: 'navbar-toggler';

        $html = <<<HTML
        <nav class="{$navbarClass}" id="main-navigation">
            <div class="container-fluid">
                <a class="{$navbarBrandClass}" href="/">MVC LIXO</a>
                <button class="{$navbarTogglerClass}" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarContent" aria-controls="navbarContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        {$this->renderMenuItems($navigationData->getPublicItems(), $currentPath)}
                    </ul>

                    <ul class="navbar-nav mb-2 mb-lg-0">
                            <!-- <div class="last-items-container"> -->

                        {$this->renderMenuItems($navigationData->getGuestItems(), $currentPath)}
                <!-- </div> -->
                        {$this->renderNestedItems($navigationData->getAccountItems(), $currentPath)}
                        {$this->renderNestedItems($navigationData->getStoreItems(), $currentPath)}
                        {$this->renderNestedItems($navigationData->getAdminItems(), $currentPath)}
                    </ul>
                </div>
            </div>
        </nav>
        HTML;

        // If subnav is enabled, render it
        if ($navigationData->shouldShowSubNav() && !empty($navigationData->getSubNavItems())) {
            $html .= $this->renderSubNavigation(
                $navigationData->getSubNavItems(),
                $navigationData->getSubNavClass(),
                $currentPath
            );
        }

        return $html;
    }

    /**
     * Render a list of menu items with Bootstrap markup
     *
     * @param array $items The menu items to render
     * @param string $currentPath The current path to highlight active items
     * @return string The rendered menu items HTML
     */
    private function renderMenuItems(array $items, string $currentPath): string
    {
        if (empty($items)) {
            return '';
        }

        $html = '';

        foreach ($items as $item) {
            $url = $item['url'] ?? '#';
            $label = htmlspecialchars($item['label'] ?? '');
            $icon = isset($item['icon']) ? $this->themeService->getIconHtml($item['icon']) : '';
            $isActive = $this->isActiveUrl($url, $currentPath);
            $activeClass = $isActive ? ' ' . ($this->themeService->getElementClass('active') ?: 'active') : '';
            $navItemClass = $this->themeService->getElementClass('nav.item') ?: 'nav-item';
            $navLinkClass = $this->themeService->getElementClass('nav.link') ?: 'nav-link';
            $ariaCurrent = $isActive ? ' aria-current="page"' : '';

            $html .= <<<HTML
            <li class="{$navItemClass}">
                <a href="{$url}" class="{$navLinkClass}{$activeClass}"{$ariaCurrent}>
                    {$icon}{$label}
                </a>
            </li>
            HTML;
        }

        return $html;
    }

    /**
     * Render nested menu items with Bootstrap dropdowns
     *
     * @param array $nestedItems The nested menu items to render
     * @param string $currentPath The current path to highlight active items
     * @return string The rendered nested menu items HTML
     */
    private function renderNestedItems(array $nestedItems, string $currentPath): string
    {
        if (empty($nestedItems)) {
            return '';
        }

        $html = '';

        foreach ($nestedItems as $group) {
            $label = $group['label'] ?? '';
            $items = $group['items'] ?? [];

            if (empty($items)) {
                continue;
            }

            // Check if any item in this group is active
            $hasActiveItem = false;
            foreach ($items as $item) {
                if ($this->isActiveUrl($item['url'] ?? '', $currentPath)) {
                    $hasActiveItem = true;
                    break;
                }
            }

            $activeClass = $hasActiveItem ? ' active' : '';

            $html .= <<<HTML
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle{$activeClass}" href="#" role="button"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    {$label}
                </a>
                <ul class="dropdown-menu">
            HTML;

            foreach ($items as $item) {
                $url = $item['url'] ?? '#';
                $itemLabel = $item['label'] ?? '';
                $isActive = $this->isActiveUrl($url, $currentPath);
                $itemActiveClass = $isActive ? ' active' : '';

                $html .= <<<HTML
                <li>
                    <a class="dropdown-item{$itemActiveClass}" href="{$url}">
                        {$itemLabel}
                    </a>
                </li>
                HTML;
            }

            $html .= "</ul></li>";
        }

        return $html;
    }

    /**
     * Render sub-navigation bar with Bootstrap styling
     *
     * @param array $items Sub-navigation items
     * @param string $navClass CSS class for the sub-navigation
     * @param string $currentPath Current path to determine active items
     * @return string Rendered HTML
     */
    public function renderSubNavigation(array $items, string $navClass, string $currentPath): string
    {
        if (empty($items)) {
            return '';
        }

        // Get theme-specific styling class (this will change based on the active visual theme)
        $subNavClass = $this->themeService->getElementClass('subnav') ?: 'bg-light py-2 border-bottom';

        $html = <<<HTML
        <div class="{$subNavClass} {$navClass}">
            <div class="container">
                <div class="row">
                    <div class="col">
                        <ul class="nav">
        HTML;

        foreach ($items as $item) {
            $url = $item['url'] ?? '#';
            $label = htmlspecialchars($item['label'] ?? '');
            $isActive = $this->isActiveUrl($url, $currentPath);
            $activeClass = $isActive ? ' active' : '';
            $ariaCurrent = $isActive ? ' aria-current="page"' : '';

            $html .= <<<HTML
            <li class="nav-item">
                <a href="{$url}" class="nav-link px-2{$activeClass}"{$ariaCurrent}>
                    {$label}
                </a>
            </li>
            HTML;
        }

        $html .= <<<HTML
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        HTML;

        return $html;
    }

    /**
     * Check if a URL matches the current path
     *
     * @param string $url The URL to check
     * @param string $currentPath The current path
     * @return bool True if the URL is active
     */
    private function isActiveUrl(string $url, string $currentPath): bool
    {
        // Simple match for now, could be more sophisticated
        return $url === $currentPath ||
               ($url !== '/' && strpos($currentPath, $url) === 0);
    }
}
