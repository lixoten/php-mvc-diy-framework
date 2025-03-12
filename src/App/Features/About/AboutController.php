<?php

declare(strict_types=1);

namespace App\Features\About;

use Core\Controller;
use App\Helpers\DebugRt as Debug;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\View;

/**
 * Home controller
 *
 */
class AboutController extends Controller
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

        $this->flash->add("FOOFEE");
        //$this->view(HomeConst::VIEW_HOME_INDEX, [
        $this->view('about/index', [
            'title' => 'Welcome About'
        ]);
    }
}
