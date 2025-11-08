<?php

declare(strict_types=1);

namespace App\Features\Login;

use App\Enums\FlashMessageType;
use App\Enums\Url;
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
use Core\Auth\Exception\AuthenticationException;
use Core\Context\CurrentContext;
use Core\Form\FormInterface; // ADDED: For type hinting in processForm
use Core\Form\FormTypeInterface;
use Core\Security\Captcha\CaptchaServiceInterface;

/**
 * Login controller
 */
class LoginController extends Controller
{
    private FormFactoryInterface $formFactory;
    private FormHandlerInterface $formHandler;
    private AuthenticationServiceInterface $authService;
    protected FormTypeInterface $formType; // CHANGED: Renamed from loginFormType to formType for consistency
    private CaptchaServiceInterface $captchaService; // ADDED: Injected CaptchaService

    /**
     * Constructor with dependencies
     *
     * @param array<string, mixed> $route_params
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
        FormTypeInterface $formType,
        AuthenticationServiceInterface $authService,
        CaptchaServiceInterface $captchaService
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
        $this->formType = $formType;
        $this->authService = $authService;
        $this->captchaService = $captchaService;
    }

    /**
     * Show the login form and handle submission
     *
     * @param ServerRequestInterface $request The incoming server request.
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {

        $pageName       = $this->scrap->getPageName();
        $pageFeature    = $this->scrap->getPageFeature();
        $pageEntity     = $this->scrap->getPageEntity();

        $xpl = explode('_', $pageName);
        $entityNm = $xpl[0]; // hack dangerdanger - i do not like how i get table name

        $this->formType->setFocus(
            $pageName,
            $pageFeature,
            $pageEntity,
            $entityNm
        );


        $this->overrideFormTypeRenderOptions(); // Set form focus and other options


        // Process the form (no existing record data for login)
        $result = $this->processForm($request, null);
        $form   = $result['form'];

        // Handle successful form submission and authentication
        if ($result['handled'] && $result['valid']) {
            $data = $form->getData();

            try {
                $remember = isset($data['remember']) ? (bool)$data['remember'] : false;
                $this->authService->login($data['username'], $data['password'], $remember);

                // Determine appropriate landing page based on user roles
                $user = $this->authService->getCurrentUser();
                $roles = $user->getRoles();

                $defaultPage = '/account/dashboard'; // Default for regular user
                if (in_array('admin', $roles)) {
                    $defaultPage = '/admin/dashboard';
                } elseif (in_array('store_owner', $roles)) {
                    $defaultPage = '/store/dashboard';
                }

                // Redirect to intended URL or default page
                $return = $request->getQueryParams()['return'] ?? $defaultPage;
                return $this->redirect($return);

            } catch (AuthenticationException $e) {
                // Handle authentication errors
                if ($e->getCode() === AuthenticationException::ACCOUNT_INACTIVE) {
                    $form->addError('username', 'Account is not active.');
                    $linkData = Url::AUTH_VERIFICATION_RESEND->toLinkData('Click here to resend');
                    $message = "Your account is not verified. " . MyLinkHelper::render($linkData);
                    $this->flash22->add($message, FlashMessageType::Warning);
                } else {
                    $form->addError('username', $e->getMessage());
                }
            }
        }

        // Prepare view data for initial load or failed submission
        $viewData = [
            'title' => 'Log In',
            'form' => $form,
            'formTheme' => $form->getCssFormThemeFile(),
        ];

        // Add debug info in development
        if ($_ENV['APP_ENV'] === 'development') {
            $viewData['scrapInfo'] = (array) $this->scrap->printIt();
            $viewData['scrap'] = $this->scrap;
        }

        // Create response with appropriate status code
        $response = $this->view(Url::LOGIN->view(), $this->buildCommonViewData($viewData)); // CHANGED: Use buildCommonViewData

        // Set 422 Unprocessable Entity status for login failures
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;
    }

    /**
     * Logout action
     *
     * @return ResponseInterface
     */
    public function logoutAction(): ResponseInterface
    {
        $this->authService->logout();
        $this->flash22->add('You have been logged out successfully', FlashMessageType::Info); // CHANGED: Use $this->flash
        return $this->redirect(Url::LOGIN->url());
    }

    /**
     * Creates and processes the form for both GET and POST requests.
     * This method mimics the pattern in AbstractCrudController.
     *
     * @param ServerRequestInterface $request The current request.
     * @param array<string, mixed>|null $recordArray The record data as an array (null for login).
     * @return array{handled: bool, valid: bool, form: FormInterface}
     */ // fixme dekete
    protected function processForm(ServerRequestInterface $request, ?array $recordArray): array
    {
        $initialData = [];

        // For a GET request, use the pre-fetched array. No database call is needed here.
        if ($request->getMethod() === 'GET' && $recordArray) {
            $initialData = $recordArray;
        }

        // Create the form instance.
        $form = $this->formFactory->create(
            formType: $this->formType,
            data: $initialData
        );

        // The form handler processes the request.
        $formHandled = $this->formHandler->handle($form, $request);

        return [
            'handled' => $formHandled,
            'valid' => $form->isValid(),
            'form' => $form
        ];
    }

    /**
     * Overrides form type render options.
     * This method is called to set the form's context (page/entity name).
     *
     * @return void
     */
    protected function overrideFormTypeRenderOptions(): void
    {
        // $pageName = $this->scrap->getPageName();
        // $xpl = explode('_', $pageName);
        // $entityNm = $xpl[0]; // Extract entity name from page config key

        // $this->formType->setFocus(
        //     $pageName,
        //     $entityNm
        // );

        // Example of overriding options if needed, but ideally, most config is in LoginFormType
        // $options = [
        //     'force_captcha' => $this->isForcedCaptcha(),
        //     'ip_address' => $this->getIpAddress(),
        // ];
        // $this->formType->overrideConfig(options: $options);
    }

    // /**
    //  * Checks if CAPTCHA is forced based on configuration.
    //  *
    //  * @return bool
    //  */
    // private function isForcedCaptcha(): bool
    // {
    //     $configService = $this->container->get('config');
    //     return $configService->get('security.captcha.force_captcha', false);
    // }

    // /**
    //  * Retrieves the client's IP address.
    //  *
    //  * @return string
    //  */
    // private function getIpAddress(): string
    // {
    //     return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    // }
}