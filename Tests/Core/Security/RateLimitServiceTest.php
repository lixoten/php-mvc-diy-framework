<?php

declare(strict_types=1);

namespace Tests\Core\Security;

use App\Repository\RateLimitRepositoryInterface;
use Core\Auth\Exception\AuthenticationException;
use Core\Interfaces\ConfigInterface;
use Core\Security\RateLimitService;
use PHPUnit\Framework\TestCase;

class RateLimitServiceTest extends TestCase
{
    private $repository;
    private $configService;
    private $service;
    private $customConfig;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(RateLimitRepositoryInterface::class);
        $this->configService = $this->createMock(ConfigInterface::class);

        // Define custom test config for predictable behavior
        $this->customConfig = [
            'login' => ['limit' => 5, 'window' => 300],
            'registration' => ['limit' => 3, 'window' => 1800],
        ];

        $this->service = new RateLimitService(
            $this->repository,
            $this->configService,
            $this->customConfig
        );
    }

    public function testCheckRateLimitUnderLimit(): void
    {
        $identifier = 'test@example.com';
        $actionType = 'login';
        $ipAddress = '127.0.0.1';
        $since = time() - 300;

        // Setup repository to return counts below limits
        $this->repository->expects($this->once())
            ->method('countRecentAttempts')
            ->with($identifier, $actionType, $this->greaterThan(time() - 301))
            ->willReturn(3); // Below limit of 5

        $this->repository->expects($this->once())
            ->method('countRecentAttemptsFromIp')
            ->with($ipAddress, $actionType, $this->greaterThan(time() - 301))
            ->willReturn(8); // Below IP limit (3x regular = 15)

        // Should not throw exception
        try {
            $this->service->checkRateLimit($identifier, $actionType, $ipAddress);
            $this->assertTrue(true); // If we get here, no exception was thrown
        } catch (AuthenticationException $e) {
            $this->fail('Exception was thrown when it should not have been');
        }
    }

    public function testCheckRateLimitExceedsIdentifierLimit(): void
    {
        $identifier = 'test@example.com';
        $actionType = 'login';
        $ipAddress = '127.0.0.1';

        // Setup repository to return counts above identifier limit
        $this->repository->expects($this->once())
            ->method('countRecentAttempts')
            ->willReturn(6); // Above limit of 5

        // Should throw exception
        $this->expectException(AuthenticationException::class);
        $this->service->checkRateLimit($identifier, $actionType, $ipAddress);
    }

    public function testCheckRateLimitExceedsIpLimit(): void
    {
        $identifier = 'test@example.com';
        $actionType = 'login';
        $ipAddress = '127.0.0.1';

        // Setup repository to return counts below identifier limit but above IP limit
        $this->repository->expects($this->once())
            ->method('countRecentAttempts')
            ->willReturn(4); // Below limit of 5

        $this->repository->expects($this->once())
            ->method('countRecentAttemptsFromIp')
            ->willReturn(16); // Above IP limit (3x regular = 15)

        // Should throw exception
        $this->expectException(AuthenticationException::class);
        $this->service->checkRateLimit($identifier, $actionType, $ipAddress);
    }

    public function testRecordAttempt(): void
    {
        $identifier = 'test@example.com';
        $actionType = 'login';
        $ipAddress = '127.0.0.1';
        $userAgent = 'PHPUnit Test';

        // Verify repository is called with correct data
        $this->repository->expects($this->once())
            ->method('recordAttempt')
            ->with($this->callback(function ($data) use ($identifier, $actionType, $ipAddress) {
                return $data['identifier'] === $identifier &&
                       $data['action_type'] === $actionType &&
                       $data['ip_address'] === $ipAddress &&
                       $data['success'] === 0;
            }))
            ->willReturn(true);

        $this->service->recordAttempt($identifier, $actionType, $ipAddress, false, $userAgent);
    }
}