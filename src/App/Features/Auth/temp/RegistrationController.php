<?php

declare(strict_types=1);

namespace App\Features\Auth;

use App\Helpers\DebugRt as Debug;
use App\Enums\FlashMessageType;
use App\Enums\Url;
use App\Features\Auth\Form\RegistrationFormType;
use App\Helpers\DebugRt;
use App\Services\RegistrationService;
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
use App\Services\UserValidationService;
use Core\Context\CurrentContext;

// use Core\Security\Captcha\CaptchaServiceInterface;

/**
 * Registration controller
 */
class RegistrationController extends Controller
{
    private FormFactoryInterface $formFactory;
    private FormHandlerInterface $formHandler;
    private RegistrationFormType $registrationFormType;
    private RegistrationService $registrationService;
    private UserValidationService $userValidationService;
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
        RegistrationFormType $registrationFormType,
        RegistrationService $registrationService,
        UserValidationService $userValidationService,
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
        $this->registrationFormType = $registrationFormType;
        $this->registrationService = $registrationService;
        $this->userValidationService = $userValidationService;
        // $this->captchaService = $captchaService;
    }

    /**
     * Show the registration form
     */
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        // Create registration form
        $form = $this->formFactory->create(
            $this->registrationFormType,
            [],
            [
                'force_captcha' => $this->isForcedCaptcha(),
                'ip_address' => $this->getIpAddress(),
                // 'captcha_required' => $captchaRequired,
                // 'layout_type' => 'none',
                // 'error_display' => 'summary',
                // 'renderer' => 'bootstrap',
                // 'html5_validation' => true, // Enable HTML5 validation (override novalidate)
            ]
        );
        $formTheme = $form->getCssFormThemeFile();


        // Process form submission
        $formHandled = $this->formHandler->handle($form, $request);
        if ($formHandled && $form->isValid()) {
            $data = $form->getData();

            // Manual password confirmation validation
            $passwordError = $this->userValidationService->validatePasswordConfirmation(
                $data['password'],
                $data['confirm_password']
            );

            if ($passwordError) {
                $form->addError('confirm_password', $passwordError);
            } else {
                // If no errors, proceed with registration
                // Debug::p($form->isValid());
                //if ($form->isValid()) {
                // Register the user using the registration service
                $result = $this->registrationService->registerUser($data, $request->getUri());

                // If registration was successful, redirect to pending verification page
                if ($result['success']) {
                    $this->flash->add(
                        'Your account has been created. Please check your email to verify your account.',
                        FlashMessageType::Success
                    );
                    //DebugRt::j('1', '', 111);
                    //return $this->redirect('/verify-email/pending');
                    return $this->redirect(Url::EMAIL_VERIFICATION->url());
                } else {
                    // Add validation errors from the registration service to the form
                    foreach ($result['errors'] as $field => $error) {
                        $form->addError($field, $error);
                    }
                }
            }
        }

        // Create FormView for rendering
        //$formView = new FormView($form);


        // Prepare view data
        $viewData = [
            'title' => 'Create Account',
            // 'form' => $formView,
            'form' => $form,
            'formTheme' => $formTheme // if $formTheme isset/used
        ];

        // // Add CAPTCHA scripts if needed
        // if ($captchaRequired) {
        //     $viewData['captcha_scripts'] = $this->captchaService->getScripts();

        //     // ALSO PASS THE SERVICE TO THE VIEW
        //     $viewData['captchaService'] = $this->captchaService;
        // }


        // Create response with appropriate status code
        // $response = $this->view(AuthConst::VIEW_AUTH_REGISTRATION, $viewData);
        $response = $this->view(Url::AUTH_REGISTRATION->view(), $viewData);

        // // Render the registration form
        // return $this->view(AuthConst::VIEW_AUTH_REGISTRATION, [
        //     'title' => 'Create Account',
        //     'form' => $formView
        // ]);
        // Create response with appropriate status code
        // $response = $this->view(AuthConst::VIEW_AUTH_REGISTRATION, [
        //     'title' => 'Create Account',
        //     'form' => $formView
        // ]);

        // Set 422 Unprocessable Entity status for form errors
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;
    }

    /**
     * Show registration success page
     */
    public function successAction(): ResponseInterface
    {
        // return $this->view(AuthConst::VIEW_AUTH_REGISTRATION_SUCCESS, [
        return $this->view(Url::AUTH_REGISTRATION_SUCCESS->view(), [
            'title' => 'Registration Successful'
        ]);
    }
}
