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
use Core\List\Renderer\ListRendererInterface;
use Core\Services\TypeResolverService;
use Psr\Log\LoggerInterface;

/**
 * User controller
 *
 */
class UserController extends AbstractCrudController
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
        UserRepositoryInterface $repository,
        TypeResolverService $typeResolver,
        ListRendererInterface $listRenderer,
        //-----------------------------------------
        protected ConfigInterface $config,
        protected LoggerInterface $logger,
        protected EmailNotificationService $emailNotificationService,
        private PaginationService $paginationService,
        private FormatterService $formatter,
        private UserService $userService
        //-----------------------------------------
        // BaseFeatureService is already injected by parent AbstractCrudController.
        // No need to inject it here again.
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
            $this->baseFeatureService
        );
        // constructor uses promotion php8+
        // $this->config = $config;
        // // $this->formFactory = $formFactory;
        // // $this->formHandler = $formHandler;
        // // $this->repository = $repository;
        // // $this->formType = $formType;
        // // $this->listFactory = $listFactory;
        // // $this->listType = $listType;
        // $this->listType->routeType = $scrap->getRouteType();
        // $this->logger = $logger;
        // $this->emailNotificationService = $emailNotificationService;
        // $this->paginationService = $paginationService;
        // $this->formatter = $formatter;
        // $this->userService = $userService;
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
        $pageName       = $this->scrap->getPageName();
        $pageFeature    = $this->scrap->getPageFeature();
        $pageEntity     = $this->scrap->getPageEntity();

        $this->listType->setFocus(
            $pageName,
            $pageFeature,
            $pageEntity,
        );


        $options            = $this->listType->getOptions() ?? [];
        $renderOptions      = [];
        $paginationOptions  = $this->listType->getPaginationOptions() ?? [];
        $listFields         = $this->listType->getFields() ?? [];


        $sortField = $options['default_sort_key']
            ?? $this->listType->getOptions()['default_sort_key'] ?? PostFields2::TITLE->value;
        $sortDirection = $options['default_sort_direction']
            ?? $this->listType->getOptions()['default_sort_direction'] ?? SortDirection::ASC->value;

        // Get the record with pagination
        $page = isset($this->route_params['page']) ? (int)$this->route_params['page'] : 1;
        $limit = $paginationOptions['per_page']
            ?? $this->listType->getPaginationOptions()['per_page'] ?? 2;
        $offset = ($page - 1) * $limit;


        $routeType = $this->scrap->getRouteType();

        $totalRecords = 0;
        // 1. Fetch total records first to determine total pages
        if ($this->scrap->getRouteType() === "account") {
            $userId = $this->scrap->getUserId();
            $totalRecords = $this->repository->countByUserId($userId);
        } else {
            if ($pageEntity === 'user') {
                $totalRecords = $this->userService->countAllUsers();
            } else {
                $storeId = $this->scrap->getStoreId();
                $totalRecords = $this->repository->countByStoreId($storeId);
            }
        }

        // 2. Calculate total pages. Ensure it's at least 1, even if no records.
        $totalPages = ($totalRecords > 0) ? ceil($totalRecords / $limit) : 1;


        // 3. Validate the requested page number and adjust internally
        if ($page < 1) {
            $page = 1; // If page is less than 1, default to page 1
        } elseif ($page > $totalPages) {
            $page = (int)$totalPages; // If page is too high, set to the last valid page
        }

        // 4. Recalculate offset with the potentially corrected page number
        $offset = ($page - 1) * $limit;

        $records = [];

        // 5. Fetch actual records
        if ($this->scrap->getRouteType() === "store") {
            $storeId = 1;
            $records = $this->repository->findByStoreIdWithFields(
                $storeId,
                $listFields,
                [$sortField => $sortDirection],
                $limit,
                $offset
            );

            // $totalRecords = $this->repository->countByStoreId($storeId);
        } else {
            $records = $this->userService->getAllUsersWithFields(
                $listFields,
                $sortField,
                $sortDirection,
                $limit,
                $offset
            );

            // $totalRecords = $this->userService->countAllUsers();
        }

        $totalPages = ceil($totalRecords / $limit);
        $paginationOptions['current_page'] = $page;
        $paginationOptions['total_pages'] = $totalPages;
        $paginationOptions['total_items'] = $totalRecords;
        $paginationOptions['listUrlEnum'] = $this->feature->listUrlEnum;

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

        // ✅ NEW: Update the ListType with the calculated runtime pagination options
        $this->listType->setPaginationOptions($paginationOptions);

        $this->listType->mergeRenderOptions([
            'url_enums' => [
                'list' => $this->feature->listUrlEnum,
                'edit' => $this->feature->editUrlEnum,
                'delete' => $this->feature->deleteUrlEnum ?? $this->feature->editUrlEnum,
                'view' => $this->feature->viewUrlEnum ?? $this->feature->baseUrlEnum,
                'add' => $this->feature->createUrlEnum ?? $this->feature->baseUrlEnum,
            ],
            'add_url' => $this->feature->createUrlEnum?->url([], $routeType) ?? '',
            'route_type' => $this->scrap->getRouteType(),
            'current_query_params' => $this->scrap->getPageQueryParms(),
            'view_type' => $this->scrap->getPageListViewType(),
        ]);

        $list = $this->listFactory->create(
            listType: $this->listType,
            data: $dataRecords,
            // The 'options' array is now empty as all configuration is on the ListType.
            // You can remove it entirely or leave it as an empty array for future factory-specific flags.
            // options: [],
            options: [
                // 'options'           => $options,
                // 'pagination'        => $paginationOptions,
                // 'render_options'    => $renderOptions,
                // 'list_fields'       => $listFields,
            ],
        );

        $renderedList = $this->listRenderer->renderList($list, []);

        $viewData = [
            'title' => 'User List Action',
            'renderedList' => $renderedList,
        ];


        $url = $this->feature->listUrlEnum;
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
        $pageName       = $this->scrap->getPageName();
        $pageFeature    = $this->scrap->getPageFeature();
        $pageEntity     = $this->scrap->getPageEntity();

        $this->formType->setFocus(
            $pageName,
            $pageFeature,
            $pageEntity
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
            $recordArray = $this->baseFeatureService->transformToDisplay($recordArray, $pageName, $pageEntity);
            //$recordArray = $this->userService->transformForDisplay($recordArray, $pageName);
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
            // $data = $this->userService->transformForStorage($data, $pageName);
            $data = $this->baseFeatureService->transformToStorage($data, $pageName, $pageEntity);



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
        $pageName       = $this->scrap->getPageName();
        $pageFeature    = $this->scrap->getPageFeature();
        $pageEntity     = $this->scrap->getPageEntity();

        $this->formType->setFocus(
            $pageName,
            $pageFeature,
            $pageEntity
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
            // $data = $this->userService->transformForStorage($data, $pageName);
            $data = $this->baseFeatureService->transformForStorage($data, $pageName);



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
