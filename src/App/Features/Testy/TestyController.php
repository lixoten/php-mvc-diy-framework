<?php

declare(strict_types=1);

namespace App\Features\Testy;

use App\Enums\FlashMessageType;
use Core\Controller;
use App\Helpers\DebugRt as Debug;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Services\ConfigService;
use stdClass;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Http\Message\ResponseInterface;

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
        ConfigService $config,
        HttpFactory $httpFactory
    ) {
        parent::__construct(
            $route_params,
            $flash,
            $view,
            $httpFactory
        );
        $this->config = $config;
    }


    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function indexAction(): ResponseInterface
    {
        // $response = $this->httpFactory->createResponse();
        // $response->getBody()->write("<h1>Testsdddd</h1>");
        // return $response;

        return $this->view(TestyConst::VIEW_TESTY_INDEX, [
            'title' => 'Testy Index Action',
            'actionLinks' => $this->getActionLinks('testy', ['index', 'testlogger', 'testsession'])
        ]);
    }



    public function testloggerAction(): ResponseInterface
    {
        $content = "<h1>Logger String Interpolation Tests</h1>";
        //$content .= Debug::p($this->route_params, 0, true);  // Return output instead of echo

        // Create logger with debug collection enabled
        $loggerConfig = $this->config->get('logger');
        $logger = new \Core\Logger(
            logDirectory: $loggerConfig['directory'],
            minLevel: $loggerConfig['min_level'],
            debugMode: true,
            samplingRate: 1.0,
            collectDebugOutput: true  // Enable debug collection
        );

        // Your existing logging code...
        // Test different log levels


        // Test cases for string interpolation
        $username = "john_doe";
        $userId = 12345;
        $amount = 99.95;

        $content = "<h1>Logger String Interpolation Tests</h1>";

        $logger->info("<h2>If you see this message in your log file, your logger is PSR-3 compliant!</h2>");
        $content .= $logger->getDebugOutput();



        // Simple variable interpolation
        $content .= "<b>Simple variable interpolation</b>";
        $logger->info("User $username has logged in");
        $content .= $logger->getDebugOutput();

        // Complex interpolation with expressions
        $content .= "<b>Complex interpolation with expressions</b>";
        $logger->info("User {$username} (ID: {$userId}) purchased items for \${$amount}");
        $content .= $logger->getDebugOutput();

        // Interpolation with array access
        $content .= "<b>Interpolation with array access</b>";
        $user = ['name' => 'Alice', 'role' => 'admin'];
        $logger->warning("Admin user {$user['name']} performed a sensitive operation");
        $content .= $logger->getDebugOutput();

        // Interpolation with object properties
        $content .= "<b>Interpolation with object properties</b>";
        $product = new stdClass();
        $product->name = "Deluxe Widget";
        $product->price = 49.99;
        $logger->error("Failed to process payment for {$product->name} at \${$product->price}");
        $content .= $logger->getDebugOutput();

        // Interpolation combined with context data
        $content .= "<b>Interpolation combined with context data</b>";
        $logger->info("Payment of \${$amount} processed for user $username", [
            'user_id' => $userId,
            'payment_method' => 'credit_card',
            'transaction_id' => 'TRX' . rand(10000, 99999)
        ]);
        $content .= $logger->getDebugOutput();
        $content .= "<p style='font-weight:bold;'>Test complete. Check your log file in the logs directory.</p><hr />";
        $content .= $logger->getDebugOutput();



        $logger->error("Operation failed: Something went wrong", [
            'message' => 'Something went wrong',
            'exception' => new \Exception('Something went wrong')
        ]);

        $content .= "<p style='font-weight:bold;'>PSR-3 compliance test completed. Check your log file.</p>";
        $content .= $logger->getDebugOutput();

        return $this->view(TestyConst::VIEW_TESTY_TESTLOGGER, [
            'title' => 'Testy testlogger Action',
            'actionLinks' => $this->getActionLinks('testy', ['index', 'testlogger']),
            'additional_content' => $content
        ]);
    }


    // Test action for session
    public function testsessionAction(): ResponseInterface
    {
        // Get visit count from session or initialize
        $visits = $this->session->get('visit_count', 0);
        $visits++;

        // Update count in session
        $this->session->set('visit_count', $visits);

        // Add flash messages - use the enum instead of strings
        $messageType = $visits % 2 ? FlashMessageType::Success : FlashMessageType::Info;
        $this->flash->add("You've visited this page $visits times", $messageType);

        // Return view
        return $this->view(TestyConst::VIEW_TESTY_TESTSESSION, [
            'title' => 'Session Test',
            'visits' => $visits,
            'sessionData' => $this->session->all() // Show all session data
        ]);
    }

    // Test action to clear session
    // public function resetSessionAction(): void
    public function resetSessionAction(): ResponseInterface
    {
        // Clear visit counter
        $this->session->remove('visit_count');

        // Add flash message
        $this->flash->add("Session counter reset", FlashMessageType::Success);

        // Redirect back to session test
        return $this->redirect('/testy/testsession');
    }







    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function xxxtestloggerAction(): ResponseInterface
    {
       // Debug::p($this->route_params, 0);

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
        $content = "<h1>Logger String Interpolation Tests</h1>";

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


        $content .= "<p>Test complete. Check your log file in the logs directory.</p>";

        $logger->info("If you see this message in your log file, your logger is PSR-3 compliant!");
        $logger->error("Operation failed: Something went wrong", [
            'message' => 'Something went wrong',
            'exception' => new \Exception('Something went wrong')
        ]);

        $content .= "PSR-3 compliance test completed. Check your log file.";
        //Debug::p($content);
        return $this->view('testy/index', [
            'title' => "testAction in Testy",
            'additional_content' => $content //
        ]);
    }
}
