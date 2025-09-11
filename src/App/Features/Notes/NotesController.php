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

namespace App\Features\Notes;

use App\Helpers\DebugRt;
use App\Enums\FlashMessageType;
use App\Enums\PostFields2;
use App\Enums\Url;
use App\Features\Posts\Form\PostsFormType;
use App\Features\Posts\List\PostsListType;
use App\Repository\PostRepositoryInterface;
use App\Services\Email\EmailNotificationService;
use App\Services\Interfaces\EmailServiceInterface;
use Core\Controller;
use App\Services\Interfaces\FlashMessageServiceInterface;
use App\Services\PaginationService;
use Core\Constants\Consts;
use Core\Context\CurrentContext;
use Core\Enum\SortDirection;
use Core\Services\ConfigService;
use stdClass;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Core\Form\FormFactoryInterface;
use Core\Form\FormHandlerInterface;
use Core\List\ListFactory;
use Core\Logger;

/**
 * Testy controller
 *
 */
class NotesController extends Controller
{
    protected ConfigService $config;
    protected ?ServerRequestInterface $request = null; // Declare correctly with proper type

    //protected FormFactoryInterface $formFactory;
    //protected FormHandlerInterface $formHandler;
    protected Logger $logger;
    protected EmailNotificationService $emailNotificationService;
    private PaginationService $paginationService;

    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        ConfigService $config,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        CurrentContext $scrap,
        /////////////////////////////////
        private FormFactoryInterface $formFactory,
        private FormHandlerInterface $formHandler,
        private PostRepositoryInterface $repository,
        private PostsFormType $formType,
        private ListFactory $listFactory,
        private PostsListType $listType
        // Logger $logger,
        // EmailNotificationService $emailNotificationService,
        // PaginationService $paginationService,
    ) {
        parent::__construct(
            $route_params,
            $flash,
            $view,
            $httpFactory,
            $container,
            $scrap
        );
        // $this->config = $config;
        // $this->formFactory = $formFactory;
        // $this->formHandler = $formHandler;
        // $this->logger = $logger;
        // $this->emailNotificationService = $emailNotificationService;
        // $this->paginationService = $paginationService;

        $this->formFactory = $formFactory;
        $this->formHandler = $formHandler;
        $this->repository = $repository;
        $this->formType = $formType;
        $this->listFactory = $listFactory;
        $this->listType = $listType;
        $this->listType->routeType = 'store';// hack $scrap->getRouteType();


    }


    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        $filter = (string)($request->getQueryParams()['filter'] ?? "DDDD");

        $this->scrap->setRouteType('store');
        $storeId = 4;// $this->scrap->getStoreId();

        $routeType = $this->scrap->getRouteType();
        if ($routeType === 'account') {
            $filter = 'user';
            $url = Url::ACCOUNT_POSTS;
        } elseif ($routeType === 'store') {
            $filter = 'store';
            $url = Url::STORE_POSTS;
        } else {
            $filter = 'user';
            $url = Url::CORE_POSTS;
        }

        $configService = $this->container->get('config');

        $view = 'posts_index';
        $configOptions = $configService->get('view_options/' . $view) ?? ['a']; // loads "list_fields/posts.php"

        // $metadatatest = PostFields2::ID->getMetadata()['label'];
        // $metadatatest2 = PostFields2::ID->label();
        // $rr = "PostFields2";
        // $rr = "\\App\\Enums\\PostFields2";
        // $metadatatet2 = $rr::ID->label();


        $options            = $configOptions['options'] ?? [];
        $renderOptions      = $configOptions['render_options'] ?? [];
        $paginationOptions  = $configOptions['pagination'] ?? [];
        $paginationOptions  = $configOptions['pagination'] ?? [];
        $listColumns        = $configOptions['list_columns'] ?? [];

        $listColumns = ['id', 'title', 'ffffff', 'ccc', 'created_at'];


        $sortField = $options['default_sort_key']
            ?? $this->listType->getListOptions()['default_sort_key'] ?? PostFields2::TITLE->value;
        $sortDirection = $options['default_sort_direction']
            ?? $this->listType->getListOptions()['default_sort_direction'] ?? SortDirection::ASC->value;

        // Get the record with pagination
        $page = isset($this->route_params['page']) ? (int)$this->route_params['page'] : 1;
        $limit = $paginationOptions['per_page']
            ?? $this->listType->getListPaginationOptions()['per_page'] ?? 2;
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

        // Map entities to simple arrays for view
        // $dataRecords = array_map(
        //     fn($record) => $this->repository->toArray($record, $listColumns),
        //     $records
        // );
        $cols = !empty($listColumns) ? $listColumns : $this->listType->getListColumns();
        if (!empty($listColumns)) {
            $cols = $listColumns;
        } else {
            $cols = $this->listType->getListColumns();
        }

        // we need to update listType when incoming Col from controller
        $this->listType->setListColumns($cols);
        // $validColumns = $this->fieldRegistryService->filterAndValidateFields($listColumns);
        $dataRecords = array_map(
            function ($record) use ($cols) {
                return $this->repository->toArray($record, $cols);
            },
            $records
        );



        $list = $this->listFactory->create(
            listType: $this->listType,
            data: $dataRecords,
            options:  array_merge(
                $options,
                [
                    'pagination' => $paginationOptions,
                    'render_options' => $renderOptions + ['list_columns' => $listColumns],
                ]
            )
        );

        return $this->view($url->view(), [
            'title' => 'LoCAL Blog Posts',
            'postsList' => $list,
        ]);




        $viewData = [
            'title' => 'Testy Index Action',
            'actionLinks' => [],
        ];

        return $this->view(Url::CORE_TESTY->view(), $viewData);
    }

    /**
     * Edit an existing post
     */
    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
         // Get post ID from route parameters
        $postId = 14; // hack isset($this->route_params['id']) ? (int)$this->route_params['id'] : null;

        // Important!! Manual Tests.
        // $postId = 333;  // Test - Record does not exist
        // $postId = null; // Test - Route missing Record ID
        // $postId = 3;    // Test - Pointing to a Record that belongs to another User ID

        if (!$postId) {
            $this->throwPostNotFound($postId);
        }

        // Get the post from the database
        $post = $this->repository->findById($postId);

        if (!$post) {
            $this->throwPostNotFound($postId);
        }

        // hack
        //if (!$this->isUserAuthorized($post->getPostUserId())) {
        //    $this->flash->add("You don't have permission to edit this post", FlashMessageType::Error);
        //    return $this->redirect(Url::CORE_POSTS->url());
        //}
        // hack


        $configService = $this->container->get('config');

        $view = 'posts_edit';
        $configOptions = $configService->get('view_options/' . $view) ?? ['a']; // loads "list_fields/posts.php"

        $options            = $configOptions['options'] ?? [];
        $renderOptions      = $configOptions['render_options'] ?? [];
        $formFields         = $configOptions['form_fields'] ?? [];

        $formFields = [
            'title', 'content'
        ];


        $options = ['ip_address' => $this->getIpAddress()];
        $renderOptions = [
            // 'form_fields' => ['title', 'username', 'status', 'created_at'],
            // 'layout_type' => CONST_L::SECTIONS,    // FIELDSETS / SECTIONS / SEQUENTIAL
            // 'security_level' => CONST_SL::LOW,      // HIGH / MEDIUM / LOW
            // 'error_display' => CONST_ED::SUMMARY,   // SUMMARY / SUMMARY / INLINE
            // 'submit_text' => "dddddddddd",
        ];

        DebugRt::j('1', '', 'boommmmmmm');
        //$fields = !empty($formFields) ? $formFields : $this->formType->getFormFields();
        if (!empty($formFields)) {
            //$fields = $formFields;
            // we need to update listType when incoming Col from controller
            $this->formType->setFormFields($formFields);
        }
        //  else {
        //     $fields = $this->formType->getFormFields();
        // }






        // Create the form with existing post data
        $form = $this->formFactory->create(
            formType: $this->formType,
            data: [
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                // Add other fields as needed
            ],
             options:  array_merge(
                $options,
                [
                    'render_options' => $renderOptions + ['form_fields' => $formFields],
                ]
            )
            // options: [
            //     'ip_address' => $this->getIpAddress(),
            //     'render_options' => [
            //                     'list_columns' => ['title', 'username', 'status', 'created_at'],
            //         'layout_type' => CONST_L::SECTIONS,    // FIELDSETS / SECTIONS / SEQUENTIAL
            //         'security_level' => CONST_SL::LOW,      // HIGH / MEDIUM / LOW
            //         'error_display' => CONST_ED::SUMMARY,   // SUMMARY / SUMMARY / INLINE
            //                     'submit_text' => "dddddddddd",
            //     ]
            // ]
        );



        $formTheme = $form->getCssFormThemeFile();

        // Process form submission
        $formHandled = $this->formHandler->handle($form, $request);
        if ($formHandled && $form->isValid()) {
            $data = $form->getData();

            $post->setTitle($data['title']);
            $post->setContent($data['content']);
            $post->setSlug($this->generateSlug($data['title']));
            // Don't update user_id as this would change ownership

            // Update the post in the database
            $success = $this->repository->update($post);
            // $success = false;
            // throw new \PDOException();
            // throw new \Exception();

            if ($success) {
                $this->flash->add("Post updated successfully", FlashMessageType::Success);
                return $this->redirect(Url::CORE_POSTS->url());
            } else {
                $form->addError('_form', 'Failed to update your post. Please try again.');
            }
        }

        // Prepare view data
        $viewData = [
            'title' => 'Edit Post',
            'post' => $post,
            'form' => $form,
            'formTheme' => $formTheme
        ];

        // Create response with appropriate status code
        $response = $this->view(Url::CORE_POSTS_EDIT->view(), $viewData);

        // Set 422 Unprocessable Entity status for form failures
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;

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
    }}
## 403 372
