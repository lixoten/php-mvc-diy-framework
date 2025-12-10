<?php

declare(strict_types=1);

namespace App\Features\Contact;

use App\Helpers\DebugRt;
use App\Enums\FlashMessageType;
use App\Enums\Url;
use App\Features\Contact\Form\ContactDirectFormType;
use App\Features\Contact\Form\ContactFormType;
use Core\Controller;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Context\CurrentContext;
use Core\Form\FormFactoryInterface;
use Core\Form\FormHandlerInterface;
use Core\Http\HttpFactory;
use Core\Logger;
use Core\View;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * About controller
 *
 */
class ContactController extends Controller
{
    private FormFactoryInterface $formFactory;
    private FormHandlerInterface $formHandler;
    protected Logger $logger;
    // private AuthenticationServiceInterface $authService;
    private ContactFormType $contactFormType;
    private ContactDirectFormType $contactDirectFormType;
    // private CaptchaServiceInterface $captchaService;

    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        FormFactoryInterface $formFactory,
        FormHandlerInterface $formHandler,
        Logger $logger,
        // AuthenticationServiceInterface $authService,
        ContactFormType $contactFormType,
        ContactDirectFormType $contactDirectFormType,
        // CaptchaServiceInterface $captchaService
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
        $this->formFactory = $formFactory;
        $this->formHandler = $formHandler;
        $this->logger = $logger;
        // $this->authService = $authService;
        $this->contactFormType = $contactFormType;
        $this->contactDirectFormType = $contactDirectFormType;
        // $this->captchaService = $captchaService;
    }


    /**
     * Show the contact form
     */
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        $form = $this->formFactory->create(
            $this->contactFormType,
            [],
            [
                // 'force_captcha' => $this->isForcedCaptcha(), // fix-force-captcha '';
                'ip_address' => $this->getIpAddress(),
                // Notes-: There below could be set here, but don't. Use the appropriate FormType
                // 'error_display' => 'inline',
                // 'submit_text' => 'sssssaaaaaaaaaaaaaSubmit',
                // 'css_form_theme_class' => "form-theme-christmas",
                // 'title_heading' => 'Send us a message',
                // 'html5_validation' => false,
                // 'css_form_theme_file' => 'christmas',
            ]
        );
        $formTheme = $form->getCssFormThemeFile();

        // Process form submission
        $formHandled = $this->formHandler->handle($form, $request);
        if ($formHandled && $form->isValid()) {
            $data = $form->getData();

            // Example: log the submission
            $this->logger->info('Contact form submitted (FormView rendering)', $data);

            // Add flash message
            $this->flash->add("Your message has been sent successfully", FlashMessageType::Success);

            // Redirect to thank you page or back to contact
            // return $this->redirect('/contact');
            return $this->redirect(Url::CORE_CONTACT->url());
        }

        // Prepare view data - pass the form directly instead of FormView
        $viewData = [
            'title' => 'Contact Us (FormView Form Rendering)',
            'form' => $form,
            'formTheme' => $formTheme // if $formTheme isset/used
        ];

        // Create response with appropriate status code
        $response = $this->view(Url::CORE_CONTACT->view(), $viewData);

        // Set 422 Unprocessable Entity status for form failures
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;
        // Only redirect on success
        //return $this->redirect('/contact');
    }




    /**
     * Show the contact form
     */
    public function directAction(ServerRequestInterface $request): ResponseInterface
    {
        $form = $this->formFactory->create(
            $this->contactDirectFormType,
            [],
            [
                // 'force_captcha' => $this->isForcedCaptcha(), // fix-force-captcha '';
                'ip_address' => $this->getIpAddress(),
                // Notes-: There below could be set here, but don't. Use the appropriate FormType
                // 'error_display' => 'inline',
                // 'submit_text' => 'sssssaaaaaaaaaaaaaSubmit',
                // 'css_form_theme_class' => "form-theme-christmas",
                // 'title_heading' => 'Send us a message',
                // 'html5_validation' => false,
                // 'css_form_theme_file' => 'christmas',
            ]
        );
        $formTheme = $form->getCssFormThemeFile();

        // Process form submission
        $formHandled = $this->formHandler->handle($form, $request);
        if ($formHandled && $form->isValid()) {
            $data = $form->getData();

            // Example: log the submission
            $this->logger->info('Contact form submitted (direct rendering)', $data);

            // Add flash message
            $this->flash->add("Your message has been sent successfully", FlashMessageType::Success);

            // Redirect to thank you page or back to contact
            // return $this->redirect('/contact/direct');
            return $this->redirect(Url::CORE_CONTACT_DIRECT->url());
        }

        // Prepare view data - pass the form directly instead of FormView
        $viewData = [
            'title' => 'Contact Us (Direct Form Rendering)',
            'form' => $form,
            'formTheme' => $formTheme // if $formTheme isset/used
        ];

        // Create response with appropriate status code
        $response = $this->view(Url::CORE_CONTACT_DIRECT->view(), $viewData);

        // Set 422 Unprocessable Entity status for form failures
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;
        // Only redirect on success
        //return $this->redirect('/contact/direct');
    }
}
