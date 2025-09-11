<?php

declare(strict_types=1);

namespace Tests\Core\Security\Captcha;

use Core\Security\BruteForceProtectionService;
use Core\Security\Captcha\GoogleReCaptchaService;
use PHPUnit\Framework\TestCase;

class GoogleReCaptchaServiceTest extends TestCase
{
    private $siteKey = 'test-site-key';
    private $secretKey = 'test-secret-key';
    private $bruteForceService;

    protected function setUp(): void
    {
        $this->bruteForceService = $this->createMock(BruteForceProtectionService::class);
    }

    public function testIsEnabledReturnsTrueByDefault(): void
    {
        $service = new GoogleReCaptchaService(
            $this->siteKey,
            $this->secretKey,
            null, // No BruteForce service
            [] // Empty config
        );

        $this->assertTrue($service->isEnabled());
    }

    public function testIsEnabledRespectsFlagWhenProvided(): void
    {
        $config = ['enabled' => false];
        $service = new GoogleReCaptchaService(
            $this->siteKey,
            $this->secretKey,
            null,
            $config
        );

        $this->assertFalse($service->isEnabled());
    }

    public function testIsRequiredReturnsFalseWhenDisabled(): void
    {
        $config = ['enabled' => false];
        $service = new GoogleReCaptchaService(
            $this->siteKey,
            $this->secretKey,
            null,
            $config
        );

        $this->assertFalse($service->isRequired('login', 'test@example.com'));
    }

    public function testIsRequiredWithoutBruteForceServiceUsesDirectConfig(): void
    {
        $config = [
            'enabled' => true,
            'thresholds' => [
                'login' => 3
            ]
        ];

        $service = new GoogleReCaptchaService(
            $this->siteKey,
            $this->secretKey,
            null, // No BruteForce service
            $config
        );

        // Should return true for configured action types
        $this->assertTrue($service->isRequired('login'));

        // Should return false for unknown action types
        $this->assertFalse($service->isRequired('unknown_action'));
    }

    public function testVerifyBypassesVerificationWhenDisabled(): void
    {
        $config = ['enabled' => false];
        $service = new GoogleReCaptchaService(
            $this->siteKey,
            $this->secretKey,
            null,
            $config
        );

        // Even with empty response, should return true when disabled
        $this->assertTrue($service->verify(''));
    }

    public function testVerifyReturnsFalseForEmptyResponse(): void
    {
        $config = ['enabled' => true];
        $service = new GoogleReCaptchaService(
            $this->siteKey,
            $this->secretKey,
            null,
            $config
        );

        $this->assertFalse($service->verify(''));
    }

    public function testRenderReturnsEmptyStringWhenDisabled(): void
    {
        $config = ['enabled' => false];
        $service = new GoogleReCaptchaService(
            $this->siteKey,
            $this->secretKey,
            null,
            $config
        );

        $this->assertEmpty($service->render());
    }

    public function testRenderReturnsScriptTagsWhenEnabled(): void
    {
        $config = ['enabled' => true];
        $service = new GoogleReCaptchaService(
            $this->siteKey,
            $this->secretKey,
            null,
            $config
        );

        $output = $service->render();
        $this->assertStringContainsString('g-recaptcha', $output);
        $this->assertStringContainsString($this->siteKey, $output);
    }
}
