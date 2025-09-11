<?php

declare(strict_types=1);

namespace App\Features\Admin\Users;

use App\Enums\Url;
use Core\Controller;
use App\Helpers\DebugRt;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Context\CurrentContext;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Container\ContainerInterface;

/**
 * Users controller
 *
 */
class UsersController extends Controller
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

        // In a controller action you want to measure:
        $startTime = microtime(true);
        // Expensive operation
        sleep(5);
        $endTime = microtime(true);
        error_log('Operation took: ' . (($endTime - $startTime) * 1000) . 'ms');



        return $this->view(Url::ADMIN_DASHBOARD->view(), [
            'title' => 'Admin Users',
            'actionLinks' => $this->getActionLinks(
                 Url::ADMIN_DASHBOARD,
                 Url::ADMIN_USERS
            )
        ]);
    }
}
