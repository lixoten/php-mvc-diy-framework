<?php

declare(strict_types=1);

namespace App\Features\Testy;

use App\Enums\FlashMessageType;
use App\Features\Testy\Form\ContactFieldRegistry;
use App\Features\Testy\Form\ContactFormType;
use Core\Controller;
use App\Helpers\DebugRt as Debug;
use App\Helpers\FormHelper;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Form\FormBuilder;
use Core\Form\Validation\Validator;
use Core\Services\ConfigService;
use stdClass;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Core\Form\Form;
use Core\Form\FormFactory;
use Core\Form\FormFactoryInterface;
use Core\Form\FormHandler;
use Core\Form\FormHandlerInterface;
use Core\Form\Validation\Rules\MaxLength;
use Core\Form\Validation\Rules\MinLength;
use Core\Form\Validation\Rules\Required;
use Core\Logger;

/**
 * Testy controller
 *
 */
class TestyController extends Controller
{
    protected ConfigService $config;
    protected ?ServerRequestInterface $request = null; // Declare correctly with proper type

    protected FormFactoryInterface $formFactory;
    protected FormHandlerInterface $formHandler;
    protected Logger $logger;
    protected ContactFieldRegistry $contactFieldRegistry;
    protected ContactFormType $contactFormType;

    // protected FormBuilder $formBuilder;
    // protected Validator $validator;
    // protected FormHelper $formHelper;

    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        ConfigService $config,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        FormFactoryInterface $formFactory,
        FormHandlerInterface $formHandler,
        Logger $logger,
        ContactFieldRegistry $contactFieldRegistry,
        ContactFormType $contactFormType
    ) {
        parent::__construct(
            $route_params,
            $flash,
            $view,
            $httpFactory,
            $container
        );
        $this->config = $config;
        $this->formFactory = $formFactory;
        $this->formHandler = $formHandler;
        $this->logger = $logger;
        $this->contactFieldRegistry = $contactFieldRegistry;
        $this->contactFormType = $contactFormType;
    }


    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function indexAction(): ResponseInterface
    {
        return $this->view(TestyConst::VIEW_TESTY_INDEX, [
            'title' => 'Testy Index Action',
            'actionLinks' => $this->getActionLinks(
                'testy',
                ['index', 'testlogger', 'testsession', 'testdatabase', 'contact', 'contactSimple']
            )
        ]);
    }



    public function testloggerAction(): ResponseInterface
    {
        $content = "<h1>Logger String Interpolation Tests</h1>";
        //$content .= Debug::p($this->route_params, 0, true);  // Return output instead of echo

        // Create logger with debug collection enabled
        $loggerConfig = $this->config->get('logger');
        //Debug::p($loggerConfig);
        // $logger = new \Core\Logger(
        //     logDirectory: $loggerConfig['directory'],
        //     minLevel: $loggerConfig['min_level'],
        //     debugMode: true,
        //     samplingRate: 1.0,
        //     collectDebugOutput: true  // Enable debug collection
        // );
        // USE THE INJECTED LOGGER INSTEAD:
        // Enable debug collection if needed
        $this->logger->setDebugMode(true);
        $this->logger->setCollectDebugOutput(true);

        // Your existing logging code...
        // Test different log levels


        // Test cases for string interpolation
        $username = "john_doe";
        $userId = 12345;
        $amount = 99.95;

        $content = "<h1>Logger String Interpolation Tests</h1>";

        $this->logger->info("<h2>If you see this message in your log file, your logger is PSR-3 compliant!</h2>");
        $content .= $this->logger->getDebugOutput();



        // Simple variable interpolation
        $content .= "<b>Simple variable interpolation</b>";
        $this->logger->info("User $username has logged in");
        $content .= $this->logger->getDebugOutput();

        // Complex interpolation with expressions
        $content .= "<b>Complex interpolation with expressions</b>";
        $this->logger->info("User {$username} (ID: {$userId}) purchased items for \${$amount}");
        $content .= $this->logger->getDebugOutput();

        // Interpolation with array access
        $content .= "<b>Interpolation with array access</b>";
        $user = ['name' => 'Alice', 'role' => 'admin'];
        $this->logger->warning("Admin user {$user['name']} performed a sensitive operation");
        $content .= $this->logger->getDebugOutput();

        // Interpolation with object properties
        $content .= "<b>Interpolation with object properties</b>";
        $product = new stdClass();
        $product->name = "Deluxe Widget";
        $product->price = 49.99;
        $this->logger->error("Failed to process payment for {$product->name} at \${$product->price}");
        $content .= $this->logger->getDebugOutput();

        // Interpolation combined with context data
        $content .= "<b>Interpolation combined with context data</b>";
        $this->logger->info("Payment of \${$amount} processed for user $username", [
            'user_id' => $userId,
            'payment_method' => 'credit_card',
            'transaction_id' => 'TRX' . rand(10000, 99999)
        ]);
        $content .= $this->logger->getDebugOutput();
        $content .= "<p style='font-weight:bold;'>Test complete. Check your log file in the logs directory.</p><hr />";
        $content .= $this->logger->getDebugOutput();



        $this->logger->error("Operation failed: Something went wrong", [
            'message' => 'Something went wrong',
            'exception' => new \Exception('Something went wrong')
        ]);

        $content .= "<p style='font-weight:bold;'>PSR-3 compliance test completed. Check your log file.</p>";
        $content .= $this->logger->getDebugOutput();

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

    // Test action for session
    public function testDatabaseAction(): ResponseInterface
    {
        try {
            // Get database connection from container
            $db = $this->container->get('db');

            // Check connection by running simple query
            $connectionTest = $db->query("SELECT 'Connected successfully' as message");

            // Create test table if it doesn't exist
            $db->execute("
                CREATE TABLE IF NOT EXISTS test_data (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");

            // Insert test record
            $name = 'Test Record ' . date('Y-m-d H:i:s');
            $db->execute(
                "INSERT INTO test_data (name) VALUES (?)",
                [$name]
            );
            $insertId = $db->lastInsertId();


            // Test transaction
            $transactionResult = $db->transaction(function ($db) {
                $db->execute(
                    "INSERT INTO test_data (name) VALUES (?)",
                    ['Transaction Test ' . date('Y-m-d H:i:s')]
                );
                return 'Transaction completed successfully';
            });


            // Fetch records
            $records = $db->query("SELECT * FROM test_data ORDER BY id DESC LIMIT 10");


            // Return view with results
            return $this->view(TestyConst::VIEW_TESTY_TESTDATABASE, [
                'title' => 'Database Test',
                'connectionStatus' => $connectionTest[0]['message'],
                'insertId' => $insertId,
                'records' => $records,
                'transactionResult' => $transactionResult,
                'error' => null
            ]);
        } catch (\Throwable $e) {
            // Handle errors gracefully
            return $this->view(TestyConst::VIEW_TESTY_TESTDATABASE, [
                'title' => 'Database Test',
                'connectionStatus' => 'Failed',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }


        // // Return view
        // return $this->view(TestyConst::VIEW_TESTY_TESTDATABASE, [
        //     'title' => 'Database Test Action',
        // ]);
    }


    /**
     * Show the contact page
     *
     * @param ServerRequestInterface $request The current request
     * @return ResponseInterface
     */
    public function contactAction(ServerRequestInterface $request): ResponseInterface
    {
        $formType = $this->contactFormType;
        //$formType = new ContactFormType(
        $formType->setConfig([
            'fields' => [
                'name' => ['label' => 'Your Name'],
                'email' => [],
                'subject' => [],
                'message' => ['attributes' => ['rows' => '8']],
                'message2' => ['label' => 'test message', 'attributes' => ['rows' => '8']]
            ]
        ]);

        // Create the form
        $form = $this->formFactory->create($formType);


        // Handle form submission
        if ($this->formHandler->handle($form, $request)) {
            // Process the contact message
            $data = $form->getData();

            // $this->logger->setDebugMode(true);
            // $this->logger->setCollectDebugOutput(true);

            // Example: log the submission
            $this->logger->info('Contact form submitted', $data);
            // $content = $this->logger->getDebugOutput();
            // Debug::p($content, 0);

            // Add flash message
            $this->flash->add("Your message has been sent successfully", FlashMessageType::Success);

            // Redirect to thank you page or back to contact
            return $this->redirect('/testy/contact');
        }

        return $this->view(TestyConst::VIEW_TESTY_CONTACT, [
            'title' => 'Contact Us',
            'actionLinks' => $this->getActionLinks(
                'testy',
                ['index', 'testlogger', 'testsession', 'testdatabase', 'contact']
            ),
            'form' => $form
        ]);
    }
}
## 403
