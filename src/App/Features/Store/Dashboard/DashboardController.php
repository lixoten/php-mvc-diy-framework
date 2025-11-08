<?php

declare(strict_types=1);

namespace App\Features\Store\Dashboard;

use App\Enums\Url;
use Core\Controller;
use Core\View;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Context\CurrentContext;
use Core\Http\HttpFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Dashboard controller
 */
class DashboardController extends Controller
{
    /**
     * Constructor
     */
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
     * Show the Dashboard index page
     */
    public function indexAction(): ResponseInterface
    {
        $test = $this->scrap->getCurrentUser();

        return $this->view(Url::STORE_DASHBOARD->view(), [
            'title' => 'Store Dashboard Placeholder'
        ]);
    }
}
