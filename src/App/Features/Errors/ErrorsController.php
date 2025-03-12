<?php

declare(strict_types=1);

namespace App\Features\Errors;

use Core\Controller;
use App\Helpers\DebugRt as Debug;
use App\Helpers\FlashMessages;
use App\Helpers\Redirector;
use App\Helpers\ReturnPageManager;
use App\Scrap;
use App\Services\Interfaces\FlashMessageServiceInterface;
use App\Services\PageInfoService;
use App\Services\ViewService;
use Core\Database;
use Core\View;

/**
 * Home controller
 *
 */
class ErrorsController extends Controller
{
    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view
        // Scrap $scrapObj,
        // Database $dbx,
        // FlashMessages $flashObj,
        // Redirector $redirectObj,
        // ReturnPageManager $returnPageManagerObj,
        // ViewService $viewService,
        // PageInfoService $pageInfoService
    ) {
        parent::__construct(
            $route_params,
            $flash,
            $view
            // $scrapObj,
            // $dbx,
            // $flashObj,
            // $redirectObj,
            // $returnPageManagerObj,
            // $viewService,
            // $pageInfoService
        );
    }

    public function serverErrorAction(): void
    {
        Debug::p(111);
        //Debug::p(111);
        // Skip view rendering since the view file doesn't exist
        http_response_code(500);
        echo '<!DOCTYPE html>';
        echo '<html><head><title>500 Server Error</title></head>';
        echo '<body>';
        echo '<h1>500 Internal Server Error</h1>';
        echo '<p>Something went wrong on our end. Please try again later.</p>';
        echo '</body></html>';
        exit; // Stop execution to prevent redirect loop
    }



    // ErrorController.php
    public function showError($code, $message, $data = [])
    {
        //Debug::p($code);
        http_response_code($code);
        //Debug::p($code);
        $this->view("errors/{$code}", [
            'layout' => "error",
            'message' => $message,
            'data' => $data,

            // 'pageInfo' => $pageInfo
        ]);
        // // Use your normal view system
        // return $this->view("errors/{$code}", [
        //     'message' => $message,
        //     'data' => $data,
        //     'showDetails' => $this->config->get('debug.enabled', false)
        // ]);
    }



    // ## 500
    // public function xxxserverErrorAction(): void
    // {
    //     $pageInfo['head'] = "ERROR";
    //     $pageInfo['title'] = '500 Internal Server Error';
    //     $pageInfo['h1'] = '500 Internal Server Error';
    //     $pageInfo['paragraph'] = 'Something went wrong on our end. Please try again later.';
    //     ## An internal server error occurred.

    //     $this->view('Errors/server-error', ['layout' => "error",
    //                                     'boo' => "BOO",
    //                                     'pageInfo' => $pageInfo
    //                                 ]);
    // }
}
