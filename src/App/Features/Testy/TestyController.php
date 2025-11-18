<?php

/**
 * TestyController.php
 *
 * This file contains the TestyController class, which handles various actions
 * such as logging, session management, database testing, and email testing.
 * It is part of the Testy feature in the application.
 *
 * @package App\Features\Testy
 */

declare(strict_types=1);

namespace App\Features\Testy;

use Core\Services\FormatterService;
use App\Helpers\DebugRt;
use App\Enums\FlashMessageType;
use App\Enums\Url;
use App\Services\Email\EmailNotificationService;
use App\Services\FeatureMetadataService;
use App\Services\Interfaces\FlashMessageServiceInterface;
use App\Services\PaginationService;
use Core\AbstractCrudController;
use Core\Context\CurrentContext;
use Core\Services\ConfigService;
use stdClass;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Core\Form\FormFactoryInterface;
use Core\Form\FormHandlerInterface;
use Core\Form\FormTypeInterface;
use Core\Formatters\TextFormatter;
use Core\Interfaces\ConfigInterface;
use Core\List\ListFactoryInterface;
use Core\List\ListTypeInterface;
use Core\Services\TypeResolverService;
use Psr\Log\LoggerInterface;
use Core\List\Renderer\ListRendererInterface;
use Core\Services\BaseFeatureService;

/**
 * Testy controller
 *
 */
class TestyController extends AbstractCrudController
{
    protected ?ServerRequestInterface $request = null;

    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash22,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        CurrentContext $scrap,
        //-----------------------------------------
        FeatureMetadataService $featureMetadataService,
        FormFactoryInterface $formFactory,
        FormHandlerInterface $formHandler,
        FormTypeInterface $formType,
        ListFactoryInterface $listFactory,
        ListTypeInterface $listType,
        TestyRepositoryInterface $repository,
        TypeResolverService $typeResolver,
        ListRendererInterface $listRenderer,
        //-----------------------------------------
        protected ConfigInterface $config,
        protected LoggerInterface $logger,
        protected EmailNotificationService $emailNotificationService,
        private PaginationService $paginationService,
        private FormatterService $formatter,
        protected BaseFeatureService $baseFeatureService
    ) {
        parent::__construct(
            $route_params,
            $flash22,
            $view,
            $httpFactory,
            $container,
            $scrap,
            //-----------------------------------------
            $featureMetadataService,
            $formFactory,
            $formHandler,
            $formType,
            $listFactory,
            $listType,
            $repository,
            $typeResolver,
            $listRenderer,
            $baseFeatureService
        );
        // constructor uses promotion php8+
    }

    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        return parent::listAction(request: $request);
        // $viewData = [
        //     'title' => 'Testy Index Action',
        //     'actionLinks' => $this->getReturnActionLinks(),
        // ];

        // return $this->view(Url::CORE_TESTY->view(), $this->buildCommonViewData($viewData));
    }



    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function listAction(ServerRequestInterface $request): ResponseInterface
    {

        // $rrr = $this->scrap();
        return parent::listAction(request: $request);
    }



    /**
     * Reusable getReturnActionLinks for all actions
     *
     * @return array
     */
    public function getReturnActionLinks(): array
    {
        $rrr = Url::CORE_POST_EDIT->url(['id' => 22]);
        return $this->getActionLinks(
            Url::CORE_TESTY,
            Url::CORE_TESTY_LIST,
            Url::CORE_TESTY_CREATE,
            Url::CORE_TESTY_EDIT,
            Url::CORE_TESTY_PLACEHOLDER,
            Url::CORE_TESTY_TESTLOGGER,
            Url::CORE_TESTY_TESTFORMATTER,
            Url::CORE_TESTY_TESTSESSION,
            Url::CORE_TESTY_TESTDATABASE,
            Url::CORE_TESTY_EMAILTEST,
            Url::CORE_TESTY_LINKDEMO,
            Url::CORE_TESTY_PAGINATION_TEST
        );
    }


    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
        // $url = $this->feature->baseUrlEnum;
        // $url = Url::CORE_TESTY;
        // $rrr = $url->action();
        // $rrr = $url->getSection('CORE');
        // $rrr = $url->url();
        // $rrr = $this->scrap();
        // $rrr = $url->action();
        // $rrr = $url->action();
        // $rrr = $url->action();

        return parent::editAction(request: $request);
    }

    protected function overrideFormTypeRenderOptions(): void
    {
        /*
        $options = [
            // 'ip_address' => $this->getIpAddress(),
            // 'boo' => 'boo',
            'render_options' => [
                'error_display' => 'summary', // 'summary, inline'
                'layout_type'   => 'fieldsets', // fieldsets / sections / sequential
                // 'submit_text'   => "add fook",
                'form_fields'   => [
                    'content', 'title', 'generic_text',
                ],
                'layout'        => [
                    [
                        'title' => 'Your Title',
                        'fields' => ['title', 'content'],
                        'divider' => true
                    ],
                    [
                        'title' => 'Your Favorite',
                        'fields' => ['generic_text'],
                        'divider' => true,
                    ],
                ],
            ]
        ];
        ***********/
        //$this->formType->overrideConfig(options: $options ?? []);
    }


    /**
     * Handles updating a resource via an AJAX request.
     * Responds to POST /testy/edit/{id}/update
     *
     * @param ServerRequestInterface $request The incoming server request.
     * @return ResponseInterface The JSON response.
     */
    public function updateAction(ServerRequestInterface $request): ResponseInterface
    {
        return parent::updateAction(request: $request);
    }


    /**
     * Show the posts form
     */
    public function createAction(ServerRequestInterface $request): ResponseInterface
    {
        // Get the current user ID - We use a trait
        // $userId = $this->getCurrentUserId();
        return parent::createAction(request: $request);

        // // Prepare view data - pass the form directly instead of FormView
        // $viewData = [
        //     'title' => 'Create New Testy',
        //     'actionLinks' => $this->getReturnActionLinks(),
        // ];

        // return $this->view(Url::CORE_TESTY_CREATE->view(), $viewData);
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

        $viewData = [
            'title' => 'Testy Logger Action',
            'actionLinks' => $this->getReturnActionLinks(),
            'additional_content' => $content
        ];

        return $this->view(Url::CORE_TESTY_TESTLOGGER->view(), $this->buildCommonViewData($viewData));
    }
    public function testformatterAction(): ResponseInterface
    {
        $content = "<h1>Logger String Interpolation Tests</h1>";
        //$content .= Debug::p($this->route_params, 0, true);  // Return output instead of echo


        // Assume TextFormatter is instantiated, e.g., via DI
        $textFormatter = new TextFormatter();


        // Basic usage
        $title = '<hr><h3>Basic usage</h3>';
        $data = 'hello world';
        $content .= "$title<b>transform </b> Does nothing<br />";
        $content .= "<b>Data: </b> $data <br />";
        $content .= "<b>Output: </b>";
        $content .= $textFormatter->transform($data); // Output: hello world

        // With null value
        $title = '<br /><hr><h3>With null value</h3>';
        $v = "['null_value' => 'No Content']";
        $data = null;
        $content .= "$title<b>transform {$v}</b><br />";
        $content .= "<b>Data: </b> $data <br />";
        $content .= "<b>Output: </b>";
        $content .= $textFormatter->transform($data, ['null_value' => 'No Content']); // Output: No Content

        // Truncation
        $title = '<br /><hr><h3>Truncation</h3>';
        $v = "['max_length' => 20]";
        $data = 'This is a very long text that needs to be truncated.';
        $content .= "$title<b>transform {$v}</b><br />";
        $content .= "<b>Data: </b> $data <br />";
        $content .= "<b>Output: </b>";
        $content .= $textFormatter->transform($data, ['max_length' => 20]);
        // Output: This is a very lon...



        // Truncation with custom suffix
        $title = '<br /><hr><h3>Truncation with custom suffix</h3>';
        $v = "['max_length' => 10, 'truncate_suffix' => '...read more']";
        $data = 'Another long sentence.';
        $content .= "$title<b>transform {$v}</b><br />";
        $content .= "<b>Data: </b> $data <br />";
        $content .= "<b>Output: </b>";
        $content .= $textFormatter->transform($data, ['max_length' => 10, 'truncate_suffix' => '...read more']);
        // Output: Another lo...read more

        // Uppercase transformation
        $title = '<br /><hr><h3>Uppercase transformation</h3>';
        $v = " ['transform' => 'uppercase']";
        $data = 'hello world';
        $content .= "$title<b>transform {$v}</b><br />";
        $content .= "<b>Data: </b> $data <br />";
        $content .= "<b>Output: </b>";
        $content .= $textFormatter->transform($data, ['transform' => 'uppercase']); // Output: HELLO WORLD

        // Lowercase transformation
        $title = '<br /><hr><h3>Lowercase transformation</h3>';
        $v = " ['transform' => 'lowercase']";
        $data = 'HELLO world';
        $content .= "$title<b>transform {$v}</b><br />";
        $content .= "<b>Data: </b> $data <br />";
        $content .= "<b>Output: </b>";
        $content .= $textFormatter->transform($data, ['transform' => 'lowercase']); // Output: hello world

        // Capitalize transformation
        $title = '<br /><hr><h3>Capitalize transformation</h3>';
        $v = " ['transform' => 'capitalize']";
        $data = 'hello world, is is a nice day';
        $content .= "$title<b>transform {$v}</b><br />";
        $content .= "<b>Data: </b> $data <br />";
        $content .= "<b>Output: </b>";
        $content .= $textFormatter->transform($data, ['transform' => 'capitalize']);
        // Output: Hello world, is is a nice day


        // Title case transformation
        $title = '<br /><hr></hr><h3>Title case transformation</h3>';
        $v = "['transform' => 'title']";
        $data = 'this is a title';
        $content .= "$title<b>transform {$v}</b><br />";
        $content .= "<b>Data: </b> $data <br />";
        $content .= "<b>Output: </b>";
        $content .= $textFormatter->transform($data, ['transform' => 'title']); // Output: This Is A Title

        // last2char_upper transformation
        $title = '<br /><hr><h3>last2char_upper transformation</h3>';
        $v = " ['transform' => 'last2char_upper']";
        $data = 'hello world, is is a nice day';
        $content .= "$title<b>transform {$v}</b><br />";
        $content .= "<b>Data: </b> $data <br />";
        $content .= "<b>Output: </b>";
        $content .= $textFormatter->transform($data, ['transform' => 'last2char_upper']);
        // Output: Hello world, is is a nice dAY


        // Custom suffix
        $title = '<br /><hr><h3>Custom suffix</h3>';
        $v = "['suffix' => ': $100']";
        $data = 'Price';
        $content .= "$title<b>transform {$v}</b><br />";
        $content .= "<b>Data: </b> $data <br />";
        $content .= "<b>Output: </b>";
        $content .= $textFormatter->transform($data, ['suffix' => ': $100']); // Output: Price: $100

        // Combination (max_length, transform, suffix)
        $title = '<br /><hr><h3>Combination (max_length, transform, suffix)</h3>';
        $v = "<pre>
            [
                'max_length' => 15,
                'truncate_suffix' => '...',
                'transform' => 'uppercase',
                'suffix' => ' (CONFIDENTIAL)'
            ]</pre>";
        $data = "super important secret message";
        $content .= "$title<b>transform {$v}</b>";
        $content .= "<b>Data: </b> $data <br />";
        $content .= "<b>Output: </b>";
        $content .= $textFormatter->transform(
            $data,
            [
                'max_length' => 15,
                'truncate_suffix' => '...',
                'transform' => 'uppercase',
                'suffix' => ' (CONFIDENTIAL)'
            ]
        );
        // Output: SUPER IMPOR... (CONFIDENTIAL)

        // $title = '<br /><hr><h3>xxx</h3>';
        // $v = "xxx";
        // $data = 'xxx';
        // $content .= "$title<b>transform {$v}</b><br />";
        // $content .= "<b>Data: </b> $data <br />";
        // $content .= "<b>Output: </b>";


        $content .= '<hr>';
        $viewData = [
            'title' => 'Testy Logger Action',
            'actionLinks' => $this->getReturnActionLinks(),
            'additional_content' => $content
        ];

        return $this->view(Url::CORE_TESTY_TESTFORMATTER->view(), $this->buildCommonViewData($viewData));
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
        $this->flash22->add("You've visited this page $visits times", $messageType);

        // Return view
        $viewData = [
            'title' => 'Session Test',
            'actionLinks' => $this->getReturnActionLinks(),
            'visits' => $visits,
            'sessionData' => $this->session->all() // Show all session data
        ];

        return $this->view(Url::CORE_TESTY_TESTSESSION->view(), $this->buildCommonViewData($viewData));
    }


    // Test action to clear session
    // public function resetSessionAction(): void
    public function resetSessionAction(): ResponseInterface
    {
        // Clear visit counter
        $this->session->remove('visit_count');

        // Add flash message
        $this->flash22->add("Session counter reset", FlashMessageType::Success);

        // Redirect back to session test
        return $this->redirect(Url::CORE_TESTY_TESTSESSION->url());
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
            $viewData = [
                'title' => 'Database Test',
                'actionLinks' => $this->getReturnActionLinks(),
                'connectionStatus' => $connectionTest[0]['message'],
                'insertId' => $insertId,
                'records' => $records,
                'transactionResult' => $transactionResult,
                'error' => null
            ];

            return $this->view(Url::CORE_TESTY_TESTDATABASE->view(), $this->buildCommonViewData($viewData));
        } catch (\Throwable $e) {
            // Handle errors gracefully
            $viewData = [
                'title' => 'Database Test',
                'actionLinks' => $this->getReturnActionLinks(),
                'connectionStatus' => 'Failed',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];

            return $this->view(Url::CORE_TESTY_TESTDATABASE->view(), $this->buildCommonViewData($viewData));
        }


        // // Return view
        // return $this->view(TestyConst::VIEW_TESTY_TESTDATABASE, [
        //     'title' => 'Database Test Action',
        // ]);
    }


    /**
     * Show .....
     *
     * @param ServerRequestInterface $request The current request
     * @return ResponseInterface
     */
    public function placeHolderAction(ServerRequestInterface $request): ResponseInterface
    {
        $viewData = [
            'title' => 'Placeholder Action Page',
            'actionLinks' => $this->getReturnActionLinks(),
        ];

        return $this->view(Url::CORE_TESTY_PLACEHOLDER->view(), $this->buildCommonViewData($viewData));
    }



    /**
     * Show .....
     *
     * @param ServerRequestInterface $request The current request
     * @return ResponseInterface
     */
    public function linkdemoAction(ServerRequestInterface $request): ResponseInterface
    {
        $linkDataFlash = Url::CORE_TESTY_LINKDEMO->toLinkData([], 'I am a link in flash');

        $message = "a link embedded in flash message: ";
        $this->flash22->add(
            $message,
            FlashMessageType::Warning,
            false,
            $linkDataFlash
        );

        $message = "flash message stack. this one has no embedded linked ";
        $this->flash22->add(
            $message,
            FlashMessageType::Warning
        );


        // 1. Get the base link data from the enum
        $linkData1 = Url::CORE_ALBUMS->toLinkData([]);
        $linkData2 = Url::STORE_ALBUMS->toLinkData([]);
        $linkData5 = Url::STORE_ALBUMS->toLinkData([]);
        $linkDataButton = Url::STORE_ALBUMS->toLinkData([]);

        $viewData = [
            'title' => 'LinkDemo Action Page',
            'actionLinks' => $this->getReturnActionLinks(),
            'linkData1' => $linkData1,
            'linkData2' => $linkData2,
            'linkData5' => $linkData5,
            'linkDataButton' => $linkDataButton,
        ];

        return $this->view(Url::CORE_TESTY_LINKDEMO->view(), $this->buildCommonViewData($viewData));
    }


    public function paginationTestAction(ServerRequestInterface $request): ResponseInterface
    {
        $currentPage = (int)($request->getQueryParams()['page'] ?? 1);
        $totalPages = 12; // Mock data1
        // Test regular pagination
        $paginationData = $this->paginationService->getPaginationData(
            Url::CORE_TESTY_PAGINATION_TEST,
            $currentPage,
            $totalPages
        );

        // Test windowed pagination
        $windowedData = $this->paginationService->getPaginationDataWithWindow(
            Url::CORE_TESTY_PAGINATION_TEST,
            $currentPage,
            $totalPages,
            2
        );

        $viewData = [
            'title' => 'Pagination Test',
            'actionLinks' => $this->getReturnActionLinks(),
            'paginationData' => $paginationData,
            'windowedData' => $windowedData,
            'currentPage' => $currentPage
        ];

        return $this->view(Url::CORE_TESTY_PAGINATION_TEST->view(), $this->buildCommonViewData($viewData));
    }


    /**
     * Show .....
     *
     * @param ServerRequestInterface $request The current request
     * @return ResponseInterface
     */
    public function emailTestAction(ServerRequestInterface $request): ResponseInterface
    {
        $emailResult = null;
        $sent = false;

        // Get CSRF token from middleware
        $csrfToken = $request->getAttribute('csrf')->generate();

        // Check if form was submitted
        if ($request->getMethod() === 'POST') {
            // Form was submitted, send the email
            $emailResult = $this->testEmail();
            $sent = true;
        }

        // Return view with result information
        $viewData = [
            'title' => 'Email Test Results',
            'actionLinks' => $this->getReturnActionLinks(),
            'result' => $emailResult,
            'sent' => $sent,
            'timestamp' => date('Y-m-d H:i:s'),
            'csrf_token' => $csrfToken
        ];

        return $this->view(Url::CORE_TESTY_EMAILTEST->view(), $this->buildCommonViewData($viewData));
    }


    // Example in a controller (for testing)
    public function testEmail(): array
    {
        // Create a mock user for testing
        $user = new \App\Entities\User();
        $user->setEmail('lixoten@gmail.com');
        $user->setUsername('testuser');

        // Generate activation token with 24 hour expiry
        $token = $user->generateActivationToken(24);

        // Send the email with the notification service
        $result = $this->emailNotificationService->sendVerificationEmail($user, $token);
        //$result = null;
        //Debug::p($result);
        if ($result) {
            return [
                'success' => true,
                'message' => 'Email sent successfully',
                'recipient' => 'Lixo Ten <lixoten@gmail.com>',
                'template' => 'Auth/verification_email'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to send email',
                'error' => $this->emailNotificationService->getLastError()
                    ?? 'Error sending email via notification service',
                'recipient' => 'Lixo Ten <lixoten@gmail.com>',
                'template' => 'Auth/verification_email'
            ];
            //Debug::p(111);
        }
    }
}
## 697 764 804 403 372
