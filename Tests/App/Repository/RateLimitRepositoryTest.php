<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\Repository\RateLimitRepository;
use Core\Database\ConnectionInterface;
use PHPUnit\Framework\TestCase;
use PDOStatement;

class RateLimitRepositoryTest extends TestCase
{
    private $connection;
    private $repository;
    private $statement;

    protected function setUp(): void
    {
        $this->statement = $this->createMock(PDOStatement::class);
        $this->connection = $this->createMock(ConnectionInterface::class);
        $this->repository = new RateLimitRepository($this->connection);
    }

    public function testRecordAttempt(): void
    {
        // Setup test data
        $data = [
            'identifier' => 'test@example.com',
            'action_type' => 'login',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Test'
        ];

        // Mock the connection and statement behavior
        $this->connection->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statement);

        $this->statement->expects($this->exactly(6))
            ->method('bindValue');

        $this->statement->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // Execute and assert
        $result = $this->repository->recordAttempt($data);
        $this->assertTrue($result);
    }

    public function testCountRecentAttempts(): void
    {
        // Setup expected data
        $identifier = 'test@example.com';
        $actionType = 'login';
        $since = time() - 300; // 5 minutes ago

        // Mock the fetch result
        $this->connection->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statement);

        $this->statement->expects($this->once())
            ->method('execute');

        $this->statement->expects($this->once())
            ->method('fetch')
            ->willReturn(['count' => 5]);

        // Execute and assert
        $result = $this->repository->countRecentAttempts($identifier, $actionType, $since);
        $this->assertEquals(5, $result);
    }

    public function testCountRecentAttemptsFromIp(): void
    {
        // Setup expected data
        $ipAddress = '127.0.0.1';
        $actionType = 'login';
        $since = time() - 300; // 5 minutes ago

        // Mock the fetch result
        $this->connection->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statement);

        $this->statement->expects($this->once())
            ->method('execute');

        $this->statement->expects($this->once())
            ->method('fetch')
            ->willReturn(['count' => 8]);

        // Execute and assert
        $result = $this->repository->countRecentAttemptsFromIp($ipAddress, $actionType, $since);
        $this->assertEquals(8, $result);
    }

    public function testClearForIdentifier(): void
    {
        // Setup expected data
        $identifier = 'test@example.com';
        $actionType = 'login';

        // Mock execution
        $this->connection->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statement);

        $this->statement->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // Execute and assert
        $result = $this->repository->clearForIdentifier($identifier, $actionType);
        $this->assertTrue($result);
    }

    public function testUpdateLastAttemptStatus(): void
    {
        // Setup expected data
        $identifier = 'test@example.com';
        $actionType = 'login';
        $success = true;

        // Mock execution with row affected
        $this->connection->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statement);

        $this->statement->expects($this->once())
            ->method('execute');

        $this->statement->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        // Execute and assert
        $result = $this->repository->updateLastAttemptStatus($identifier, $actionType, $success);
        $this->assertTrue($result);
    }
}