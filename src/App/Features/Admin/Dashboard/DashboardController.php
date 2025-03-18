<?php

declare(strict_types=1);

namespace App\Features\Admin\Dashboard;

use Core\Controller;
use App\Helpers\DebugRt as Debug;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Container\ContainerInterface;

/**
 * Home controller
 *
 */
class DashboardController extends Controller
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
        // In a controller action you want to measure:
        $startTime = microtime(true);
        // Expensive operation
        sleep(5);
        $endTime = microtime(true);
        error_log('Operation took: ' . (($endTime - $startTime) * 1000) . 'ms');



        return $this->view(DashboardConst::VIEW_DASHBOARD_INDEX, [
            'title' => 'Dashboard Index Action',
            'actionLinks' => $this->getActionLinks('admin/dashboard', ['index'])
        ]);
    }
}
