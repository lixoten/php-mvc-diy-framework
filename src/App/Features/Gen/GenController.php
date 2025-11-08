<?php

/**
 * PostController.php
 *
 * This file contains the PostController class, which handles various actions
 * such as logging, session management, database testing, and email testing.
 * It is part of the Post feature in the application.
 *
 * @package App\Features\Gen
 */

declare(strict_types=1);

namespace App\Features\Gen;

use Core\Services\FormatterService;
use App\Helpers\DebugRt;
use App\Enums\FlashMessageType;
use App\Enums\PostFields2;
use App\Enums\Url;
use App\Features\Gen\List\PostListType;
use App\Features\Gen\Form\GenFormType;
use App\Repository\PostRepositoryInterface;
use App\Services\Email\EmailNotificationService;
use App\Services\FeatureMetadataService;
use App\Services\Interfaces\FlashMessageServiceInterface;
use App\Services\Interfaces\GenericDataServiceInterface;
use App\Services\PaginationService;
use Core\AbstractCrudController;
use Core\AbstractGenCrudController;
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
use Core\Formatters\PhoneNumberFormatter;
use Core\Interfaces\ConfigInterface;
use Core\List\ListFactory;
use Core\List\ListFactoryInterface;
use Core\List\ListTypeInterface;
use Core\Logger;
use Core\Repository\BaseRepositoryInterface;
use Core\Services\TypeResolverService;
use Psr\Log\LoggerInterface;

/**
 * Post controller
 *
 */
class GenController extends AbstractGenCrudController
{
    protected ConfigService $config;
    protected ?ServerRequestInterface $request = null; // Declare correctly with proper type

    // private FormFactoryInterface $formFactory;
    // private FormHandlerInterface $formHandler;
    // private PostRepositoryInterface $repository;
    // private PostFormType $formType;//dangerdanger
    // private ListFactory $listFactory;
    // private PostListType $listType;

    // protected Logger $logger;
    // protected EmailNotificationService $emailNotificationService;
    // private PaginationService $paginationService;
    protected FormTypeInterface $formType; // Change from PostFormType to FormTypeInterface //dangerdanger
    //protected BaseRepositoryInterface $repository; // Change from PostRepositoryInterface to BaseRepositoryInterface

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
        // GenFormType $formType,
        FormTypeInterface $formType, // Change to interface//dangerdanger
        protected GenericDataServiceInterface $dataService,
        PostRepositoryInterface $repository,
        protected TypeResolverService $typeResolver,
        //-----------------------------------------
        ConfigInterface $config,
        private ListFactoryInterface $listFactory,
        private ListTypeInterface $listType,
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
            $dataService,
            $repository,
            $typeResolver,
        );
        $this->config = $config;
        // $this->formFactory = $formFactory;
        // $this->formHandler = $formHandler;
        // $this->repository = $repository;
        $this->formType = $formType;
        $this->listFactory = $listFactory;
        $this->listType = $listType;
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
            'title' => 'Post Index Action',
            'actionLinks' => [],
        ];

        return $this->view(Url::CORE_POST->view(), $viewData);
    }


    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function listAction(ServerRequestInterface $request): ResponseInterface
    {
        // $this->flash22->add("hi there", FlashMessageType::Info);
        // $this->logger->alert("test123");


        $tmpEnum = $this->feature->baseUrlEnum;
        $pageName       = $this->scrap->getPageName();
        $pageFeature    = $this->scrap->getPageFeature();
        $pageEntity     = $this->scrap->getPageEntity();

        $tmp = $tmpEnum->data()['view'];
        $xpl = explode('/', $pageName);
        // $pageName   = $xpl[0] . '_' . $xpl[1];
        $entityNm = $xpl[0];

        $this->listType->setFocus(
            $pageName,
            $pageFeature,
            $pageEntity,
            $entityNm
        );





        // Get the page key from route params
        // $entityType = $this->route_params['page_name'] ?? null;
        $entityType = $this->scrap->getPageKey(); // Use context

        if ($entityType === null) {
            throw new \RuntimeException("Entity type (page_name) not provided in route parameters.");
        }










        $options            = $this->listType->getOptions() ?? [];
        $renderOptions      = [];
        $paginationOptions  = $this->listType->getPaginationOptions() ?? [];
        $listFields         = $this->listType->getFields() ?? [];


        $filter = (string)($request->getQueryParams()['filter'] ?? "DDDD");

        $this->scrap->setRouteType('store');
        // $storeId = 4;// $this->scrap->getStoreId();//fixme

        $routeType = $this->scrap->getRouteType();
        if ($routeType === 'account') {
            $filter = 'user';
            $url = Url::ACCOUNT_POST;
        } elseif ($routeType === 'store') {
            $filter = 'store';
            $url = Url::STORE_POST;
        } else {
            $filter = 'user';
            $url = Url::CORE_POST;
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

        $cols = !empty($listFields) ? $listFields : $this->listType->getFields();
        if (!empty($listFields)) {
            $cols = $listFields;
            $cols = $this->listType->validateFields($cols);
            // we need to update listType when incoming Col from controller
            // $this->listType->setFields($cols);
        }
        // else {
        //     $cols = $this->listType->getFields();
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
            'title' => 'LoCAL Blog Post',
            'list' => $list,
        ]);


        $viewData = [
            'title' => 'Post Index Action',
            'actionLinks' => [],
        ];

        return $this->view(Url::CORE_POST->view(), $viewData);
    }



    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
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
     * Responds to POST /post/edit/{id}/update
     *
     * @param ServerRequestInterface $request The incoming server request.
     * @return ResponseInterface The JSON response.
     */
    public function updateAction(ServerRequestInterface $request): ResponseInterface
    {
        return parent::updateAction(request: $request);
    }


    /**
     * Show the post form
     */
    public function createAction(ServerRequestInterface $request): ResponseInterface
    {
        // Get the current user ID - We use a trait
        // $userId = $this->getCurrentUserId();
        return parent::createAction(request: $request);
    }
}
## 697 764 804 403 372
