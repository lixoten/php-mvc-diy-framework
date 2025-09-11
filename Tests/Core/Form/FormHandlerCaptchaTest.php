<?php

declare(strict_types=1);

namespace Tests\Core\Form;

use Core\Form\CSRF\CSRFToken;
use Core\Form\Event\FormEvents;
use Core\Form\FormHandler;
use Core\Form\FormInterface;
use Core\Form\Validation\ValidatorRegistry;
use Core\Security\Captcha\CaptchaServiceInterface;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;

class FormHandlerCaptchaTest extends TestCase
{
    private $formHandler;
    private $csrf;
    private $validatorRegistry;
    private $captchaService;
    private $eventDispatcher;
    private $form;
    private $request;

    protected function setUp(): void
    {
        /** @var CSRFToken */
        $this->csrf = $this->createMock(CSRFToken::class);
        /** @var ValidatorRegistry */
        $this->validatorRegistry = $this->createMock(ValidatorRegistry::class);
        /** @var CaptchaServiceInterface */
        $this->captchaService = $this->createMock(CaptchaServiceInterface::class);
        /** @var EventDispatcherInterface */
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->formHandler = new FormHandler(
            $this->csrf,
            $this->validatorRegistry,
            $this->captchaService,
            $this->eventDispatcher
        );

        $this->form = $this->createMock(FormInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
    }

    public function testHandleIgnoresCaptchaWhenDisabled(): void
    {
        // Setup
        $this->request->method('getMethod')->willReturn('POST');
        $this->request->method('getParsedBody')->willReturn([
            'csrf_token' => 'test-token'
        ]);

        // Be more specific about which field check returns true
        $this->form->method('hasField')
            ->willReturnCallback(function ($field) {
                if ($field === 'captcha') {
                    return true;
                }
                return false;
            });

        $this->form->method('validateCSRFToken')
            ->with('test-token')
            ->willReturn(true);

        $this->form->method('validate')
            ->with(['request' => $this->request])
            ->willReturn(true);

        // CAPTCHA service is disabled
        $this->captchaService->method('isEnabled')->willReturn(false);

        // Verify is never called even though form has CAPTCHA field
        $this->captchaService->expects($this->never())
            ->method('verify');

        // Allow any event dispatch
        $this->eventDispatcher->method('dispatch')->willReturn(null);

        // Submit form (should succeed despite no CAPTCHA response)
        $result = $this->formHandler->handle($this->form, $this->request);
        $this->assertTrue($result);
    }

    public function testHandleRequiresCaptchaWhenEnabled(): void
    {
        // Setup
        $this->request->method('getMethod')->willReturn('POST');
        $this->request->method('getParsedBody')->willReturn([
            // No g-recaptcha-response
        ]);

        $this->form->method('hasField')->with('captcha')->willReturn(true);

        // CAPTCHA service is enabled
        $this->captchaService->method('isEnabled')->willReturn(true);

        // Should add error to form for missing CAPTCHA
        $this->form->expects($this->once())
            ->method('addError')
            ->with('captcha', $this->anything());

        // Allow any event dispatch
        $this->eventDispatcher->method('dispatch')->willReturn(null);

        // Form submission should fail
        $result = $this->formHandler->handle($this->form, $this->request);
        $this->assertFalse($result);
    }

    public function testHandleValidatesCaptchaWhenProvided(): void
    {
        // Setup
        $captchaResponse = 'test-response';
        $this->request->method('getMethod')->willReturn('POST');
        $this->request->method('getParsedBody')->willReturn([
            'g-recaptcha-response' => $captchaResponse,
            'csrf_token' => 'test-token'
        ]);

        // Be more specific about which field check returns true
        $this->form->method('hasField')
            ->willReturnCallback(function ($field) {
                if ($field === 'captcha') {
                    return true;
                }
                return false;
            });

        $this->form->method('validateCSRFToken')
            ->with('test-token')
            ->willReturn(true);

        $this->form->method('validate')
            ->with(['request' => $this->request])
            ->willReturn(true);

        // CAPTCHA service is enabled
        $this->captchaService->method('isEnabled')->willReturn(true);

        // Verify should be called with the response
        $this->captchaService->expects($this->once())
            ->method('verify')
            ->with($captchaResponse)
            ->willReturn(true);

        // Allow any event dispatch
        $this->eventDispatcher->method('dispatch')->willReturn(null);

        // Form submission should succeed
        $result = $this->formHandler->handle($this->form, $this->request);
        $this->assertTrue($result);
    }

    public function testHandleRejectsInvalidCaptcha(): void
    {
        // Setup
        $captchaResponse = 'invalid-response';
        $this->request->method('getMethod')->willReturn('POST');
        $this->request->method('getParsedBody')->willReturn([
            'g-recaptcha-response' => $captchaResponse
        ]);

        $this->form->method('hasField')->with('captcha')->willReturn(true);

        // CAPTCHA service is enabled
        $this->captchaService->method('isEnabled')->willReturn(true);

        // Verify should return false for invalid response
        $this->captchaService->method('verify')
            ->with($captchaResponse)
            ->willReturn(false);

        // Should add error to form for invalid CAPTCHA
        $this->form->expects($this->once())
            ->method('addError')
            ->with('captcha', $this->anything());

        // Allow any event dispatch
        $this->eventDispatcher->method('dispatch')->willReturn(null);

        // Form submission should fail
        $result = $this->formHandler->handle($this->form, $this->request);
        $this->assertFalse($result);
    }
}
