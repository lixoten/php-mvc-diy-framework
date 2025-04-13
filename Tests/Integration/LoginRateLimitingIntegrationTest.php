<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Repository\RateLimitRepository;
use Core\Database\ConnectionInterface;
use Core\Security\RateLimitService;
use Core\Interfaces\ConfigInterface;
use PHPUnit\Framework\TestCase;

class LoginRateLimitingIntegrationTest extends TestCase
{
    private $connection;
    private $repository;
    private $configService;
    private $service;

    protected function setUp(): void
    {
        // Real connection can be replaced with SQLite in-memory for tests
        $this->connection = $this->createMock(ConnectionInterface::class);
        $this->repository = new RateLimitRepository($this->connection);
        $this->configService = $this->createMock(ConfigInterface::class);

        // Use a test-specific configuration
        $testConfig = [
            'login' => ['limit' => 3, 'window' => 60], // 3 attempts per minute
        ];

        $this->service = new RateLimitService(
            $this->repository,
            $this->configService,
            $testConfig
        );
    }

    public function testLoginRateLimitingFullFlow(): void
    {
        $identifier = 'test@example.com';
        $actionType = 'login';
        $ipAddress = '127.0.0.1';

        // 1. Initial check should pass (no attempts yet)
        $this->mockCountResults(0, 0);

        try {
            $this->service->checkRateLimit($identifier, $actionType, $ipAddress);
            $this->assertTrue(true); // No exception = pass
        } catch (\Exception $e) {
            $this->fail('Should not have thrown exception on first attempt');
        }

        // 2. Record failed login attempts
        $this->mockRecordAttempt();

        for ($i = 0; $i < 3; $i++) {
            $this->service->recordAttempt($identifier, $actionType, $ipAddress);
        }

        // 3. Now we should be at the limit
        $this->mockCountResults(3, 3);

        $this->expectException(\Core\Auth\Exception\AuthenticationException::class);
        $this->service->checkRateLimit($identifier, $actionType, $ipAddress);
    }

    private function mockCountResults(int $identifierCount, int $ipCount): void
    {
        // Configure the mocked repository to return our test counts
        $stmt = $this->createMock(\PDOStatement::class);

        // Mock for countRecentAttempts
        $this->connection->expects($this->any())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->any())
            ->method('execute');

        $stmt->expects($this->any())
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['count' => $identifierCount],
                ['count' => $ipCount]
            );
    }

    private function mockRecordAttempt(): void
    {
        $stmt = $this->createMock(\PDOStatement::class);

        $this->connection->expects($this->any())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->any())
            ->method('bindValue');

        $stmt->expects($this->any())
            ->method('execute')
            ->willReturn(true);
    }
}