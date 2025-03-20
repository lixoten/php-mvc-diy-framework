<?php

declare(strict_types=1);

namespace App\Features\Testy;

use App\Enums\FlashMessageType;
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
use Core\Form\Validation\Rules\MaxLength;
use Core\Form\Validation\Rules\MinLength;
use Core\Form\Validation\Rules\Required;

/**
 * Testy controller
 *
 */
class xxxxxxTestyController extends Controller
{
    protected ConfigService $config;
    protected ?ServerRequestInterface $request = null; // Declare correctly with proper type

    protected FormBuilder $formBuilder;
    protected Validator $validator;
    protected FormHelper $formHelper;

    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        ConfigService $config,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        FormBuilder $formBuilder,
        Validator $validator,
        FormHelper $formHelper  // <-- Add this parameter
    ) {
        parent::__construct(
            $route_params,
            $flash,
            $view,
            $httpFactory,
            $container
        );
        $this->config = $config;
        $this->formBuilder = $formBuilder;
        $this->validator = $validator;
        $this->formHelper = $formHelper;
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
     * @return ResponseInterface
     */
    public function contactAction(): ResponseInterface
    {
        $csrf = $this->container->get('csrf');

        // Create a test form
        $form = $this->createContactForm();
        $errors = [];
        $formData = null;

        // Check for submission success message in session
        $showSubmittedData = $this->session->get('form_submitted_data');
        if ($showSubmittedData) {
            // We have submitted data from a previous request
            $formData = $showSubmittedData;
            $this->session->remove('form_submitted_data'); // Clean up
        }

        // Handle form submission if POST
        if ($this->request && $this->request->getMethod() === 'POST') {
            $data = $this->request->getParsedBody();

            if ($form->handle($data)) {
                // Form is valid
                $this->flash->add("Form submitted successfully!", FlashMessageType::Success);

                // Save form data in session for the next request
                $this->session->set('form_submitted_data', $form->getData());

                // Just for testing, echo out the data
                $name = $form->getValue('name', '');
                $this->flash->add("Hello, $name!", FlashMessageType::Info);

                // Redirect to prevent form resubmission
                return $this->redirect('/testy/contact');
            }

            // Form has errors
            $errors = $form->getErrors();
            $this->flash->add("Please correct the errors below.", FlashMessageType::Error);
        }

        return $this->view(TestyConst::VIEW_TESTY_CONTACT, [
            'title' => 'CSRF Protection Test',
            'form' => $form,
            'errors' => $errors,
            'formData' => $formData, // Pass the stored data if present
            'actionLinks' => $this->getActionLinks(
                'testy',
                ['index', 'testlogger', 'testsession', 'testdatabase', 'contact', 'contactsimple']
            ),
            'csrf' => $csrf
        ]);
    }


    // Add this helper method
    protected function createContactForm(): Form
    {
        $form = new Form('test', $this->formBuilder);

        // Add validation rules
        $form
            ->addRule('name', [
                new Required(),
                new MinLength(2),
                new MaxLength(100)
            ]);

        // Configure form builder
        $form->builder()
            ->action('/testy/contact') // Submit to this same action
            ->method('POST')
            ->setAttribute('class', 'needs-validation')
            ->text('name', ['class' => 'form-control', 'id' => 'name']);

        return $form;
    }





   /**
     * Show the contact page
     *
     * @return ResponseInterface
     */
    // Update the contactsimpleAction method
    public function contactsimpleAction(): ResponseInterface
    {
        $csrf = $this->container->get('csrf');
        //$formHelper = new FormHelper(); // Create the form helper
        //$formHelper = $this->container->get('formHelper');
        $formHelper = $this->formHelper;
        $errors = [];
        $formData = null;

        // Check if there's stored form data from a previous submission
        $storedData = $this->session->get('simple_form_data');
        Debug::p($_SESSION, 0);
        if ($storedData) {
            $formData = $storedData;
            $this->session->remove('simple_form_data');
        }

        // Handle form submission
        if ($this->request && $this->request->getMethod() === 'POST') {
            $data = $this->request->getParsedBody();

            // Basic validation
            $errors = $this->validateSimpleForm($data);

            // If no errors, process the form
            if (empty($errors)) {
                // Store data in session for display after redirect
                $this->session->set('simple_form_data', $data);

                $this->flash->add("Thanks for your message, " . htmlspecialchars($data['name'] ?? '') . "!", FlashMessageType::Success);

                // Redirect to prevent resubmission
                return $this->redirect('/testy/contactsimple');
            }

            // Keep the submitted data for repopulating the form
            $formData = $data;

            // Add error flash message
            $this->flash->add("Please correct the errors in your form.", FlashMessageType::Error);
        }

        return $this->view(TestyConst::VIEW_TESTY_CONTACTSIMPLE, [
            'title' => 'Simple Contact Form',
            'errors' => $errors,
            'formData' => $formData,
            'formHelper' => $formHelper, // Pass the form helper to the view
            'actionLinks' => $this->getActionLinks('testy', [
                'index', 'testlogger', 'testsession', 'testdatabase', 'contact', 'contactsimple'
            ]),
            'csrf' => $csrf
        ]);
    }


    /**
     * Validate the simple contact form
     *
     * @param array $data Form data
     * @return array Validation errors
     */
    private function validateSimpleForm(array $data): array
    {
        $errors = [];

        // Name validation
        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        } elseif (strlen($data['name']) < 2) {
            $errors['name'] = 'Name must be at least 2 characters';
        } elseif (strlen($data['name']) > 100) {
            $errors['name'] = 'Name cannot exceed 100 characters';
        }

        // Email validation
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }

        // Subject validation
        if (empty($data['subject'])) {
            $errors['subject'] = 'Subject is required';
        } elseif (strlen($data['subject']) < 5) {
            $errors['subject'] = 'Subject must be at least 5 characters';
        } elseif (strlen($data['subject']) > 200) {
            $errors['subject'] = 'Subject cannot exceed 200 characters';
        }

        // Message validation
        if (empty($data['message'])) {
            $errors['message'] = 'Message is required';
        } elseif (strlen($data['message']) < 10) {
            $errors['message'] = 'Message must be at least 10 characters';
        } elseif (strlen($data['message']) > 2000) {
            $errors['message'] = 'Message cannot exceed 2000 characters';
        }

        return $errors;
    }


    /**
     * Handle CSRF form submission
     *
     * @return ResponseInterface
     */
    public function csrfSubmitAction(): ResponseInterface
    {
        // If this method is called, CSRF validation was successful
        $this->flash->add('Form submitted successfully with valid CSRF token!', FlashMessageType::Success);

        // Get POST data - using null coalescing to prevent null errors
        $data = $this->request ? $this->request->getParsedBody() : [];
        $name = $data['name'] ?? 'unknown';

        return $this->view(TestyConst::VIEW_TESTY_INDEX, [
            'title' => 'CSRF Test Result',
            'message' => "Hello, $name! Your form was processed successfully.",
            'actionLinks' => $this->getActionLinks('testy', ['index', 'testlogger', 'testsession', 'testdatabase', 'contact'])
        ]);
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
