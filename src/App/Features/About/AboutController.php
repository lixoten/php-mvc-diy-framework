<?php

declare(strict_types=1);

namespace App\Features\About;

use App\Enums\Url;
use Core\Controller;
use App\Helpers\DebugRt as Debug;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Context\CurrentContext;
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
        ContainerInterface $container,
        CurrentContext $scrap,
    ) {
        parent::__construct(
            $route_params,
            $flash,
            $view,
            $httpFactory,
            $container,
            $scrap
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

        return $this->view(Url::CORE_ABOUT->view(), [
        // return $this->view('about/index', [
            'title' => 'About Index Action',
            'actionLinks' => $this->getActionLinks('about', ['index']),
        ]);
    }
}
