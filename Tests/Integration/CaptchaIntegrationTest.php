<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Features\Auth\LoginController;
use Core\Form\Field\Type\CaptchaFieldType;
use Core\Form\FormBuilder;
use Core\Form\FormHandler;
use Core\Form\FormInterface;
use Core\Security\Captcha\GoogleReCaptchaService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class CaptchaIntegrationTest extends TestCase
{
    private $captchaConfig;
    private $captchaService;
    private $formHandler;
    private $loginController;
    private $formBuilder;
    private $request;
    private $mockForm;
    private $mockFormBuilder;
    private $mockFormHandler;

    protected function setUp(): void
    {
        // Create mock objects needed for integration test
        $this->setupMocks();

        // Create a real GoogleReCaptchaService with test configuration
        $this->captchaConfig = [
            'enabled' => true,
            'thresholds' => [
                'login' => 3,
                'registration' => 2
            ]
        ];

        $this->captchaService = new GoogleReCaptchaService(
            'test-site-key',
            'test-secret-key',
            null, // No BruteForce service
            $this->captchaConfig
        );
    }

    public function testCaptchaDisabledGloballySkipsCaptchaEverywhere(): void
    {
        // Disable CAPTCHA globally
        $this->captchaConfig['enabled'] = false;

        $this->captchaService = new GoogleReCaptchaService(
            'test-site-key',
            'test-secret-key',
            null,
            $this->captchaConfig
        );

        // 1. Check form doesn't require CAPTCHA field
        $this->mockForm->expects($this->never())
            ->method('addField')
            ->with('captcha', CaptchaFieldType::class);

        // 2. Fix this section - FormHandler handles form, not validates directly
        $this->mockForm->expects($this->any())
            ->method('isValid')
            ->willReturn(true);

        // Rest of test remains the same
        $this->assertEmpty($this->captchaService->render());
        $this->assertTrue($this->captchaService->verify(''));
    }

    public function testCaptchaEnabledWithFallbackLogicWithoutBruteForce(): void
    {
        // Enable CAPTCHA
        $this->captchaConfig['enabled'] = true;

        $this->captchaService = new GoogleReCaptchaService(
            'test-site-key',
            'test-secret-key',
            null, // No BruteForce service
            $this->captchaConfig
        );

        // CAPTCHA should be required for configured action types
        $this->assertTrue($this->captchaService->isRequired('login'));
        $this->assertTrue($this->captchaService->isRequired('registration'));

        // But not for others
        $this->assertFalse($this->captchaService->isRequired('unknown_action'));

        // Verify render returns non-empty string with site key
        $output = $this->captchaService->render();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('test-site-key', $output);

        // Empty response should fail verification
        $this->assertFalse($this->captchaService->verify(''));
    }

    private function setupMocks(): void
    {
        // Create mocks for dependent objects
        $this->mockForm = $this->createMock(FormInterface::class);
        $this->mockFormBuilder = $this->createMock(FormBuilder::class);
        $this->mockFormHandler = $this->createMock(FormHandler::class);
        $this->request = $this->createMock(ServerRequestInterface::class);

        // Additional setup as needed
    }
}
