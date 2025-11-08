<?php

/**
 * UserController.php
 *
 * This file contains the UserController class, which handles various actions
 * such as logging, session management, database testing, and email testing.
 * It is part of the User feature in the application.
 *
 * @package App\Features\User
 */

declare(strict_types=1);

namespace App\Features\User;

use App\Enums\FlashMessageType;
use Core\Services\FormatterService;
use App\Enums\Url;
use App\Services\Email\EmailNotificationService;
use App\Services\FeatureMetadataService;
use App\Services\Interfaces\FlashMessageServiceInterface;
use App\Services\PaginationService;
// use App\Services\UserService;
use Core\AbstractCrudController;
use Core\Context\CurrentContext;
use Core\Services\ConfigService;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Core\Form\FormFactoryInterface;
use Core\Form\FormHandlerInterface;
use Core\Form\FormTypeInterface;
use Core\Interfaces\ConfigInterface;
use Core\List\ListFactoryInterface;
use Core\List\ListTypeInterface;
use Core\Services\TypeResolverService;
use Psr\Log\LoggerInterface;

/**
 * User controller
 *
 */
class UserController extends AbstractCrudController
{
    protected ConfigService $config;
    protected ?ServerRequestInterface $request = null; // Declare correctly with proper type
    private UserService $userService;

    // private FormFactoryInterface $formFactory;
    // private FormHandlerInterface $formHandler;
    // private UserRepositoryInterface $repository;
    // private UserFormType $formType;
    // private ListFactory $listFactory;
    // private UserListType $listType;

    // protected Logger $logger;
    // protected EmailNotificationService $emailNotificationService;
    // private PaginationService $paginationService;
    //protected FormTypeInterface $formType; // Change from UserFormType to FormTypeInterface
    //protected BaseRepositoryInterface $repository; // Change from UserRepositoryInterface to BaseRepositoryInterface

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
        UserRepositoryInterface $repository,
        protected TypeResolverService $typeResolver,
        //-----------------------------------------
        ConfigInterface $config,
        protected LoggerInterface $logger,
        protected EmailNotificationService $emailNotificationService,
        private PaginationService $paginationService,
        private FormatterService $formatter,
        UserService $userService
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
        // $this->formFactory = $formFactory;
        // $this->formHandler = $formHandler;
        // $this->repository = $repository;
        // $this->formType = $formType;
        // $this->listFactory = $listFactory;
        // $this->listType = $listType;
        $this->listType->routeType = $scrap->getRouteType();
        $this->logger = $logger;
        $this->emailNotificationService = $emailNotificationService;
        $this->paginationService = $paginationService;
        $this->formatter = $formatter;
        $this->userService = $userService;
    }

    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function indexAction(): ResponseInterface
    {
        $viewData = [
            'title' => 'User Index Action',
            'actionLinks' => $this->getReturnActionLinks(),
        ];

        return $this->view(Url::CORE_USER->view(), $this->buildCommonViewData($viewData));
    }



    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function listAction(ServerRequestInterface $request): ResponseInterface
    {
        // return parent::listAction(request: $request);

        // $debugBar = $this->getDebugBar();

        // $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        // $navData = $this->getNavigationData($currentPath);



        //var_dump($request);
        // $this->formType = $this->listType;
        //$tmpEnum = $this->feature->baseUrlEnum;
        $pageName       = $this->scrap->getPageName();
        $pageFeature    = $this->scrap->getPageFeature();
        $pageEntity     = $this->scrap->getPageEntity();

        // $tmp = $tmpEnum->data()['view'];
        $xpl = explode('_', $pageName);
        // $pageNm   = $xpl[0] . '_' . $xpl[1];
        $entityNm = $xpl[0]; // hack dangerdanger - i do not like how i get table name

        $this->listType->setFocus(
            $pageName,
            $pageFeature,
            $pageEntity,
            $entityNm
        );




        $options            = $this->listType->getOptions() ?? [];
        $renderOptions      = [];
        $paginationOptions  = $this->listType->getPaginationOptions() ?? [];
        $listFields         = $this->listType->getFields() ?? [];







        $filter = (string)($request->getQueryParams()['filter'] ?? "DDDD");

        // $this->scrap->setRouteType('store');
        // $storeId = $this->scrap->getStoreId();


        //$EE = $this->feature->baseUrlEnum;

        $url = $this->feature->listUrlEnum;


        $routeType = $this->scrap->getRouteType();
        // if ($routeType === 'account') {
        //     $filter = 'user';
        //     $url = Url::ACCOUNT_TESTY;
        // } elseif ($routeType === 'store') {
        //     $filter = 'store';
        //     $url = Url::STORE_TESTY;
        // } else {
        //     $filter = 'user';
        //     $url = Url::CORE_TESTY;
        // }

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
        // if ($filter === "user") {
        if ($this->scrap->getRouteType() === "store") {
            // $userId = $this->scrap->getUserId();
            // $records = $this->repository->findByUserId($userId,
            //                                              [$sortField => $sortDirection], $limit, $offset);
            $storeId = 1;
            $records = $this->repository->findByStoreIdWithFields(
                $storeId,
                $listFields,
                [$sortField => $sortDirection],
                $limit,
                $offset
            );

            $totalRecords = $this->repository->countByStoreId($storeId);
        } else {
            //$records = $this->repository->findByStoreId($storeId, [$sortField => $sortDirection],
            // $limit, $offset);

            //help me here??????????????????????????????
            $records = $this->userService->getAllUsersWithFields(
                $listFields,
                $sortField,
                $sortDirection,
                $limit,
                $offset
            );

            $totalRecords = $this->userService->countAllUsers();
        }

        $totalPages = ceil($totalRecords / $limit);
        $paginationOptions['current_page'] = $page;
        $paginationOptions['total_pages'] = $totalPages;
        $paginationOptions['total_items'] = $totalRecords;

        // $cols = !empty($listFields) ? $listFields : $this->listType->getFields();
        // if (!empty($listFields)) {
        //     $cols = $listFields;
        //     $cols = $this->listType->validateFields($cols);
        //     // we need to update listType when incoming Col from controller
        //     // $this->listType->setFields($cols);
        // }
        // else {
        //     $cols = $this->listType->getFields();
        // }


        // // Map entities to simple arrays for view
        // // deleteme $validColumns = $this->fieldRegistryService->filterAndValidateFields($listColumns);
        // $dataRecords = array_map(
        //     function ($record) use ($cols) {
        //         return $this->repository->toArray($record, $cols);
        //     },
        //     $records
        // );
        $dataRecords = $records;

        $list = $this->listFactory->create(
            listType: $this->listType,
            data: $dataRecords,
            options: [
                // 'options'           => $options,
                'pagination'        => $paginationOptions,
                // 'render_options'    => $renderOptions,
                // 'list_fields'       => $listFields,
            ],
        );


        $viewData = [
            'title' => 'Testy List Action',
            'list' => $list,
        ];

        // $foo = $this->buildCommonViewData($viewData);
        // $fee = Url::CORE_TESTY_LIST->view();
        // $r = $this->view($fee, $foo);
        // $r = $this->view(Url::CORE_TESTY_LIST->view(), $this->buildCommonViewData($viewData));
        // return $r;
        // exit();

        // return $this->view(Url::CORE_TESTY_LIST->view(), $this->buildCommonViewData($viewData));
        return $this->view($url->view(), $this->buildCommonViewData($viewData));
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
            Url::CORE_USER,
            Url::CORE_USER_LIST,
            Url::CORE_USER_CREATE,
            Url::CORE_USER_EDIT,
        );
    }


    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
        // return parent::editAction(request: $request);

        $pageName       = $this->scrap->getPageName();
        $pageFeature    = $this->scrap->getPageFeature();
        $pageEntity     = $this->scrap->getPageEntity();

        $xpl = explode('_', $pageName);
        $entityNm = $xpl[0]; // hack dangerdanger - i do not like how i get table name

        $this->formType->setFocus(
            $pageName,
            $pageFeature,
            $pageEntity,
            $entityNm
        );

        $this->overrideFormTypeRenderOptions();

        $recordId = isset($this->route_params['id']) ? (int)$this->route_params['id'] : null;
        if ($recordId === null) {
            $this->flash22->add("Invalid record ID.", FlashMessageType::Error);
            return $this->redirect($this->feature->baseUrlEnum->view());
        }


        // 1. Define all columns needed for this request (form fields + permission fields).
        $formFields = $this->formType->getFields();

        $ownerForeignKey = $this->feature->ownerForeignKey;
        if (isset($ownerForeignKey)) {
            $requiredFields = array_unique(array_merge($formFields, [$ownerForeignKey]));
        }

        if ($request->getMethod() === 'GET') {
            // 2. Fetch the required data ONCE as an array.
            $recordArray = $this->userService->getUserByIdWithFields($recordId, $requiredFields);

            // 3. Check for existence and permissions using the fetched array.
            if (!$this->scrap->isAdmin()) {
                $this->checkForEditPermissions($recordArray);
            }

            // Transform data for form display
            $recordArray = $this->userService->transformForDisplay($recordArray, $pageName);
        } else {
            $recordArray = null;
        }

        // 4. Pass the fetched array to the form processor.
        $result = $this->processForm($request, $recordArray);
        $form   = $result['form'];

        // Prepare the form for JavaScript
        ///$form->setAttribute('data-ajax-action', '/testy/edit/' . $recordId . '/update');
        $form->setAttribute(
            'data-ajax-action',
            $this->feature->editUrlEnum->url(['id' => $recordId]) . '/update'
        );
        //$form->setAttribute('data-ajax-action', '/testy/edit/' . $recordId . '/update');
        //$form->setAttribute('data-ajax-save', 'true');

        // This block handles the submission AFTER the form has been processed
        if (
            $result['handled']
            && $result['valid']
            && $request->getHeaderLine('X-Requested-With') !== 'XMLHttpRequest'
        ) {
            $data = $form->getUpdatableData();

            // Transform form data before saving
            $data = $this->userService->transformForStorage($data, $pageName);



            // $fullRecordObj = $this->repository->findById($recordId);
            $fullRecordObj = $this->userService->getUserById($recordId); //fixme no purpose

            // foreach ($data as $name => $field) {
            //     if ($field->getAttribute('type') === 'hidden' || $field->getAttribute('disabled')
            //                                                               || $field->getAttribute('readonly')) {
            //         unset($data[$name]);
            //     }
            // }

            // if ($this->repository->updateFields($recordId, $data)) {
            if ($this->userService->updateUserWithFields($recordId, $data)) {
                $this->flash22->add("Record updated successfully", FlashMessageType::Success);
                return $this->redirect($this->getRedirectUrlAfterSave($recordId));
            } else {
                $form->addError('_form', 'Failed to update the record in the database.');
            }
        }

        // This block handles the initial page load (GET) or a failed submission
        $viewData = [
            'title' => 'Edit Record',
            'form' => $form,
            'formTheme' => $form->getCssFormThemeFile(),
        ];

        $url = $this->feature->editUrlEnum;
        $response =  $this->view($url->view(), $this->buildCommonViewData($viewData));


        // If the form has errors (on a failed POST), return a 422 status code
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;
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
     * Responds to POST /user/edit/{id}/update
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
        //return parent::createAction(request: $request);

        $pageName       = $this->scrap->getPageName();
        $pageFeature    = $this->scrap->getPageFeature();
        $pageEntity     = $this->scrap->getPageEntity();

        $xpl = explode('_', $pageName);
        $entityNm = $xpl[0]; // hack dangerdanger - i do not like how i get table name

        $this->formType->setFocus(
            $pageName,
            $pageFeature,
            $pageEntity,
            $entityNm
        );

        $this->overrideFormTypeRenderOptions();

        // 1. Define all columns needed for this request (form fields + permission fields).
        $formFields = $this->formType->getFields();

        $ownerForeignKey = $this->feature->ownerForeignKey;
        if (isset($ownerForeignKey)) {
            $requiredFields = array_unique(array_merge($formFields, [$ownerForeignKey]));
        }


        // 4. Pass the fetched array to the form processor.
        $result = $this->processForm($request, null);
        $form   = $result['form'];


        // This block handles the submission AFTER the form has been processed
        if (
            $result['handled']
            && $result['valid']
            && $request->getHeaderLine('X-Requested-With') !== 'XMLHttpRequest'
        ) {
            $data = $form->getUpdatableData();

            // Transform form data before saving
            $data = $this->userService->transformForStorage($data, $pageName);



            // $fullRecordObj = $this->repository->findById($recordId);
            //$fullRecordObj = $this->userService->getUserById($recordId); //fixme no purpose

            // foreach ($data as $name => $field) {
            //     if ($field->getAttribute('type') === 'hidden' || $field->getAttribute('disabled')
            //                                                               || $field->getAttribute('readonly')) {
            //         unset($data[$name]);
            //     }
            // }
            $newRecordId = $this->userService->insertFields($data);

            // if ($this->repository->updateFields($recordId, $data)) {
            if ($newRecordId) {
                $this->flash22->add("Record added successfully", FlashMessageType::Success);
                return $this->redirect($this->getRedirectUrlAfterSave((int)$newRecordId));
            } else {
                $form->addError('_form', 'Failed to update the record in the database.');
            }
        }

        // This block handles the initial page load (GET) or a failed submission
        $viewData = [
            'title' => 'Edit Record',
            'form' => $form,
            'formTheme' => $form->getCssFormThemeFile(),
        ];

        $url = $this->feature->editUrlEnum;
        $response =  $this->view($url->view(), $this->buildCommonViewData($viewData));


        // If the form has errors (on a failed POST), return a 422 status code
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;
    }
}
## 697 764 804 403 372
