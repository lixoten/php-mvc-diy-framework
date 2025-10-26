<?php

declare(strict_types=1);

namespace Core\Errors;

use Core\Controller;
use App\Helpers\DebugRt as Debug;
use App\Helpers\FlashMessages;
use App\Helpers\Redirector;
use App\Helpers\ReturnPageManager;
use App\Scrap;
use App\Services\Interfaces\FlashMessageServiceInterface;
use App\Services\PageInfoService;
use App\Services\ViewService;
use Core\Context\CurrentContext;
use Core\Database;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Container\ContainerInterface;

/**
 * Home controller
 *
 */
class ErrorsController extends Controller
{
    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        CurrentContext $scrap,
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
            $view,
            $httpFactory,
            $container,
            $scrap
            // $scrapObj,
            // $dbx,
            // $flashObj,
            // $redirectObj,
            // $returnPageManagerObj,
            // $viewService,
            // $pageInfoService
        );
    }

    public function serverErrorAction(): ResponseInterface
    {
        // $response = $this->httpFactory->createResponse(500);
        // $body = '<!DOCTYPE html>';
        // $body .= '<html><head><title>500 Server Error</title></head>';
        // $body .= '<body>';
        // $body .= '<h1>500 Internal Server Error</h1>';
        // $body .= '<p>Something went wrong on our end. Please try again later.</p>';
        // $body .= '</body></html>';

        // $response->getBody()->write($body);
        // return $response;
        //Debug::p(111);
        return $this->view('errors/500', [
            'layout' => 'error',
            'message' => 'Something went wrong on our end. Please try again later.',
            'title' => '500 Internal Server Error'
        ], 500);
    }



    // ErrorController.php
    public function showError($code, $message, $data = []): ResponseInterface
    {
        $minimal = in_array((int)$code, [500, 503], true); // Use minimal layout for 500/503
        $layout = $minimal ? 'abend' : 'error';

        $viewData = [
            'layout' => $layout,
            'message' => $message,
            'data' => $data,
        ];

        return $this->view("errors/{$code}", $viewData, (int)$code);
    }
}
