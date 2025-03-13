<?php

namespace Tests\Core;

use Core\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class LoggerTest extends TestCase
{
    private $logDir;
    private $logFile;

    protected function setUp(): void
    {
        // Create temporary directory for logs
        $this->logDir = sys_get_temp_dir() . '/logger_test_' . uniqid();
        mkdir($this->logDir);
        $this->logFile = $this->logDir . '/app-' . date('Y-m-d') . '.log';
    }

    protected function tearDown(): void
    {
        // Clean up test log files
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
        rmdir($this->logDir);
    }

    public function testLoggerWritesMessages(): void
    {
        $logger = new Logger($this->logDir);

        $logger->info('Test message');

        $this->assertFileExists($this->logFile);
        $content = file_get_contents($this->logFile);
        $this->assertStringContainsString('[INFO] Test message', $content);
    }

    public function testLoggerInterpolatesContext(): void
    {
        $logger = new Logger($this->logDir);

        $logger->info('User {username} logged in', ['username' => 'john']);

        $content = file_get_contents($this->logFile);
        $this->assertStringContainsString('User john logged in', $content);
    }

    public function testLogLevelFiltering(): void
    {
        $logger = new Logger($this->logDir, Logger::ERROR);

        $logger->error('Error message');  // Should be logged
        $logger->info('Info message');    // Should NOT be logged

        $content = file_get_contents($this->logFile);
        $this->assertStringContainsString('Error message', $content);
        $this->assertStringNotContainsString('Info message', $content);
    }
}
