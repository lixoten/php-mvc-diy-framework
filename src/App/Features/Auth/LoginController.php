<?php

declare(strict_types=1);

namespace App\Features\Auth;

use App\Helpers\DebugRt;
use App\Enums\FlashMessageType;
use App\Enums\Url;
use App\Features\Auth\Form\LoginFormType;
use App\Helpers\MyLinkHelper;
use Core\Auth\AuthenticationServiceInterface;
use Core\Controller;
use Core\Form\FormFactoryInterface;
use Core\Form\FormHandlerInterface;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Services\Interfaces\FlashMessageServiceInterface;
//use App\Services\LinkDataService;
use Core\Auth\Exception\AuthenticationException;
use Core\Context\CurrentContext;
use Core\Security\Captcha\CaptchaServiceInterface;

/**
 * Login controller
 */
class LoginController extends Controller
{
    private FormFactoryInterface $formFactory;
    private FormHandlerInterface $formHandler;
    private AuthenticationServiceInterface $authService;
    private LoginFormType $loginFormType;
    // private CaptchaServiceInterface $captchaService;

    /**
     * Constructor with dependencies
     */
    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        CurrentContext $scrap,
        FormFactoryInterface $formFactory,
        FormHandlerInterface $formHandler,
        AuthenticationServiceInterface $authService,
        //private LinkDataService $linkDataService,
        LoginFormType $loginFormType,
        // CaptchaServiceInterface $captchaService
    ) {
        parent::__construct(
            $route_params,
            $flash,
            $view,
            $httpFactory,
            $container,
            $scrap
        );
        $this->formFactory = $formFactory;
        $this->formHandler = $formHandler;
        $this->authService = $authService;
        $this->loginFormType = $loginFormType;
        // $this->captchaService = $captchaService;
    }

    /**
     * Show the login form
     */
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {


        $configService = $this->container->get('config');

        $view = 'posts_edit';
        $configOptions = $configService->get('view_options/' . $view) ?? ['a']; // loads "list_fields/posts.php"

        $options            = $configOptions['options'] ?? [];
        $renderOptions      = $configOptions['render_options'] ?? [];
        $formFields         = $configOptions['form_fields'] ?? [];

        //$formFields = ['id', 'title', 'ffffff', 'fffee', 'created_at'];


        $options = [
            'testfoo' => 'notrender LoginController value',
            'ip_address' => $this->getIpAddress(),
            'force_captcha' => $this->isForcedCaptcha(),
            'ip_address' => $this->getIpAddress(),
        ];
        $renderOptions = [
            'testfoo' => 'LoginController value',
            // 'form_fields' => ['title', 'username', 'status', 'created_at'],
            // 'layout_type' => CONST_L::SECTIONS,    // FIELDSETS / SECTIONS / SEQUENTIAL
            // 'security_level' => CONST_SL::LOW,      // HIGH / MEDIUM / LOW
            // 'error_display' => CONST_ED::SUMMARY,   // SUMMARY / SUMMARY / INLINE
            // 'submit_text' => "dddddddddd",
            //'captcha_required' => $captchaRequired,

        ];

  // $captchaRequired = $this->captchaService->isRequired('login', $ipAddress);
        $options = [
            // 'ip_address' => $this->getIpAddress(),
            // 'boo' => 'boo',
            'testfoo' => 'notrender LoginController value',
            'ip_address' => $this->getIpAddress(),
            'force_captcha' => $this->isForcedCaptcha(),
            'ip_address' => $this->getIpAddress(),

            'render_options' => [
                'error_display' => 'summary', // 'summary, inline'
                'layout_type'   =>  'sequential', // fieldsets / sections / sequential
            //     'submit_text'   => "add fook",
                'form_fields'   => [
                    'username', 'password', 'remember'
                ],
                'layout'        => [
                    [
                        'title' => 'Your Mesdddsage', // Displays in 'fieldset, sequential'
                        'fields' => ['username', 'password'],
                        'divider' => true
                    ],
                    [
                        'title' => 'Your Mesdddsage', // Displays in 'fieldset, sequential'
                        'fields' => ['remember'],
                        'divider' => true,
                    ],
                ],
            ]
        ];


        // Create login form
        $form = $this->formFactory->create(
            formType: $this->loginFormType,
            data: ['remember' => false], // Default value for "remember me"
            options: $options ?? [],
            // options:  array_merge(
            //     $options,
            //     [
            //         'render_options' => $renderOptions + ['form_fields' => $formFields],
            //     ]
            // )
            // options: [
            //     'force_captcha' => $this->isForcedCaptcha(),
            //     'ip_address' => $this->getIpAddress(),
            //     'render_options' => [
            //         // 'layout_type' => CONST_L::SECTIONS,    // FIELDSETS / SECTIONS / SEQUENTIAL
            //         // 'security_level' => CONST_SL::LOW,      // HIGH / MEDIUM / LOW
            //         // 'error_display' => CONST_ED::SUMMARY,   // SUMMARY / SUMMARY / INLINE
            //                     'submit_text' => "dddddddddd",
            //     ]
            //     // 'captcha_required' => $captchaRequired,
            //     // 'layout_type' => 'none',
            //     // 'error_display' => 'inline', // summary // inline
            //     // 'renderer' => 'bootstrap',
            //     // 'html5_validation' => true, // Enable HTML5 validation (override novalidate)
            // ]
        );
        $formTheme = $form->getCssFormThemeFile();

        // Process form submission
        $formHandled = $this->formHandler->handle($form, $request);
        if ($formHandled && $form->isValid()) {
            $data = $form->getData();

            try {
                $remember = isset($data['remember']) ? (bool)$data['remember'] : false;
                $this->authService->login($data['username'], $data['password'], $remember);

                // Determine appropriate landing page based on user roles
                $user = $this->authService->getCurrentUser();
                $roles = $user->getRoles();
                //$defaultPage = in_array('admin', $roles) ? '/admin/dashboard' : '/dashboard';
                // Navigation: XXX
                if (in_array('admin', $roles)) {
                    $defaultPage = '/admin/dashboard';
                } elseif (in_array('store_owner', $roles)) {
                    $defaultPage = '/stores/dashboard'; // Redirect to store dashboard
                } else {
                    $defaultPage = '/account/dashboard'; // Regular user dashboard
                }

                // Redirect to intended URL or default page
                $return = $request->getQueryParams()['return'] ?? $defaultPage;
                return $this->redirect($return);
            } catch (AuthenticationException $e) {
                // Handle authentication errors
                //$form->addError('username', $e->getMessage());
                // if ($e->getCode() === AuthenticationException::ACCOUNT_INACTIVE) {
                //     // Handle inactive account specifically
                //     $form->addError('username', 'Account is not active.');

                //     // $resendUrl = Url::AUTH_VERIFICATION_RESEND->url();
                //     $resendLink = $this->linkBuilder->create(
                //         Url::AUTH_VERIFICATION_RESEND,
                //         'Click here to resend verification email'
                //     );

                //     $this->flash->add(
                //         // "Your account is not verified. <a href=\"{$resendUrl}\">Click here to resend verification email</a>",
                //         "Your account is not verified. {$resendLink}",
                //         FlashMessageType::Warning
                //     );
                // }
                if ($e->getCode() === AuthenticationException::ACCOUNT_INACTIVE) {
                    $form->addError('username', 'Account is not active.');

                    // Use LinkDataService to get structured data:
                    $linkData = Url::AUTH_VERIFICATION_RESEND->toLinkData('Click here to resend');

                    $message = "Your account is not verified. " . MyLinkHelper::render($linkData);
                    $this->flash->add(
                        $message,
                        FlashMessageType::Warning
                    );
                } else {
                    // Handle other authentication errors
                    $form->addError('username', $e->getMessage());
                }
            }
        }
        // Create FormView for rendering
        // $formView = new FormView($form, ['error_display' => 'summary']);
        //$formView = new FormView($form);

        // Prepare view data
        $viewData = [
            'title' => 'Log In',
            // 'form' => $formView,
            'form' => $form,
            'formTheme' => $formTheme // if $formTheme isset/used
        ];
        if ($_ENV['APP_ENV'] === 'development') {
            $viewData['scrapInfo'] = (array) $this->scrap->printIt();
            $viewData['scrap'] = $this->scrap;
        }

        // // Add CAPTCHA scripts if needed
        // if ($captchaRequired) {
        //     $viewData['captcha_scripts'] = $this->captchaService->getScripts();

        //     // ALSO PASS THE SERVICE TO THE VIEW
        //     $viewData['captchaService'] = $this->captchaService;
        // }

        // Create response with appropriate status code
        // $response = $this->view(AuthConst::VIEW_AUTH_LOGIN, $viewData);
        $response = $this->view(Url::AUTH_LOGIN->view(), $viewData);

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
        // return $this->redirect('/login');
        return $this->redirect(Url::LOGIN->url());
    }
}
