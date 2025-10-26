<?php

/**
 * Main navigation menu component
 *
 * Renders the main application navigation using the appropriate renderer for the active theme.
 */

declare(strict_types=1);

use App\Services\NavigationService;
use Core\Services\ThemeConfigurationManagerService;

/**
 * Main navigation menu component
 *
 * @var string $currentPath
 * @var object $navigationRenderer
 * @var object $navigationData
 */


// Get the container
// global $container;

// // Get current path
// $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// // Get navigation service
// $navigationService = $container->get(NavigationService::class);

// // Get theme configuration manager to determine active theme
// $themeManager = $container->get(ThemeConfigurationManagerService::class);

// // Get appropriate renderer for the active theme
// $activeTheme = $themeManager->getActiveTheme();
// $rendererClass = match ($activeTheme) {
//     'bootstrap' => 'Core\Navigation\Bootstrap\BootstrapNavigationRendererService',
//     'material' => 'Core\Navigation\Material\MaterialNavigationRendererService',
//     default => 'Core\Navigation\Bootstrap\BootstrapNavigationRendererService',
// };

// $navigationRenderer = $container->get($rendererClass);

// // Build navigation data
// $navigationData = $navigationService->buildNavigation($currentPath);

// Render the main navigation
echo $navigationRenderer->renderMainNavigation($navigationData, $currentPath);

// Render sub-navigation if needed
if ($navigationData->shouldShowSubNav()  && !empty($navigationData->getSubNavItems())) {
    echo $navigationRenderer->renderSubNavigation(
        $navigationData->getSubNavItems(),
        $navigationData->getSubNavClass() ?? '',
        $currentPath
    );
}
