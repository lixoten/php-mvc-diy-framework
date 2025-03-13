<?php

declare(strict_types=1);

namespace App\Features\Testy;

use Core\Controller;
use App\Helpers\DebugRt as Debug;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Services\ConfigService;
use Core\View;
use stdClass;

/**
 * Testy controller
 *
 */
class TestyController extends Controller
{
    protected ConfigService $config;

    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        ConfigService $config
    ) {
        parent::__construct(
            $route_params,
            $flash,
            $view
        );
        $this->config = $config;
    }


    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction(): void
    {
        echo "<h1>Tests</h1>";
        echo '1. <a href="/testy/testlogger">Test Logger</a>';


        //$this->view(TestyConst::VIEW_TESTY_INDEX, [
        $this->view('testy/index', [
            'title' => 'Welcome Testy'
        ]);
    }


    /**
     * Show the index page
     *
     * @return void
     */
    public function testloggerAction(): void
    {
        Debug::p($this->route_params, 0);

        // Get Logger config settings
        $loggerConfig = $this->config->get('logger');

        // Create logger with appropriate settings
        $logger = new \Core\Logger(
            logDirectory: $loggerConfig['directory'],
            minLevel: $loggerConfig['min_level'],
            debugMode: $loggerConfig['debug_mode']
        );

        // Test cases for string interpolation
        $username = "john_doe";
        $userId = 12345;
        $amount = 99.95;
        echo "<h1>Logger String Interpolation Tests</h1>";

        // Test different log levels
        // Simple variable interpolation
        $logger->info("User $username has logged in");

        // Complex interpolation with expressions
        $logger->info("User {$username} (ID: {$userId}) purchased items for \${$amount}");

        // Interpolation with array access
        $user = ['name' => 'Alice', 'role' => 'admin'];
        $logger->warning("Admin user {$user['name']} performed a sensitive operation");

        // Interpolation with object properties
        $product = new stdClass();
        $product->name = "Deluxe Widget";
        $product->price = 49.99;
        $logger->error("Failed to process payment for {$product->name} at \${$product->price}");

        // Interpolation combined with context data
        $logger->info("Payment of \${$amount} processed for user $username", [
            'user_id' => $userId,
            'payment_method' => 'credit_card',
            'transaction_id' => 'TRX' . rand(10000, 99999)
        ]);


        echo "<p>Test complete. Check your log file in the logs directory.</p>";

        $logger->info("If you see this message in your log file, your logger is PSR-3 compliant!");
        $logger->error("Operation failed: Something went wrong", [
            'message' => 'Something went wrong',
            'exception' => new \Exception('Something went wrong')
        ]);

        echo "PSR-3 compliance test completed. Check your log file.";

        $this->view('testy/index', [
            'title' => "testAction in Testy"
        ]);
    }
}
