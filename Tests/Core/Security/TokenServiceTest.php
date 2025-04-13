<?php

declare(strict_types=1);

namespace Tests\Core\Security;

use Core\Security\TokenService;
use Core\Interfaces\ConfigInterface;
use PHPUnit\Framework\TestCase;

class TokenServiceTest extends TestCase
{
    private $configService;
    private $tokenService;
    private const SECRET_KEY = 'test_secret_key_for_unit_tests';

    protected function setUp(): void
    {
        // Mock the configuration service
        $this->configService = $this->createMock(ConfigInterface::class);
        $this->configService->method('get')
            ->with('app.secret')
            ->willReturn(self::SECRET_KEY);

        $this->tokenService = new TokenService($this->configService);
    }

    public function testConstructorThrowsExceptionWithEmptySecret(): void
    {
        $this->configService = $this->createMock(ConfigInterface::class);
        $this->configService->method('get')
            ->with('app.secret')
            ->willReturn('');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('A valid secret key is required for the TokenService.');

        new TokenService($this->configService);
    }

    public function testGenerateReturnsString(): void
    {
        $token = $this->tokenService->generate();

        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token)); // bin2hex of 32 bytes = 64 hex chars
    }

    public function testGenerateWithCustomLength(): void
    {
        $token = $this->tokenService->generate(16);

        $this->assertIsString($token);
        $this->assertEquals(32, strlen($token)); // bin2hex of 16 bytes = 32 hex chars
    }

    public function testGenerateWithExpiry(): void
    {
        $result = $this->tokenService->generate(32, true, 3600);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('expires_at', $result);
        $this->assertIsString($result['token']);
        $this->assertIsInt($result['expires_at']);

        // Check expiry time is roughly correct (allowing 5 seconds for test execution)
        $expectedExpiry = time() + 3600;
        $this->assertGreaterThanOrEqual($expectedExpiry - 5, $result['expires_at']);
        $this->assertLessThanOrEqual($expectedExpiry + 5, $result['expires_at']);
    }

    public function testGenerateUrlSafe(): void
    {
        $token = $this->tokenService->generateUrlSafe();

        $this->assertIsString($token);
        // Check it only contains URL-safe characters
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9_-]+$/', $token);
    }

    public function testGenerateWithExpiryWrapper(): void
    {
        $result = $this->tokenService->generateWithExpiry(32, 3600);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('expires_at', $result);
        $this->assertIsString($result['token']);
        $this->assertIsInt($result['expires_at']);
    }

    public function testHasExpiredWithExpiredToken(): void
    {
        $expiredTime = time() - 100; // 100 seconds in the past

        $this->assertTrue($this->tokenService->hasExpired($expiredTime));
    }

    public function testHasExpiredWithFutureToken(): void
    {
        $futureTime = time() + 3600; // 1 hour in the future

        $this->assertFalse($this->tokenService->hasExpired($futureTime));
    }

    public function testGenerateSigned(): void
    {
        $data = 'user:123';
        $token = $this->tokenService->generateSigned($data);

        $this->assertIsString($token);
        $this->assertStringContainsString('.', $token); // Should contain signature separator
    }

    public function testVerifySignedWithValidToken(): void
    {
        $data = 'user:123';
        $token = $this->tokenService->generateSigned($data);

        $result = $this->tokenService->verifySigned($token);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('timestamp', $result);
        $this->assertEquals($data, $result['data']);
    }

    public function testVerifySignedWithInvalidFormat(): void
    {
        $this->assertFalse($this->tokenService->verifySigned('invalid-token'));
    }

    public function testVerifySignedWithTamperedSignature(): void
    {
        $data = 'user:123';
        $token = $this->tokenService->generateSigned($data);

        // Tamper with the signature
        $parts = explode('.', $token, 2);
        $tamperedToken = 'invalid' . $parts[0] . '.' . $parts[1];

        $this->assertFalse($this->tokenService->verifySigned($tamperedToken));
    }

    public function testVerifySignedWithTamperedPayload(): void
    {
        $data = 'user:123';
        $token = $this->tokenService->generateSigned($data);

        // Modify the last character of the token (part of the payload)
        $tamperedToken = substr($token, 0, -1) . 'X';

        $this->assertFalse($this->tokenService->verifySigned($tamperedToken));
    }

    public function testVerifySignedWithExpiredToken(): void
    {
        $data = 'user:123';
        $token = $this->tokenService->generateSigned($data);
        sleep(3); // Wait 2 seconds


        // The token should fail verification if maxAge is very small
        $this->assertFalse($this->tokenService->verifySigned($token, null, 1));

        // But should pass with a reasonable maxAge
        $this->assertIsArray($this->tokenService->verifySigned($token, null, 3600));
    }

    public function testVerifySignedWithCustomKey(): void
    {
        $data = 'user:123';
        $customKey = 'custom_secret_key';

        // Generate with custom key
        $token = $this->tokenService->generateSigned($data, $customKey);

        // Should pass verification with same custom key
        $this->assertIsArray($this->tokenService->verifySigned($token, $customKey));

        // Should fail verification with default key
        $this->assertFalse($this->tokenService->verifySigned($token));
    }
}
