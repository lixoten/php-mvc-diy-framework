<?php

/**
 * GalleryController.php
 *
 * This file contains the GalleryController class, which handles various actions
 * such as logging, session management, database testing, and email testing.
 * It is part of the Gallery feature in the application.
 *
 * @package App\Features\Gallery
 */

declare(strict_types=1);

namespace App\Features\Gallery;

use Core\Services\FormatterService;
use App\Helpers\DebugRt;
use App\Enums\FlashMessageType;
use App\Enums\PostFields2;
use App\Enums\Url;
use App\Features\Post\Form\PostFormType;
use App\Features\Gallery\Form\GalleryFormType;
use App\Services\Email\EmailNotificationService;
use App\Services\FeatureMetadataService;
use Core\Controller;
use App\Services\Interfaces\FlashMessageServiceInterface;
use App\Services\PaginationService;
use Core\AbstractCrudController;
use Core\Context\CurrentContext;
use Core\Enum\SortDirection;
use Core\Exceptions\ForbiddenException;
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
use Core\Form\ZzzzFormType;
use Core\Formatters\PhoneNumberFormatter;
use Core\Interfaces\ConfigInterface;
use Core\List\ListFactory;
use Core\List\ListFactoryInterface;
use Core\List\ListTypeInterface;
use Core\Services\TypeResolverService;
use Psr\Log\LoggerInterface;

/**
 * Gallery controller
 *
 */
class GalleryController extends AbstractCrudController
{
    protected ConfigService $config;
    protected ?ServerRequestInterface $request = null; // Declare correctly with proper type

    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash22,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        CurrentContext $scrap,
        //-----------------------------------------
        private FeatureMetadataService $featureMetadataService,
        protected FormFactoryInterface $formFactory,
        protected FormHandlerInterface $formHandler,
        FormTypeInterface $formType,//dangerdanger // 10
        private ListFactoryInterface $listFactory,
        private ListTypeInterface $listType,
        GalleryRepositoryInterface $repository,
        protected TypeResolverService $typeResolver,
        //-----------------------------------------
        ConfigInterface $config,
        protected LoggerInterface $logger,
        protected EmailNotificationService $emailNotificationService,
        private PaginationService $paginationService,
        private FormatterService $formatter,
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
        );
        $this->config = $config;
        $this->listType->routeType = $scrap->getRouteType();
        $this->logger = $logger;
        $this->emailNotificationService = $emailNotificationService;
        $this->paginationService = $paginationService;
        $this->formatter = $formatter;
    }

    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function indexAction(): ResponseInterface
    {
        $viewData = [
            'title' => 'Gallery Index Action',
            'actionLinks' => $this->getReturnActionLinks(),
        ];

        return $this->view(Url::CORE_Gallery->view(), $this->buildCommonViewData($viewData));
    }



    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function listAction(ServerRequestInterface $request): ResponseInterface
    {


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
            Url::CORE_Gallery,
            Url::CORE_Gallery_LIST,
            Url::CORE_Gallery_CREATE,
            Url::CORE_Gallery_EDIT,
            Url::CORE_Gallery_PLACEHOLDER,
            Url::CORE_Gallery_TESTLOGGER,
            Url::CORE_Gallery_TESTSESSION,
            Url::CORE_Gallery_TESTDATABASE,
            Url::CORE_Gallery_EMAILTEST,
            Url::CORE_Gallery_LINKDEMO,
            Url::CORE_Gallery_PAGINATION_TEST
        );
    }


    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
        // $url = $this->feature->baseUrlEnum;
        // $url = Url::CORE_Gallery;
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
     * Responds to POST /Gallery/edit/{id}/update
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
        //     'title' => 'Create New Gallery',
        //     'actionLinks' => $this->getReturnActionLinks(),
        // ];

        // return $this->view(Url::CORE_Gallery_CREATE->view(), $viewData);
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
            'title' => 'Gallery Logger Action',
            'actionLinks' => $this->getReturnActionLinks(),
            'additional_content' => $content
        ];

        return $this->view(Url::CORE_Gallery_TESTLOGGER->view(), $this->buildCommonViewData($viewData));
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

        return $this->view(Url::CORE_Gallery_TESTSESSION->view(), $this->buildCommonViewData($viewData));
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
        return $this->redirect(Url::CORE_Gallery_TESTSESSION->url());
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

            return $this->view(Url::CORE_Gallery_TESTDATABASE->view(), $this->buildCommonViewData($viewData));
        } catch (\Throwable $e) {
            // Handle errors gracefully
            $viewData = [
                'title' => 'Database Test',
                'actionLinks' => $this->getReturnActionLinks(),
                'connectionStatus' => 'Failed',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];

            return $this->view(Url::CORE_Gallery_TESTDATABASE->view(), $this->buildCommonViewData($viewData));
        }


        // // Return view
        // return $this->view(GalleryConst::VIEW_Gallery_TESTDATABASE, [
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

        return $this->view(Url::CORE_Gallery_PLACEHOLDER->view(), $this->buildCommonViewData($viewData));
    }



    /**
     * Show .....
     *
     * @param ServerRequestInterface $request The current request
     * @return ResponseInterface
     */
    public function linkdemoAction(ServerRequestInterface $request): ResponseInterface
    {
        $linkDataFlash = Url::CORE_Gallery_LINKDEMO->toLinkData([], 'I am a link in flash');

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

        return $this->view(Url::CORE_Gallery_LINKDEMO->view(), $this->buildCommonViewData($viewData));
    }


    public function paginationTestAction(ServerRequestInterface $request): ResponseInterface
    {
        $currentPage = (int)($request->getQueryParams()['page'] ?? 1);
        $totalPages = 12; // Mock data1
        // Test regular pagination
        $paginationData = $this->paginationService->getPaginationData(
            Url::CORE_Gallery_PAGINATION_TEST,
            $currentPage,
            $totalPages
        );

        // Test windowed pagination
        $windowedData = $this->paginationService->getPaginationDataWithWindow(
            Url::CORE_Gallery_PAGINATION_TEST,
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

        return $this->view(Url::CORE_Gallery_PAGINATION_TEST->view(), $this->buildCommonViewData($viewData));
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

        return $this->view(Url::CORE_Gallery_EMAILTEST->view(), $this->buildCommonViewData($viewData));
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
## 697 764 804 403 372 613
