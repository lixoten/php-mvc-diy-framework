<?php

declare(strict_types=1);

namespace App\Features\Account\Mynotes;

use Core\Controller;
use Core\View;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Http\HttpFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * My Notes controller
 */
class MynotesController extends Controller
{
    /**
     * Constructor
     */
    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container
    ) {
        parent::__construct(
            $route_params,
            $flash,
            $view,
            $httpFactory,
            $container
        );
    }

    /**
     * Show the My Notes index page
     */
    public function indexAction(): ResponseInterface
    {
        //return $this->view('Account/mynotes', [
        return $this->view('account/mynotes/index', [
            'title' => 'My Notes'
        ]);
    }
}
