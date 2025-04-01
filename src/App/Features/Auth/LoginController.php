<?php

declare(strict_types=1);

namespace App\Features\Auth;

use App\Enums\FlashMessageType;
use App\Features\Auth\Form\LoginFormType;
use Core\Auth\AuthenticationServiceInterface;
use Core\Auth\Exception\AuthenticationException;
use Core\Controller;
use Core\Form\FormFactoryInterface;
use Core\Form\FormHandlerInterface;
use Core\Form\View\FormView;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Services\Interfaces\FlashMessageServiceInterface;

/**
 * Login controller
 */
class LoginController extends Controller
{
    private FormFactoryInterface $formFactory;
    private FormHandlerInterface $formHandler;
    private AuthenticationServiceInterface $authService;
    private LoginFormType $loginFormType;

    /**
     * Constructor with dependencies
     */
    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        FormFactoryInterface $formFactory,
        FormHandlerInterface $formHandler,
        AuthenticationServiceInterface $authService,
        LoginFormType $loginFormType
    ) {
        parent::__construct(
            $route_params,
            $flash,
            $view,
            $httpFactory,
            $container
        );
        $this->formFactory = $formFactory;
        $this->formHandler = $formHandler;
        $this->authService = $authService;
        $this->loginFormType = $loginFormType;
    }

    /**
     * Show the login form
     */
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        // Create login form
        $form = $this->formFactory->create(
            $this->loginFormType,
            [],
            [
                'layout_type' => 'none',
                'error_display' => 'summary',
                'renderer' => 'bootstrap'
            ]
        );

        // Process form submission
        $formHandled = $this->formHandler->handle($form, $request);
        if ($formHandled && $form->isValid()) {
            $data = $form->getData();

            try {
                // Attempt login
                $remember = isset($data['remember']) ? (bool)$data['remember'] : false;
                $this->authService->login($data['username'], $data['password'], $remember);

                // Success! Redirect to intended URL or dashboard
                $return = $request->getQueryParams()['return'] ?? '/admin/dashboard';
                $this->flash->add('You have been logged in successfully', FlashMessageType::Success);

                return $this->redirect($return);
            } catch (AuthenticationException $e) {
                // Handle login errors
                $form->addError('_form', $e->getMessage());
            }
        }

        // Create FormView for rendering
        $formView = new FormView($form, [
            'error_display' => 'summary'
        ]);

        // Render the login form
        return $this->view(AuthConst::VIEW_AUTH_LOGIN, [
            'title' => 'Log In',
            'form' => $formView
        ]);
    }

    /**
     * Logout action
     */
    public function logoutAction(): ResponseInterface
    {
        $this->authService->logout();
        $this->flash->add('You have been logged out successfully', FlashMessageType::Info);
        return $this->redirect('/login');
    }
}
