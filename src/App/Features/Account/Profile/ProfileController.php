<?php

declare(strict_types=1);

namespace App\Features\Account\Profile;

use App\Enums\Url;
use Core\Controller;
use Core\View;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Context\CurrentContext;
use Core\Http\HttpFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Profile controller
 */
class ProfileController extends Controller
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
     * Show the Profile index page
     */
    public function indexAction(): ResponseInterface
    {
        // return $this->view('account/profile/index', [
        return $this->view(Url::ACCOUNT_PROFILE->view(), [
            'title' => 'User Profile Placeholder'
        ]);
    }
}
