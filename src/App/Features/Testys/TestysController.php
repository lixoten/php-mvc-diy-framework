<?php

/**
 * TestysController.php
 *
 * This file contains the TestysController class, which handles various actions
 * such as logging, session management, database testing, and email testing.
 * It is part of the Testy feature in the application.
 *
 * @package App\Features\Testys
 */

declare(strict_types=1);

namespace App\Features\Testys;

use App\Helpers\DebugRt;
use App\Enums\FlashMessageType;
use App\Enums\PostFields2;
use App\Enums\Url;
use App\Features\Testys\Form\TestysFormType;
use App\Features\Testys\List\TestysListType;
use App\Repository\TestyRepositoryInterface;
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
use Core\Interfaces\ConfigInterface;
use Core\List\ListFactory;
use Core\List\ListFactoryInterface;
use Core\Logger;
use Psr\Log\LoggerInterface;

/**
 * Testys controller
 *
 */
class TestysController extends AbstractCrudController
{
    protected ConfigService $config;
    protected ?ServerRequestInterface $request = null; // Declare correctly with proper type

    // private FormFactoryInterface $formFactory;
    // private FormHandlerInterface $formHandler;
    // private TestyRepositoryInterface $repository;
    // private TestysFormType $formType;
    // private ListFactory $listFactory;
    // private TestysListType $listType;

    // protected Logger $logger;
    // protected EmailNotificationService $emailNotificationService;
    // private PaginationService $paginationService;

    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash22,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        CurrentContext $scrap,
        /////////////////////////////////
        ConfigInterface $config,
        protected FormFactoryInterface $formFactory,
        protected FormHandlerInterface $formHandler,
        protected TestyRepositoryInterface $repository,
        protected TestysFormType $formType,
        private ListFactoryInterface $listFactory,
        private TestysListType $listType,
        protected LoggerInterface $logger,
        protected EmailNotificationService $emailNotificationService,
        private PaginationService $paginationService,
        private FeatureMetadataService $featureMetadataService,
    ) {
        parent::__construct(
            $route_params,
            $flash22,
            $view,
            $httpFactory,
            $container,
            $scrap,
            $featureMetadataService,
            $formFactory,
            $formHandler,
            $formType,
            $repository
        );
        $this->config = $config;
        // $this->formFactory = $formFactory;
        // $this->formHandler = $formHandler;
        // $this->repository = $repository;
        // $this->formType = $formType;
        $this->listFactory = $listFactory;
        $this->listType = $listType;
        $this->listType->routeType = $scrap->getRouteType();
        $this->logger = $logger;
        $this->emailNotificationService = $emailNotificationService;
        $this->paginationService = $paginationService;
    }

    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function indexAction(): ResponseInterface
    {
        $viewData = [
            'title' => 'Testy Index Action',
            'actionLinks' => $this->getReturnActionLinks(),
        ];

        return $this->view(Url::CORE_TESTY->view(), $viewData);
    }



    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function listAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->flash22->add("hi there", FlashMessageType::Info);
        $this->logger->alert("test123");

        $options            = $this->listType->getOptions() ?? [];
        $renderOptions      = [];
        $paginationOptions  = $this->listType->getPaginationOptions() ?? [];
        $listFields         = $this->listType->getListFields() ?? [];


        $filter = (string)($request->getQueryParams()['filter'] ?? "DDDD");

        $this->scrap->setRouteType('store');
        $storeId = 4;// $this->scrap->getStoreId();//fixme

        $routeType = $this->scrap->getRouteType();
        if ($routeType === 'account') {
            $filter = 'user';
            $url = Url::ACCOUNT_TESTYS;
        } elseif ($routeType === 'store') {
            $filter = 'store';
            $url = Url::STORE_TESTYS;
        } else {
            $filter = 'user';
            $url = Url::CORE_TESTYS;
        }

        $sortField = $options['default_sort_key']
            ?? $this->listType->getOptions()['default_sort_key'] ?? PostFields2::TITLE->value;
        $sortDirection = $options['default_sort_direction']
            ?? $this->listType->getOptions()['default_sort_direction'] ?? SortDirection::ASC->value;

        // Get the record with pagination
        $page = isset($this->route_params['page']) ? (int)$this->route_params['page'] : 1;
        $limit = $paginationOptions['per_page']
            ?? $this->listType->getPaginationOptions()['per_page'] ?? 2;
        // $limit = $paginationOptions['per_page'] ?? 20;
        $offset = ($page - 1) * $limit;

        ## todo introduce filters. At the moment we are not.
        if ($filter === "user") {
            $userId = $this->scrap->getUserId();
            $records = $this->repository->findByUserId($userId, [$sortField => $sortDirection], $limit, $offset);

            $totalRecords = $this->repository->countByUserId($userId);
        } else {
            $records = $this->repository->findByStoreId($storeId, [$sortField => $sortDirection], $limit, $offset);
            $totalRecords = $this->repository->countByStoreId($storeId);
        }

        $totalPages = ceil($totalRecords / $limit);
        $paginationOptions['current_page'] = $page;
        $paginationOptions['total_pages'] = $totalPages;
        $paginationOptions['total_items'] = $totalRecords;

        $cols = !empty($listFields) ? $listFields : $this->listType->getListFields();
        if (!empty($listFields)) {
            $cols = $listFields;
            $cols = $this->listType->validateFields($cols);
            // we need to update listType when incoming Col from controller
            // $this->listType->setListFields($cols);
        }
        // else {
        //     $cols = $this->listType->getListFields();
        // }


        // Map entities to simple arrays for view
        // deleteme $validColumns = $this->fieldRegistryService->filterAndValidateFields($listColumns);
        $dataRecords = array_map(
            function ($record) use ($cols) {
                return $this->repository->toArray($record, $cols);
            },
            $records
        );

        $list = $this->listFactory->create(
            listType: $this->listType,
            data: $dataRecords,
            options: [
                'options'           => $options,
                'pagination'        => $paginationOptions,
                'render_options'    => $renderOptions,
                'list_fields'       => $listFields,
            ],
        );

        return $this->view($url->view(), [
            'title' => 'LoCAL Blog Testys',
            'list' => $list,
        ]);


        $viewData = [
            'title' => 'Testy Index Action',
            'actionLinks' => [],
        ];

        return $this->view(Url::CORE_TESTY->view(), $viewData);
    }


    /**
     * Reusable getReturnActionLinks for all actions
     *
     * @return array
     */
    public function getReturnActionLinks(): array
    {
        $rrr = Url::CORE_POSTS_EDIT->url(['id' => 22]);
        return $this->getActionLinks(
            Url::CORE_TESTY,
            Url::CORE_TESTY_CREATE,
            Url::CORE_TESTY_EDIT,
            Url::CORE_TESTY_PLACEHOLDER,
            Url::CORE_TESTY_TESTLOGGER,
            Url::CORE_TESTY_TESTSESSION,
            Url::CORE_TESTY_TESTDATABASE,
            Url::CORE_TESTY_EMAILTEST,
            Url::CORE_TESTY_LINKDEMO,
            Url::CORE_TESTY_PAGINATION_TEST
        );
    }


    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
        return parent::editAction(request: $request);
    }


    /**
     * Handles updating a resource via an AJAX request.
     * Responds to POST /testys/edit/{id}/update
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

        // Prepare view data - pass the form directly instead of FormView
        $viewData = [
            'title' => 'Create New Testy',
            'actionLinks' => $this->getReturnActionLinks(),
        ];

        return $this->view(Url::CORE_TESTY_CREATE->view(), $viewData);
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
            'title' => 'Testy testlogger Action',
            'actionLinks' => $this->getReturnActionLinks(),
            'additional_content' => $content
        ];

        return $this->view(Url::CORE_TESTY_TESTLOGGER->view(), $viewData);
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
        return $this->view(Url::CORE_TESTY_TESTSESSION->view(), [
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
            return $this->view(Url::CORE_TESTY_TESTDATABASE->view(), [
                'title' => 'Database Test',
                'connectionStatus' => $connectionTest[0]['message'],
                'insertId' => $insertId,
                'records' => $records,
                'transactionResult' => $transactionResult,
                'error' => null
            ]);
        } catch (\Throwable $e) {
            // Handle errors gracefully
            return $this->view(Url::CORE_TESTY_TESTDATABASE->view(), [
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
     * Show .....
     *
     * @param ServerRequestInterface $request The current request
     * @return ResponseInterface
     */
    public function placeHolderAction(ServerRequestInterface $request): ResponseInterface
    {
        return $this->view(Url::CORE_TESTY_PLACEHOLDER->view(), [
            'title' => 'Placeholder Action Page',
        ]);
    }


    /**
     * Show .....
     *
     * @param ServerRequestInterface $request The current request
     * @return ResponseInterface
     */
    public function linkdemoAction(ServerRequestInterface $request): ResponseInterface
    {
        $linkDataFlash = Url::CORE_TESTY_LINKDEMO->toLinkData('I am a link in flash');

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
        $linkData1 = Url::STORE_ALBUMS->toLinkData();
        $linkData2 = Url::STORE_ALBUMS_EDIT->toLinkData(
            'I am a album with rec 465 link set in Action',
            ['id' => 456]
        );
        $linkData2['attributes'] = ['class' => 'fw-bold', 'style' => 'color: green;'];
        // $linkData2 ['id' => 123],



        $linkData5 = [
          'href' => 'https://google.com',
          'text' => 'Google',
          'icon' => 'fab fa-google',
          'attributes' => [
              'style' => 'color: red;',
              'target' => '_blank' // Good practice for external links.
            ]
        ];


        // Prepare data for an indirect button link
        $linkDataButton = Url::CORE_CONTACT->toLinkData('Contact Us Indirectly');



        return $this->view(Url::CORE_TESTY_LINKDEMO->view(), [
            'title' => 'LinkDemo Action Page',
            'linkData1' => $linkData1,
            'linkData2' => $linkData2,
            'linkData5' => $linkData5,
            'linkDataButton' => $linkDataButton, // Pass the new data to the view
        ]);
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

        return $this->view(Url::CORE_TESTY_PAGINATION_TEST->view(), [
            'title' => 'Pagination Test',
            'paginationData' => $paginationData,
            'windowedData' => $windowedData,
            'currentPage' => $currentPage
        ]);
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
        return $this->view(Url::CORE_TESTY_EMAILTEST->view(), [
            'title' => 'Email Test Results',
            'result' => $emailResult,
            'sent' => $sent,
            'timestamp' => date('Y-m-d H:i:s'),
            'csrf_token' => $csrfToken
        ]);
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
