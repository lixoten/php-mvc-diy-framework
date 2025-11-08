<?php

declare(strict_types=1);

namespace Core;

use App\Scrap;
use App\ViewHelpers\FlashMessageRendererView;
use App\Helpers\DebugRt;
use Core\Services\ConfigService;
use Core\Services\ThemeAssetService;
use Core\Services\ThemeConfigurationManagerService;
use Core\Services\ThemePreviewService;
use Core\Services\ThemeServiceInterface;

/**
 * View
 */
class View
{
    /**
     * @var ThemeConfigurationManagerService Theme manager
     */
    private ThemeConfigurationManagerService $themeManager;

    /**
     * @var ThemeServiceInterface|null Cached active theme service
     */
    private ?ThemeServiceInterface $activeTheme = null;


    /**
     * @var ThemeAssetService Theme asset service
     */
    private ThemeAssetService $themeAsset;

    /**
     * @var ThemePreviewService Theme preview service
     */
    private ThemePreviewService $themePreview;


    private string $msg;

    private string $page;
    private string $op;
    private int $id;
    private ConfigService $config;


    public function __construct(
        ConfigService $config,
        ThemeConfigurationManagerService $themeManager,
        ThemeAssetService $themeAsset,
        ThemePreviewService $themePreview
    ) {
        $this->config = $config;
        $this->themeManager = $themeManager;

        $this->themeAsset = $themeAsset;
        $this->themePreview = $themePreview;

        // Apply theme preview if active
        $this->themePreview->applyPreviewIfActive();
        // $this->scrapObj = $scrapObj;
        // $this->flashObj = $flashObj;
    }


    public function getTemplate($template, $data = [])
    {
        // $template = $template . "D";
        //Debug::p($template);
        extract($data);
        //Debug::p($template);

        $feature = $this->convertToPath($template);
        //                                     "store/dashboard/index"
        //"Account\Dashboard\Views\index.php" "account/dashboard/index"

        //Debug::p($feature);
        if (strpos($template, 'errors/') === 0) {
            $path = __DIR__ . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Core" .
            DIRECTORY_SEPARATOR . $feature;
        } else {
            $path = __DIR__ . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "App/Features" .
            DIRECTORY_SEPARATOR . $feature;
        }
        //Debug::p($path);
        //"D:\xampp\htdocs\my_projects\mvclixo\src\Core..\..\App/Features\Store\Views\dashboard\index.php"
        //"D:\xampp\htdocs\my_projects\mvclixo\src\Core..\..\App/Features\Account\Dashboard\Views\index.php"
        //"D:\xampp\htdocs\my_projects\mvclixo\src\Core..\..\App/Features\Testy\Views\edit.php"
        //"D:\xampp\htdocs\my_projects\mvclixo\src\Core..\..\App/Features\ Posts\Views\edit.php"
        // D:\xampp\htdocs\my_projects\mvclixo\src\App\Features\Testy\Views\edit .php
        //D:\xampp\htdocs\my_projects\mvclixo\src\App\Features\Posts\Views\edit.php

        if (!file_exists($path)) {
            // Log the problem
            // DebugRt::j('1', '', '111');
            error_log("Template file not found: $path");
            return "<p>Template not found: $template</p>";
        }
        //Debug::p($path);

        //"D:\xampp\htdocs\my_projects\mvclixo\src\Core..\..\App/Features\Post\Views\index.php"
        //"D:\xampp\htdocs\my_projects\mvclixo\src\Core..\..\App/Features\Testy\Views\list.php"

        // Start output buffering and include the template
        ob_start();
        include $path;
        return ob_get_clean();
    }


    private function convertToPath($template)
    {
        // Split the template into parts
        $parts = explode('/', $template);

        // if (count($parts) >= 4 && strtolower($parts[1]) === 'store') {
        //     // For admin templates like "admin/dashboard/index"
        //     // Take "admin" and the feature name together
        //     $accountPart = array_shift($parts); // Get "admin"
        //     $storePart = array_shift($parts); // Get "admin"
        //     $featurePart = array_shift($parts); // Get "dashboard"
        //     $feature = ucfirst($accountPart) . DIRECTORY_SEPARATOR .
        //                ucfirst($storePart) . DIRECTORY_SEPARATOR .
        //                ucfirst($featurePart);
        // } else
        if (
            (count($parts) >= 3 && strtolower($parts[0]) === 'admin') ||
            strtolower($parts[0]) === 'account' ||
            strtolower($parts[0]) === 'store'
        ) {
            // For admin templates like "admin/dashboard/index"
            // Take "admin" and the feature name together
            $adminPart = array_shift($parts); // Get "admin"
            $featurePart = array_shift($parts); // Get "dashboard"
            $feature = ucfirst($adminPart) . DIRECTORY_SEPARATOR . ucfirst($featurePart);
        // } elseif (strtolower($parts[0]) === 'auth') {
        //     // The first part is the feature (e.g., Home, Posts, Users)
        //     // For regular templates like "home/index"
        //     $feature = $template;
        } else {
            $feature = ucfirst(array_shift($parts));
        }

        $cssFramework = $this->themeManager->getActiveTheme();

        // The remaining parts are the path to the template file
        $path = implode(DIRECTORY_SEPARATOR, $parts) . '.php';

        // Construct the full path within the feature
        $rrr = $feature . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR
                                                        . $cssFramework . DIRECTORY_SEPARATOR . $path;
        return $rrr;
        // return $feature . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR
                                                        // . $cssFramework . DIRECTORY_SEPARATOR . $path;
    }


    /**
     * Render a view template
     *
     * @param string $view The view file
     * @param array $data Parameters to pass to the view
     * @return string The rendered view
     */
    public function render(string $view, array $data = []): string
    {
        // Get active theme once and cache it
        if ($this->activeTheme === null) {
            $this->activeTheme = $this->themeManager->getActiveThemeService();
        }

        // Make theme service available to all templates
        $data['theme'] = $this->activeTheme;
        $data['themeAssets'] = $this->themeAsset;
        $data['themePreview'] = $this->themePreview;

        // Handle flash messages
        if (isset($data['flash'])) {
            $flashRenderer = new FlashMessageRendererView($data['flash']);
            $data['flashRenderer'] = $flashRenderer;
            unset($data['flash']);
        }

        extract($data);

        $path = dirname(__DIR__, 1) . "/App/Views/{$view}.php";

        // Start output buffering
        ob_start();

        if ($path && file_exists($path)) {
            include $path;
        } else {
            echo "<p>Layout template not found: $view</p>";
        }

        // Return the buffered content
        return ob_get_clean();
    }

    /**
     * Render a view with a layout
     *
     * @param string $view The view file
     * @param array $data Parameters to pass to the view
     * @return string The rendered view with layout
     */
    public function renderWithLayout(string $view, array $data = []): string
    {
        // Make config available to all views
        //$config = $this->container->get('config');
        // $theme = $this->config->get('view')['themes']['default'];
        // $theme = $this->config->getConfigValue('view', 'themes.default');
        // $theme = $this->config->getConfigValue('view', "themes.available.{$theme}.css", '');
        // Debug::p($theme);

        //exit();
        //Debug::p($view);
        $content = $this->getTemplate($view, $data);
        //Debug::p($content);

        $data = array_merge(['content' => $content], $data);

        if (isset($data['layout'])) {
            if ($data['layout'] === 'error') {
                // $layout = 'layouts/base8Error';
                $themeLayout = $this->themeManager->getActiveThemeService()->getViewLayout('error');
                $layout = $themeLayout ?? 'layouts/base5simple'; // Fallback to your existing layout
                // $layout = 'layouts/base8ErrorSimple';
                //exit();
            } else {  //  layout = abend
                $themeLayout = $this->themeManager->getActiveThemeService()->getViewLayout('abend');
                $layout = $themeLayout ?? 'template_abend'; // Fallback to your existing layout
            }
        } else {
            // $layout = 'layouts/base5';
            // $layout = 'layouts/base5simple';
            $themeLayout = $this->themeManager->getActiveThemeService()->getViewLayout();
            // DebugRt::j('0', 'Core/View themeManager: activeVariant:', $this->themeManager->getActiveVariant() ?? 'None');
            // DebugRt::j('0', 'Core/View themeAsset: activeVariant:', $this->themeAsset->getActiveVariant() ?? 'None');

            $layout = $themeLayout ?? 'layouts/base5simple'; // Fallback to your existing layout
            // $layout = 'layouts/basePreTheme'; // Fallback to your existing layout
            // $layout = 'layouts/base5simple'; // Fallback to your existing layout
            // $layout = 'layouts/pimp1'; // Fallback to your existing layout
            // $layout = 'layouts/pimp2'; // Fallback to your existing layout
            $rrr = 5;
        }

        // Return the rendered layout
         return $this->render($layout, $data);
    }

    /**
     * Get the active theme service
     *
     * @return ThemeServiceInterface
     */
    public function getThemeService(): ThemeServiceInterface
    {
        if ($this->activeTheme === null) {
            $this->activeTheme = $this->themeManager->getActiveThemeService();
        }

        return $this->activeTheme;
    }
}
# 244 119 246
