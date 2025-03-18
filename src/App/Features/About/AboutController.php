<?php

declare(strict_types=1);

namespace App\Features\About;

use Core\Controller;
use App\Helpers\DebugRt as Debug;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * About controller
 *
 */
class AboutController extends Controller
{
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
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function indexAction(): ResponseInterface
    {
        $this->flash->add("FOOFEE");

        return $this->view(AboutConst::VIEW_ABOUT_INDEX, [
            'title' => 'About Index Action',
            'actionLinks' => $this->getActionLinks('about', ['index']),
        ]);
    }
}
