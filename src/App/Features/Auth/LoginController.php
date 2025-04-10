<?php

declare(strict_types=1);

namespace App\Features\Auth;

use App\Helpers\DebugRt as Debug;
use App\Enums\FlashMessageType;
use App\Features\Auth\Form\LoginFormType;
use Core\Auth\AuthenticationServiceInterface;
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
use Core\Auth\Exception\AuthenticationException;

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
            ['remember' => false], // Default value for "remember me"
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
                // Use SessionAuthenticationService for credential validation
                $remember = isset($data['remember']) ? (bool)$data['remember'] : false;
                $this->authService->login($data['username'], $data['password'], $remember);

                // Determine appropriate landing page based on user roles
                $user = $this->authService->getCurrentUser();
                $roles = $user->getRoles();
                $defaultPage = in_array('admin', $roles) ? '/admin/dashboard' : '/dashboard';

                // Redirect to intended URL or default page
                $return = $request->getQueryParams()['return'] ?? $defaultPage;
                return $this->redirect($return);
            } catch (AuthenticationException $e) {
                // Handle authentication errors
                $form->addError('username', $e->getMessage());
            }
        }

        // Create FormView for rendering
        $formView = new FormView($form, ['error_display' => 'summary']);

        // Create response with appropriate status code
        $response = $this->view(AuthConst::VIEW_AUTH_LOGIN, [
            'title' => 'Log In',
            'form' => $formView
        ]);

        // Set 422 Unprocessable Entity status for login failures
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;
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
