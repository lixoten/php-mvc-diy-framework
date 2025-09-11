<?php

declare(strict_types=1);

namespace Tests\App\Features\Auth;

use App\Enums\FlashMessageType;
use App\Features\Auth\Form\LoginFormType;
use App\Features\Auth\LoginController;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Auth\AuthenticationServiceInterface;
use Core\Form\Field\Type\CaptchaFieldType;
use Core\Form\FormFactoryInterface;
use Core\Form\FormHandlerInterface;
use Core\Form\FormInterface;
use Core\Http\HttpFactory;
use Core\Security\Captcha\CaptchaServiceInterface;
use Core\View;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class LoginControllerCaptchaTest extends TestCase
{
    private $loginController;
    private $formFactory;
    private $formHandler;
    private $captchaService;
    private $request;
    private $form;

    protected function setUp(): void
    {
        // Create mocks for all dependencies
        $routeParams = [];
        /** @var FlashMessageServiceInterface */
        $flash = $this->createMock(FlashMessageServiceInterface::class);
        /** @var \Core\View */
        $view = $this->createMock(View::class);

        /** @var HttpFactory */
        $httpFactory = $this->createMock(HttpFactory::class);

        /** @var ContainerInterface */
        $container = $this->createMock(ContainerInterface::class);
        /** @var FormFactoryInterface */
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        /** @var FormHandlerInterface */
        $this->formHandler = $this->createMock(FormHandlerInterface::class);
        /** @var AuthenticationServiceInterface */
        $authService = $this->createMock(AuthenticationServiceInterface::class);
        /** @var LoginFormType */
        $loginFormType = $this->createMock(LoginFormType::class);
        /** @var CaptchaServiceInterface */
        $this->captchaService = $this->createMock(CaptchaServiceInterface::class);

        // Create the controller with the correct parameters
        $this->loginController = new LoginController(
            $routeParams,
            $flash,
            $view,
            $httpFactory,
            $container,
            $this->formFactory,
            $this->formHandler,
            $authService,
            $loginFormType,
            $this->captchaService
        );

        // Setup request
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->form = $this->createMock(FormInterface::class);
    }

    public function testLoginFormWithCaptchaWhenRequired(): void
    {
        // Setup URI
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getQuery')->willReturn('');
        $this->request->method('getUri')->willReturn($uri);

        // Setup server params with IP
        $this->request->method('getServerParams')->willReturn(['REMOTE_ADDR' => '127.0.0.1']);
        $this->request->method('getQueryParams')->willReturn([]);

        // CAPTCHA is enabled and required for this IP
        $this->captchaService->method('isEnabled')->willReturn(true);
        $this->captchaService->method('isRequired')
            ->with('login', '127.0.0.1')
            ->willReturn(true);

        // Form creation expectations
        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function ($options) {
                    return $options['captcha_required'] === true;
                })
            )
            ->willReturn($this->form);

        // Call the controller action
        $this->loginController->indexAction($this->request);
    }

    public function testLoginFormWithoutCaptchaWhenNotRequired(): void
    {
        // Setup URI
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getQuery')->willReturn('');
        $this->request->method('getUri')->willReturn($uri);

        // Setup server params with IP
        $this->request->method('getServerParams')->willReturn(['REMOTE_ADDR' => '127.0.0.1']);
        $this->request->method('getQueryParams')->willReturn([]);

        // CAPTCHA is enabled but not required for this IP
        $this->captchaService->method('isEnabled')->willReturn(true);
        $this->captchaService->method('isRequired')
            ->with('login', '127.0.0.1')
            ->willReturn(false);

        // Form creation expectations
        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function ($options) {
                    return $options['captcha_required'] === false;
                })
            )
            ->willReturn($this->form);

        // Call the controller action
        $this->loginController->indexAction($this->request);
    }

    public function testLoginFormWithoutCaptchaWhenDisabled(): void
    {
        // Setup URI
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getQuery')->willReturn('');
        $this->request->method('getUri')->willReturn($uri);

        // Setup server params with IP
        $this->request->method('getServerParams')->willReturn(['REMOTE_ADDR' => '127.0.0.1']);

        // CAPTCHA is disabled globally
        $this->captchaService->method('isEnabled')->willReturn(false);

        // isRequired should never be called
        $this->captchaService->expects($this->never())
            ->method('isRequired');

        // Form creation expectations
        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function ($options) {
                    return $options['captcha_required'] === false;
                })
            )
            ->willReturn($this->form);

        // Call the controller action
        $this->loginController->indexAction($this->request);
    }

    public function testForceCaptchaWithQueryParam(): void
    {
        // Setup URI with show_captcha param
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getQuery')->willReturn('show_captcha=1');
        $this->request->method('getUri')->willReturn($uri);

        // Parse query params
        $this->request->method('getQueryParams')->willReturn(['show_captcha' => '1']);

        // Setup server params with IP
        $this->request->method('getServerParams')->willReturn(['REMOTE_ADDR' => '127.0.0.1']);

        // CAPTCHA is enabled but would not normally be required
        $this->captchaService->method('isEnabled')->willReturn(true);
        $this->captchaService->method('isRequired')
            ->with('login', '127.0.0.1')
            ->willReturn(false);

        // Form creation expectations - with CAPTCHA required due to the query param
        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function ($options) {
                    return $options['captcha_required'] === true;
                })
            )
            ->willReturn($this->form);

        // Call the controller action
        $this->loginController->indexAction($this->request);
    }
}
