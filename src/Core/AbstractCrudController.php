<?php

declare(strict_types=1);

namespace Core;

use App\Enums\FlashMessageType;
use App\Enums\PostFields2;
use App\Services\FeatureMetadataService;
use Core\Context\CurrentContext;
use Core\Controller;
use Core\Exceptions\ForbiddenException;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Container\ContainerInterface;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Enum\SortDirection;
use Core\Form\FormFactoryInterface;
use Core\Form\FormHandlerInterface;
use Core\Form\FormInterface;
use Core\Form\FormTypeInterface;
use Core\Form\Renderer\FormRendererInterface;
use Core\List\ListFactoryInterface;
use Core\List\ListTypeInterface;
use Core\View\ViewFactoryInterface;
use Core\View\ViewTypeInterface;
use Core\Repository\BaseRepositoryInterface;
use Core\Services\TypeResolverService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Core\List\Renderer\ListRendererInterface;
use Core\Services\BaseFeatureService;
use Core\Services\ReturnUrlManagerServiceInterface;
use Core\View\Renderer\ViewRendererInterface;

/**
 * Provides a base for controllers that handle standard CRUD operations.
 */
abstract class AbstractCrudController extends Controller
{
    /**
     * @param array<string, mixed> $route_params
     */
    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        CurrentContext $scrap,
        //-----------------------------------------
        protected FeatureMetadataService $feature,
        protected FormFactoryInterface $formFactory,
        protected FormHandlerInterface $formHandler,
        protected FormTypeInterface $formType,
        protected ListFactoryInterface $listFactory,
        protected ListTypeInterface $listType,
        protected ViewFactoryInterface $viewFactory,
        protected ViewTypeInterface $viewType,
        protected BaseRepositoryInterface $repository,
        protected TypeResolverService $typeResolver,
        protected ListRendererInterface $listRenderer,
        protected FormRendererInterface $formRenderer,
        protected ViewRendererInterface $viewRenderer,
        protected BaseFeatureService $baseFeatureService,
        protected ReturnUrlManagerServiceInterface $returnUrlManager,
        //-----------------------------------------
    ) {
        parent::__construct($route_params, $flash, $view, $httpFactory, $container, $scrap);
        // constructor uses promotion php8+
    }


   /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function listAction(ServerRequestInterface $request): ResponseInterface
    {
        $pageKey        = $this->scrap->getPageKey();
        $pageName       = $this->scrap->getPageName();
        $pageAction     = $this->scrap->getPageAction();
        $pageFeature    = $this->scrap->getPageFeature();
        $pageEntity     = $this->scrap->getPageEntity();

        $this->listType->setFocus(
            $pageKey,
            $pageName,
            $pageAction,
            $pageFeature,
            $pageEntity,
        );

        // ✅ Allow child controllers to override list rendering or other list configs
        $this->overrideListTypeRenderOptions();

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
        $totalRecords = $this->fetchTotalListRecords($request);

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

        // 5. Fetch actual records
        $records = $this->fetchListRecords($request, $limit, $offset, [$sortField => $sortDirection]);
        // $record2 = $this->baseFeatureService->transformToDisplay($records, $pageKey, $pageEntity);
        $dataRecords = array_map(function ($record) use ($pageKey, $pageEntity) {
            $rrr = $this->baseFeatureService->transformToDisplay($record, $pageKey, $pageEntity);
            return $rrr;
        }, $records);

        $paginationOptions['current_page'] = $page;
        $paginationOptions['total_pages'] = $totalPages;
        $paginationOptions['total_items'] = $totalRecords;
        $paginationOptions['listUrlEnum'] = $this->feature->listUrlEnum;

        // $dataRecords = $records;

        // ✅ Update the ListType with the calculated runtime pagination options
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
            options: [],
        );

        $renderedList = $this->listRenderer->renderList($list, []);

        $viewData = [
            'title' => 'List Action',
            'renderedList' => $renderedList,
        ];

        $url = $this->feature->listUrlEnum;
        return $this->view($url->view(), $this->buildCommonViewData($viewData));
    }


    /**
     * Edit an existing record. Handles standard GET and POST requests.
     *
     * @param ServerRequestInterface $request The incoming server request.
     * @return ResponseInterface The response, either rendering the view or redirecting.
     */
    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
        $pageKey       = $this->scrap->getPageKey();
        $pageName       = $this->scrap->getPageName();
        $pageAction     = $this->scrap->getPageAction();
        $pageFeature    = $this->scrap->getPageFeature();
        $pageEntity     = $this->scrap->getPageEntity();

        $this->formType->setFocus(
            $pageKey,
            $pageName,
            $pageAction,
            $pageFeature,
            $pageEntity
        );

        $recordId = isset($this->route_params['id']) ? (int)$this->route_params['id'] : null;
        if ($recordId === null) {
            $this->flash22->add("Invalid record ID.", FlashMessageType::Error);
            return $this->redirect($this->feature->baseUrlEnum->view());
        }

        // 📌 Store caller URL in session when entering edit page (GET only)
        if ($request->getMethod() === 'GET') {
            $callerUrl = $_SERVER['HTTP_REFERER'] ?? $this->feature->listUrlEnum->url([], $this->scrap->getRouteType());
            $this->returnUrlManager->setReturnUrl($recordId, $callerUrl);
        }

        // 📌 Inject form URLs and route context via render options
        $routeType = $this->scrap->getRouteType();

        $this->formType->mergeRenderOptions([
            'action_url' => $this->feature->editUrlEnum->url(['id' => $recordId], $routeType), // Form action URL enum
            'cancel_url' => $this->feature->listUrlEnum->url([], $routeType), // Cancel/back button URL enum
            'route_type' => $routeType, // Current route context
            'record_id'  => $recordId,
            // 'ajax_update_url' => $this->feature->editUrlEnum->url(['id' => $recordId], $routeType) . '/update',
        ]);


        // 📌 1. Define all columns needed for this request (form fields + permission fields).
        $formFields       = $this->formType->getFields();
        $formHiddenFields = $this->formType->getHiddenFields();
        $formExtraFields  = $this->formType->getExtraFields();

        $ownerForeignKey = $this->feature->ownerForeignKey;
        if (isset($ownerForeignKey)) {
            $requiredFields = array_unique(array_merge($formFields, $formExtraFields, $formHiddenFields, [$ownerForeignKey]));
        }
        // end



            //$recordArray = $this->fetchSingleRecord($recordId, $requiredFields);



        if ($request->getMethod() === 'GET') {
            // 2. Fetch the required data ONCE as an array.
            $recordArray = $this->fetchSingleRecord($recordId, $requiredFields);

            // 3. Check for existence and permissions using the fetched array.
            if (!$this->scrap->isAdmin()) {
                $this->checkForEditPermissions($recordArray);
            }

            $recordArray = $this->baseFeatureService->transformToDisplay($recordArray, $pageKey, $pageEntity);
        } else {
            $recordArray = null;
        }

        // findme - override field`
        // Important!!! -  atm, only used by image to change a field type from file to display
        $this->overrideFormTypeRenderOptions($pageAction, $formFields, $recordArray );


        // 4. Pass the fetched array to the form processor.
        $result = $this->processForm($request, $recordArray);
        $form   = $result['form'];


        // Prepare the form for JavaScript
        $form->setAttribute(
            'data-ajax-action',
            $this->feature->editUrlEnum->url(['id' => $recordId]) . '/update'
        );
        //$form->setAttribute('data-ajax-save', 'true');

        // This block handles the submission AFTER the form has been processed
        if (
            $result['handled']
            && $result['valid']
            && $request->getHeaderLine('X-Requested-With') !== 'XMLHttpRequest'
        ) {
            $data = $form->getUpdatableData();
            $extraProcessData = $form->getExtraProcessedData(); // ✅ All data, including metadata

            $fullRecordObj = $this->repository->findById($recordId);

            if (in_array('title', $formFields)) {
                // Auto-update slug if needed (same logic as before)
                $oldTitle = $fullRecordObj->getTitle() ?? null;
                $oldSlug  = $fullRecordObj->getSlug() ?? null;

                // Slug regeneration if needed
                if (isset($data['title']) && $data['title'] !== $oldTitle) {
                    // Only compute original-generated slug when title changed
                    $generatedFromOld = $this->generateSlug((string)$oldTitle);

                    // Regenerate slug if the current slug was auto-generated before or no slug supplied
                    if (empty($data['slug']) || $oldSlug === $generatedFromOld) {
                        $data['slug'] = $this->generateSlug((string)$data['title']);
                    }
                }
            }

            // Transform form data before saving
            $data = $this->baseFeatureService->transformToStorage($data, $pageKey, $pageEntity);


            // foreach ($data as $name => $field) {
            //     if ($field->getAttribute('type') === 'hidden' || $field->getAttribute('disabled')
            //                                                               || $field->getAttribute('readonly')) {
            //         unset($data[$name]);
            //     }
            // }

            $savedId = $this->saveRecord($data, $extraProcessData, $recordId); // ✅ Pass both data arrays
            if ($savedId) {
                $this->flash22->add("Record updated successfully", FlashMessageType::Success);
                return $this->redirect($this->getRedirectUrlAfterSave($recordId));
            } else {
                $form->addError('_form', 'Failed to update the record in the database.');
            }
        }


        $renderedForm = $this->formRenderer->renderForm($form, []);

        // This block handles the initial page load (GET) or a failed submission
        $viewData = [
            'title' => 'Edit RecordTitleOk',
            'form' => $form,
            'formTheme' => $form->getCssFormThemeFile(),
            'renderedForm' => $renderedForm,
            //'geo_region' => $regionCode,
        ];

        $url = $this->feature->editUrlEnum;
        $response =  $this->view($url->view(), $this->buildCommonViewData($viewData));


        // If the form has errors (on a failed POST), return a 422 status code
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;
    }

    public function viewAction(ServerRequestInterface $request): ResponseInterface
    {
        // ✅ NEW: Implement viewAction following the new pattern
        $recordId = (int)($this->route_params['id'] ?? 0);
        if (!$recordId) {
            $this->flash22->add("Invalid record ID for view.", FlashMessageType::Error);
            return $this->redirect($this->feature->listUrlEnum->url());
        }

        // ✅ Set focus on ViewType
        $pageKey = $this->scrap->getPageKey();
        $pageName = $this->scrap->getPageName();
        $pageAction = $this->scrap->getPageAction();
        $pageFeature = $this->scrap->getPageFeature();
        $pageEntity = $this->scrap->getPageEntity();

        $this->viewType->setFocus(
            $pageKey,
            $pageName,
            $pageAction,
            $pageFeature,
            $pageEntity
        );

        // ✅ Allow child controllers to override view type rendering or other view configs
        $this->overrideViewTypeRenderOptions();

        // ✅ Fetch record data using fields defined in ViewType
        $fields = $this->viewType->getFields();
        //$recordArray = $this->repository->findByIdWithFields($recordId, $fields);
        $recordArray = $this->fetchSingleRecord($recordId); // ✅ Calls abstract method

        if (!$recordArray) {
            $this->flash22->add("Record not found.", FlashMessageType::Error);
            return $this->redirect($this->feature->listUrlEnum->url());
        }

        // ✅ Check permissions (reusing checkForEditPermissions as it checks ownership)
        // Note: checkForEditPermissions throws ForbiddenException if not allowed
        if (!$this->scrap->isAdmin()) {
            $this->checkForEditPermissions($recordArray);
        }

        // ✅ Transform data for display using BaseFeatureService
        $recordArray = $this->baseFeatureService->transformToDisplay($recordArray, $pageKey, $pageEntity);


        // ✅ Inject action URLs into render options for the view renderer
        $routeType = $this->scrap->getRouteType();
        $this->viewType->mergeRenderOptions([
            'edit_url' => $this->feature->editUrlEnum->url(['id' => $recordId], $routeType),
            'delete_url' => $this->feature->deleteUrlEnum?->url(['id' => $recordId], $routeType) ?? '',
            'back_url' => $this->feature->listUrlEnum->url([], $routeType),
            'record_id' => $recordId,
            'route_type' => $routeType,
        ]);

        // ✅ Create View via ViewFactory
        $view = $this->viewFactory->create(
            viewType: $this->viewType,
            data: $recordArray
        );

        // ✅ Render View using the injected ViewRenderer
        $renderedView = $this->viewRenderer->renderView($view, []);

        // Prepare view data for the overall page layout
        $viewData = [
            // 'title' => $this->translator->get('view.record.title', ['pageName' => $pageName]),
            'title' => 'view.record.title',
            'renderedView' => $renderedView,
            'actionLinks' => $this->getReturnActionLinks(), // Include navigation links
        ];

        return $this->view($this->feature->viewUrlEnum->view(), $this->buildCommonViewData($viewData));
    }


    /**
     * Create a new record. Handles standard GET requests.
     *
     * @param ServerRequestInterface $request The incoming server request.
     * @return ResponseInterface The response, rendering the view.
     */
    public function createAction(ServerRequestInterface $request): ResponseInterface
    {
        $pageKey       = $this->scrap->getPageKey();
        $pageName       = $this->scrap->getPageName();
        $pageAction     = $this->scrap->getPageAction();
        $pageFeature    = $this->scrap->getPageFeature();
        $pageEntity     = $this->scrap->getPageEntity();

        $this->formType->setFocus(
            $pageKey,
            $pageName,
            $pageAction,
            $pageFeature,
            $pageEntity,
        );

        // $recordId = isset($this->route_params['id']) ? (int)$this->route_params['id'] : null;
        // if ($recordId === null) {
        //     $this->flash22->add("Invalid record ID.", FlashMessageType::Error);
        //     return $this->redirect($this->feature->baseUrlEnum->view());
        // }

        // // 📌 Store caller URL in session when entering edit page (GET only)
        // if ($request->getMethod() === 'GET') {
        //     $callerUrl = $_SERVER['HTTP_REFERER'] ?? $this->feature->listUrlEnum->url([], $this->scrap->getRouteType());
        //     $this->returnUrlManager->setReturnUrl($recordId, $callerUrl);
        // }

        // 📌 Inject form URLs and route context via render options
        $routeType = $this->scrap->getRouteType();

        $this->formType->mergeRenderOptions([
            'action_url' => $this->feature->createUrlEnum->url([], $routeType),
            'cancel_url' => $this->feature->listUrlEnum->url([], $routeType),
            'route_type' => $routeType,
            // 'record_id'  => $recordId,
            // 'ajax_update_url' => $this->feature->editUrlEnum->url(['id' => $recordId], $routeType) . '/update',
        ]);


        // 1. Define all columns needed for this request (form fields + permission fields).
        $formFields      = $this->formType->getFields();
        $formExtraFields = $this->formType->getExtraFields();

        // 1. Define all columns needed for this request (form fields + permission fields).
        $ownerForeignKey = $this->feature->ownerForeignKey;
        if (isset($ownerForeignKey)) {
            $requiredFields = array_unique(array_merge($formFields, $formExtraFields, [$ownerForeignKey]));
        }

        // if ($request->getMethod() === 'GET') {
        //     // 2. Fetch the required data ONCE as an array.
        //     $recordArray = $this->fetchSingleRecord($recordId, $requiredFields);

        //     // 3. Check for existence and permissions using the fetched array.
        //     if (!$this->scrap->isAdmin()) {
        //         $this->checkForEditPermissions($recordArray);
        //     }

        //     $recordArray = $this->baseFeatureService->transformToDisplay($recordArray, $pageKey, $pageEntity);
        // } else {
        //     $recordArray = null;
        // }

        // findme - override field
        // Important!!! -  atm, only used by image to change a field type from file to display
        //$this->overrideFormTypeRenderOptions([]);
        $this->overrideFormTypeRenderOptions($pageAction, $formFields);


        // 4. Pass the fetched array to the form processor.
        $result = $this->processForm($request, null);
        $form = $result['form'];


        // Prepare the form for JavaScript
        // $form->setAttribute(
        //     'data-ajax-action',
        //     $this->feature->editUrlEnum->url(['id' => $recordId]) . '/update'
        // );
        //$form->setAttribute('data-ajax-save', 'true');

        // This block handles the submission AFTER the form has been processed
        if (
            $result['handled']
            && $result['valid']
            && $request->getHeaderLine('X-Requested-With') !== 'XMLHttpRequest'
        ) {
            $data = $form->getUpdatableData();
            $extraProcessData = $form->getExtraProcessedData(); // ✅ All data, including metadata

            //$fullFormData = $form->getData(); // ✅ All data, including metadata

            // Add owner foreign key for new records
            $currentUserId = $this->scrap->getUserId();
            $data[$ownerForeignKey] = $currentUserId;

            if ($this->scrap->getPageFeature() !== 'User') {
                $data['store_id'] = $this->scrap->getStoreId();
            }

            // if (in_array('title', $formFields)) {
            if (array_key_exists('title', $data)) {
                // Slug regeneration if needed
                if (isset($data['title'])) {
                    // Regenerate slug if the current slug was auto-generated before or no slug supplied
                    $data['slug'] = $this->generateSlug((string)$data['title']);
                } else {
                    $data['title'] = $this->generateTitle(3, 6);
                    // protected function generateTitle(int $wordCount = 2, int $wordLength = 6): string

                    $data['slug'] = $this->generateSlug((string)$data['title']);
                }
            }

            // Transform form data before saving
            $data = $this->baseFeatureService->transformToStorage($data, $pageKey, $pageEntity);


            // foreach ($data as $name => $field) {
            //     if ($field->getAttribute('type') === 'hidden' ||
            //                             $field->getAttribute('disabled') || $field->getAttribute('readonly')) {
            //         unset($data[$name]);
            //     }
            // }

            $savedId = $this->saveRecord($data, $extraProcessData); // ✅ Pass both data arrays
            if ($savedId) {
                $this->flash22->add("Record added successfully", FlashMessageType::Success);
                return $this->redirect($this->getRedirectUrlAfterSave((int)$savedId));
            } else {
                $form->addError('_form', 'Failed to add the record in the database.');
           }
        }

        $renderedForm = $this->formRenderer->renderForm($form, []);

        // This block handles the initial page load (GET)
        $viewData = [
            'title' => 'Create New Record',
            'form' => $form,
            'formTheme' => $form->getCssFormThemeFile(),
            'renderedForm' => $renderedForm,
        ];

        $url = $this->feature->editUrlEnum;
        // $url = $this->feature->createUrlEnum;
        $response = $this->view($url->view(), $this->buildCommonViewData($viewData));

        // If the form has errors (unlikely on GET), return a 422 status code
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;
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
        //DebugRt::j('1', '', 'boom');
        try {
            $recordId = isset($this->route_params['id']) ? (int)$this->route_params['id'] : null;
            if ($recordId === null) {
                return $this->json(['success' => false, 'message' => 'Record ID is missing.'], 400);
            }

            // 1. Define all columns needed.
            $formFields = $this->formType->getFields();
            $requiredFields = array_unique(array_merge($formFields, ['user_id']));//hardcoded

            // 2. Fetch the required data ONCE as an array.
            // $recordArray = $this->repository->findByIdWithFields($recordId, $requiredFields);
            $recordArray = $this->fetchSingleRecord($recordId); // Need existing data for context/validation

            // 3. Check permissions.
            $this->checkForEditPermissions($recordArray);

            // 4. Pass the array to the form processor.
            $result = $this->processForm($request, $recordArray);
            $form = $result['form'];


            if ($result['handled'] && $result['valid']) {
                $data = $form->getUpdatableData();
                $fullFormData = $form->getData(); // ✅ All data, including metadata

                $savedId = $this->saveRecord($data, $fullFormData, $recordId); // ✅ Calls abstract method


                // if ($this->repository->updateFields($recordId, $data)) {
                if ($savedId) {
                    $redirectUrl = null;
                    if ($this->feature->redirectAfterSave === 'list') {
                        $redirectUrl = $this->getRedirectUrlAfterSave($recordId);
                    }

                    return $this->json([
                        'success' => true,
                        'message' => 'Record updated successfully.',
                        'redirect_url' => $redirectUrl
                    ]);
                }

                return $this->json(
                    [
                        'success' => false,
                        'message' => 'Failed to save to the database.'
                    ],
                    500
                );
            }

            return $this->json([
                'success' => false,
                'message' => 'Validation failed. Please check the form.',
                'errors' => $form->getErrors()
            ], 422);
        } catch (ForbiddenException $e) {
            // If checkForEditPermissions fails, catch the exception and return a 403 Forbidden error.
            return $this->json(['success' => false, 'message' => $e->getMessage()], $e->getCode());
        }
    }



    /**
     * Handles deletion of a record.
     *
     * On GET request: Displays a confirmation page for deletion.
     * On POST request: Performs the actual deletion after confirmation.
     *
     * @param ServerRequestInterface $request The incoming server request.
     * @return ResponseInterface The response, either rendering the confirmation view,
     *                           redirecting after deletion, or returning an error.
     * @throws ForbiddenException If the user is not authorized, the record doesn't exist,
     *                            or the CSRF token is invalid.
     */
    public function deleteAction(ServerRequestInterface $request): ResponseInterface
    {
        $recordId = isset($this->route_params['id']) ? (int)$this->route_params['id'] : null;
        if ($recordId === null) {
            $this->flash22->add("Invalid record ID.", FlashMessageType::Error);
            return $this->redirect($this->feature->listUrlEnum->url());
        }

        // Fetch record to perform permission check and get display data
        $ownerForeignKey = $this->feature->ownerForeignKey;
        // For User, 'username' is a good descriptive field.
        $recordArray = $this->repository->findByIdWithFields($recordId, ['id', 'username', $ownerForeignKey]);

        // Check permissions (reusing checkForEditPermissions for ownership)
        $this->checkForEditPermissions($recordArray); // Throws ForbiddenException if not allowed

        // Determine record title for confirmation message
        $recordTitle = $recordArray['username'] ?? 'this item'; // Use username for User entity

        if ($request->getMethod() === 'GET') {
            // Display confirmation page
            $viewData = [
                'title' => "Confirm Delete: " . htmlspecialchars((string)$recordTitle),
                'recordId' => $recordId,
                'recordTitle' => $recordTitle,
                'deleteUrl' => $this->feature->deleteUrlEnum->url(['id' => $recordId]), // POST target
                'cancelUrl' => $this->feature->listUrlEnum->url(),
                'csrfToken' => $this->scrap->getCsrfToken(), // Get CSRF token for the form
            ];

            return $this->view($this->feature->deleteConfirmUrlEnum->view(), $this->buildCommonViewData($viewData));
        } elseif ($request->getMethod() === 'POST') {
            // Perform actual deletion
            try {
                // Manual CSRF protection for the delete form
                $parsedBody = $request->getParsedBody();
                $csrfToken = $parsedBody['csrf_token'] ?? '';
                if (!$this->scrap->validateCsrfToken($csrfToken)) {
                    throw new ForbiddenException("Invalid CSRF token.", 403);
                }

                if ($this->repository->delete($recordId)) {
                    $this->flash22->add("Record '" . htmlspecialchars((string)$recordTitle)
                                                   . "' deleted successfully", FlashMessageType::Success);

                    return $this->redirect($this->feature->listUrlEnum->url());
                } else {
                    $this->flash22->add("Failed to delete record '" . htmlspecialchars((string)$recordTitle)
                                                                    . "'.", FlashMessageType::Error);

                    return $this->redirect($this->feature->listUrlEnum->url());
                }
            } catch (ForbiddenException $e) {
                $this->flash22->add($e->getMessage(), FlashMessageType::Error);

                return $this->redirect($this->feature->listUrlEnum->url());
            } catch (\Throwable $e) {
                error_log("Error deleting record (ID: {$recordId}): " . $e->getMessage());
                $this->flash22->add("An unexpected error occurred while deleting record '"
                                              . htmlspecialchars((string)$recordTitle) . "'.", FlashMessageType::Error);

                return $this->redirect($this->feature->listUrlEnum->url());
            }
        }

        // Should not reach here
        return $this->redirect($this->feature->listUrlEnum->url());
    }




    //abstract protected function processForm(ServerRequestInterface $request, ?array $recordArray): array;

    /**
     * Creates and processes the form for both GET and POST requests.
     *
     * @param ServerRequestInterface $request The current request.
     * @param array<string, mixed>|null $recordArray The record data as an array.
     * @return array{handled: bool, valid: bool, form: \Core\Form\FormInterface}
     */
    protected function processForm(ServerRequestInterface $request, ?array $recordArray): array
    {
        $initialData = [];

        // For a GET request, use the pre-fetched array. No database call is needed here.
        if ($request->getMethod() === 'GET' && $recordArray) {
            $initialData = $recordArray;
        }

        // findme - FormFactory-Create
        // Create the form instance.
        // If it's a GET request, it will be populated with $initialData.
        // If it's a POST request, $initialData is empty, and the formHandler will populate it.
        $form = $this->formFactory->create(
            formType: $this->formType,
            data: $initialData
        );

        // 📌 This ensures the FormHandler has access to the store_id for operations like image uploads.
        $form->addContext(['store_id' => $this->scrap->getStoreId()]);

        // The form handler processes the request.
        // For a GET request, it does nothing.
        // For a POST request, it populates the form with submitted data and validates it.
        $formHandled = $this->formHandler->handle($form, $request);

        return [
            'handled' => $formHandled,
            'valid' => $form->isValid(),
            'form' => $form
        ];
    }

    /**
     * Hook method for child controllers to override form type rendering options.
     *
     * This method is called during form processing to apply feature-specific
     * display adjustments or configuration overrides to the FormType.
     *
     * @param array<string, mixed>|null $recordData The existing record data as an array (if in edit context),
     *                      or null for new creation.
     * @return void
     */
    // abstract protected function overrideFormTypeRenderOptions(?array $recordData = null): void;
    abstract protected function overrideFormTypeRenderOptions(string $pageAction, array $formFields, ?array $recordData = null): void;


    /**
     * Abstract method to allow child controllers to override view type rendering options.
     *
     * This method is called during view processing to apply feature-specific
     * display adjustments or configuration overrides to the ViewType.
     *
     * @return void
     */
    abstract protected function overrideViewTypeRenderOptions(): void;

    /**
     * Abstract method to allow child controllers to override list type rendering options.
     *
     * This method is called during list processing to apply feature-specific
     * display adjustments or configuration overrides to the ListType.
     *
     * @return void
     */
    abstract protected function overrideListTypeRenderOptions(): void;



    /**
     * Determines where to redirect after a successful save.
     *
     * Precedence:
     * 1. Session-stored return URL (caller URL captured on GET)
     * 2. Feature config redirectAfterSave (list or edit)
     *
     * @param int $recordId The record being saved.
     * @return string The URL to redirect to after save.
     */
    protected function getRedirectUrlAfterSave(int $recordId): string
    {
        // Important!!!   - this is jusy temp out
        // // ✅ Check session first: if user came from a specific caller page, return there
        // $returnUrl = $this->returnUrlManager->getAndClearReturnUrl($recordId);
        // if ($returnUrl) {
        //     return $returnUrl;
        // }

        // Fall back to config-driven redirect
        if ($this->feature->redirectAfterSave === 'list') {
            return $this->feature->listUrlEnum->url(routeType: $this->scrap->getRouteType());
        }

        return $this->feature->editUrlEnum->url(['id' => $recordId], $this->scrap->getRouteType());
    }


    /**
     * Verifies if the current user has permission to edit the specified record.
     *
     * This method is generic and relies on the FeatureMetadataService to determine
     * the correct foreign key for the ownership check.
     *
     * @param array<string, mixed>|null $recordArray The record data as an array.
     * @return void
     * @throws ForbiddenException If the user is not authorized or the record doesn't exist.
     */
    protected function checkForEditPermissions(?array $recordArray): void
    {
        // 1. Check if the record even exists.
        if (!$recordArray) {
            throw new ForbiddenException("Record not found.", 404);
        }

        // 2. Get the ID of the currently logged-in user.
        $currentUserId = $this->scrap->getUserId();
        //$currentUserId = 4; //hack because i am not logged in

        // 3. Get the owner foreign key from the metadata service (e.g., 'user_id', 'user_id')
        $ownerForeignKey = $this->feature->ownerForeignKey;

        // 4. Compare the record's owner ID with the current user's ID.
        if (!isset($recordArray[$ownerForeignKey]) || $recordArray[$ownerForeignKey] !== $currentUserId) {
            // 5. If they don't match or the key isn't present, throw an exception.
            throw new ForbiddenException("You do not have permission to edit this record.", 403);
        }
    }

    ///////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Fetches a list of records for display.
     * Child controllers can override this to define how list data is retrieved,
     * e.g., using a specific service or repository methods with custom filtering/sorting.
     *
     * @param ServerRequestInterface $request The current server request, useful for query parameters.
     * @param int $limit The number of records to fetch.
     * @param int $offset The starting offset for records.
     * @param array<string, string> $orderBy Associative array of column => direction (ASC/DESC).
     * @return array<array<string, mixed>> An array of record data.
     */
    protected function fetchListRecords(ServerRequestInterface $request, int $limit, int $offset, array $orderBy): array
    {
        $listFields = $this->listType->getFields(); // Get fields from ListType
        $pageEntity = $this->scrap->getPageEntity();
        $routeType  = $this->scrap->getRouteType();

        // Implement Testy-specific logic to fetch records
        if ($routeType === "account") {
            $userId = $this->scrap->getUserId();
            return $this->repository->findByUserIdWithFields(
                $userId,
                $listFields,
                $orderBy,
                $limit,
                $offset
            );
        } else { // 'core' route or other default
            // Assuming for 'user' entity in core, we fetch all, otherwise by store ID
            if ($pageEntity === 'user') { // This logic would typically be in UserController
                return $this->repository->findAllWithFields(
                    $listFields,
                    $orderBy,
                    $limit,
                    $offset
                );
            } else {
                $storeId = $this->scrap->getStoreId();
                return $this->repository->findByStoreIdWithFields(
                    $storeId,
                    $listFields,
                    $orderBy,
                    $limit,
                    $offset
                );
            }
        }
    }

    /**
     * Gets the total count of records for the list.
     * Child controllers can override this to define how the total count is retrieved,
     * considering any filters or specific conditions.
     *
     * @param ServerRequestInterface $request The current server request.
     * @return int The total number of records.
     */
    // abstract protected function fetchTotalListRecords(ServerRequestInterface $request): int;
    protected function fetchTotalListRecords(ServerRequestInterface $request): int
    {
        $pageEntity = $this->scrap->getPageEntity();
        $routeType  = $this->scrap->getRouteType();

        // Implement Testy-specific logic to get total count
        if ($routeType === "account") {
            $userId = $this->scrap->getUserId();
            return $this->repository->countByUserId($userId);
        } elseif ($routeType === "public") {
            $storeId = $this->scrap->getStoreId();
            return $this->repository->countByStoreId($storeId);
        } else { // 'core' route or other default
            if ($pageEntity === 'user') { // This logic would typically be in UserController
                return $this->repository->countAll();
            } else {
                return $this->repository->countAll();
            }
        }
    }

    /**
     * Fetches a single record by its ID.
     * Child controllers can override this to define how a single record's data is retrieved.
     *
     * @param int $id The ID of the record to fetch.
     * @param array<string> $fields An array of fields to retrieve for the record.
     * @return array<string, mixed>|null The record data as an associative array, or null if not found.
     */
    // abstract protected function fetchSingleRecord(int $id, array $fields = []): ?array;
    protected function fetchSingleRecord(int $id, array $fields = []): ?array
    {
        // This uses the injected TestyRepositoryInterface for Testy-specific data.
        // return $this->repository->findById($id);
        return $this->repository->findByIdWithFields($id, $fields);
    }


    /**
     * Persists (creates or updates) a record based on form data.
     * Child controllers can override this to define how record data is saved,
     * e.g., using a specific service for business logic and validation, then a repository.
     *
     * @param array<string, mixed> $updatableData The validated and filtered data, ready for direct DB update (e.g., from form->getUpdatableData()).
     * @param array<string, mixed> $fullFormData The complete validated data from the form, including any auxiliary data (e.g., from form->getData()).
     * @param int|null $id The ID of the record to update, or null for a new record.
     * @return int|null The ID of the saved record (new ID for create, existing for update), or null on failure.
     */
    // abstract protected function saveRecord(array $updatableData, ?int $id = null): ?int;
    protected function saveRecord(array $updatableData, array $fullFormData, ?int $id = null): ?int // ✅ UPDATED SIGNATURE
    {
        // This is where TestyController uses its specific repository (or service) to save data.
        // If you had a TestyService with business logic, you'd call it here:
        // return $this->testyService->save($updatableData, $id);
        if ($id) {
            // Update existing record
            return $this->repository->updateFields($id, $updatableData) ? $id : null;
        } else {
            // Create new record
            return $this->repository->insertFields($updatableData);
        }
    }

    /**
     * Deletes a record by its ID.
     * Child controllers can override this to define how a record is deleted,
     * e.g., using a specific service for business rules before repository deletion.
     *
     * @param int $id The ID of the record to delete.
     * @return bool True if deletion was successful, false otherwise.
     */
    // abstract protected function deleteRecord(int $id): bool;
    protected function deleteRecord(int $id): bool
    {
        // This uses the injected TestyRepositoryInterface for Testy-specific deletion.
        return $this->repository->delete($id);
    }

    // 1011 1052 977
}
