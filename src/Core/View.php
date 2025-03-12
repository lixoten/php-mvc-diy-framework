<?php

declare(strict_types=1);

namespace Core;

use App\Scrap;
use App\ViewHelpers\FlashMessageRendererView;

/**
 * View
 */
class View
{
    private string $mmsg;

    private string $page;
    private string $op;
    private int $id;


    public function __construct()
    {
        // $this->scrapObj = $scrapObj;
        // $this->flashObj = $flashObj;
    }


    ## CLASSIC Tree Structure ## SAVEME
    public function getTemplate($template, $data = [])
    {
        //Debug::p(xxx);
        extract($data);
        $feature = $this->convertToPath($template);
        //Debug::p($template);
        include __DIR__ . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "App/Features" .
                DIRECTORY_SEPARATOR . $feature;
        //include $rrr;
        // $content = ob_get_contents(); // Get the output buffer content
        // ob_end_clean(); // Clean the output buffer
        // echo $content; // Echo the content to the browser
        // return $content; // Return the content
        return ob_get_clean();
    }

    ## FEATURE Tree Structure
    private function convertToPath($template)
    {
        // Split the template into parts
        $parts = explode('/', $template);

        if (count($parts) >= 3 && strtolower($parts[0]) === 'admin') {
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


    public function render(string $view, array $data = [])
    {
        $flashRenderer = new FlashMessageRendererView($data['flash']);
        $data['flashRenderer'] = $flashRenderer;
        unset($data['flash']);

        extract($data);

        $path = __DIR__ . "../../app/Views/{$view}.html";
        if ($path && file_exists($path)) {
            //Debug::p($view);
            include $path;
        } else {
            echo "----File not found or path is incorrect: " . __DIR__ . '/../base6.html';
        }
    }

    public function renderWithLayout(string $view, array $data = [])
    {
        // $flashRenderer = new FlashMessageRendererView($data['flash']);
        // $data['flashRenderer'] = $flashRenderer;

        $content = $this->getTemplate($view, $data);

        $data = array_merge(['content' => $content], $data);


        if (isset($data['layout']) && ($data['layout'] === 'error')) {
            // $layout = 'layouts/base8Error';
            $layout = 'layouts/base8ErrorSimple';
            //exit();
        } else {
            // $layout = 'layouts/base5';
            $layout = 'layouts/base5simple';
        }

        $this->render($layout, $data);
    }


    public function ren($return_arr): never
    {
        echo json_encode($return_arr);
        exit();
    }
}
# 244 119
