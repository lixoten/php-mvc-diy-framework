<?php

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
/**
 * How to run this file:
 * `php test-logger.php`
 */

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

/**
 * This function accepts ANY PSR-3 compliant logger
 */
function doSomethingWithLogger(Psr\Log\LoggerInterface $logger)
{
    $logger->info("If you see this message in your log file, your logger is PSR-3 compliant!");
    $logger->debug("Processing data", ['status' => 'starting']);

    try {
        // Simulate an operation that might fail
        if (rand(0, 1) === 0) {
            throw new \RuntimeException("Something went wrong");
        }
        $logger->info("Operation completed successfully");
    } catch (\Exception $e) {
        $logger->error("Operation failed: {message}", [
            'message' => $e->getMessage(),
            'exception' => $e
        ]);
    }
}

// Create your logger
$logger = new Core\Logger();
$logger->setDebugMode(true);

// Test it with a function expecting a PSR-3 logger
doSomethingWithLogger($logger);

echo "PSR-3 compliance test completed. Check your log file.";
