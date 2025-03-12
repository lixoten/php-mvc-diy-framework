<?php

declare(strict_types=1);

namespace App\Features\Admin\Dashboard;

use Core\Controller;
use App\Helpers\DebugRt as Debug;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\View;

/**
 * Home controller
 *
 */
class DashboardController extends Controller
{
    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view
    ) {
        parent::__construct(
            $route_params,
            $flash,
            $view
        );
    }

    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction(): void
    {
        echo "hello";
        //$this->view(HomeConst::VIEW_HOME_INDEX, [
        $this->view('Admin/Dashboard/index', [
            'title' => 'Welcome Dashboard'
        ]);
    }
}
