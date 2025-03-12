<?php

declare(strict_types=1);

namespace App\Features\Testy;

use Core\Controller;
use App\Helpers\DebugRt as Debug;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\View;

/**
 * Testy controller
 *
 */
class TestyController extends Controller
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
        //$this->view(TestyConst::VIEW_TESTY_INDEX, [
        $this->view('testy/index', [
            'title' => 'Welcome Testy'
        ]);
    }
}
