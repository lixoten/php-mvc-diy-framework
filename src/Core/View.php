<?php

declare(strict_types=1);

namespace Core;

use App\Scrap;
use App\ViewHelpers\FlashMessageRendererView;
use App\Helpers\DebugRt as Debug;
use Core\Services\ConfigService;

/**
 * View
 */
class View
{
    private string $mmsg;

    private string $page;
    private string $op;
    private int $id;
    private ConfigService $config;


    public function __construct(ConfigService $config)
    {
        $this->config = $config;
        // $this->scrapObj = $scrapObj;
        // $this->flashObj = $flashObj;
    }


    public function getTemplate($template, $data = [])
    {
        //Debug::p($template);
        extract($data);
        //Debug::p($template);

        $feature = $this->convertToPath($template);
        //Debug::p($feature);
        if (strpos($template, 'errors/') === 0) {
            $path = __DIR__ . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Core" .
            DIRECTORY_SEPARATOR . $feature;
        } else {
            $path = __DIR__ . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "App/Features" .
            DIRECTORY_SEPARATOR . $feature;
        }
        //Debug::p($path);

        if (!file_exists($path)) {
            // Log the problem
            error_log("Template file not found: $path");
            return "<p>Template not found: $template</p>";
        }
        //Debug::p($path);

        // Start output buffering and include the template
        ob_start();
        include $path;
        return ob_get_clean();
    }


    private function convertToPath($template)
    {
        // Split the template into parts
        $parts = explode('/', $template);

        if (count($parts) >= 3 && strtolower($parts[0]) === 'admin' || strtolower($parts[0]) === 'account') {
            // For admin templates like "admin/dashboard/index"
            // Take "admin" and the feature name together
            $adminPart = array_shift($parts); // Get "admin"
            $featurePart = array_shift($parts); // Get "dashboard"
            $feature = ucfirst($adminPart) . DIRECTORY_SEPARATOR . ucfirst($featurePart);
        } else {
            // The first part is the feature (e.g., Home, Posts, Users)
            // For regular templates like "home/index"
            $feature = ucfirst(array_shift($parts));
        }


        // The remaining parts are the path to the template file
        $path = implode(DIRECTORY_SEPARATOR, $parts) . '.php';

        // Construct the full path within the feature
        return $feature . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . $path;
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
        // Handle flash messages
        if (isset($data['flash'])) {
            $flashRenderer = new FlashMessageRendererView($data['flash']);
            $data['flashRenderer'] = $flashRenderer;
            unset($data['flash']);
        }

        extract($data);

        $path = __DIR__ . "../../app/Views/{$view}.html";

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

        if (isset($data['layout']) && ($data['layout'] === 'error')) {
            // $layout = 'layouts/base8Error';
            $layout = 'layouts/base8ErrorSimple';
            //exit();
        } else {
            // $layout = 'layouts/base5';
            $layout = 'layouts/base5simple';
        }

        // Return the rendered layout
        return $this->render($layout, $data);
    }
}
# 244 119
