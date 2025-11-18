<?php

declare(strict_types=1);

namespace Core;

use App\Enums\FlashMessageType;
use App\Enums\PostFields2;
use App\Enums\Url;
use App\Features\Testy\TestyRepositoryInterface;
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
use Core\Form\FormTypeInterface;
use Core\List\ListFactoryInterface;
use Core\List\ListTypeInterface;
use Core\Repository\BaseRepositoryInterface;
use Core\Services\TypeResolverService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Core\List\Renderer\ListRendererInterface;
use Core\Services\BaseFeatureService;

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
        protected BaseRepositoryInterface $repository,
        protected TypeResolverService $typeResolver,
        protected ListRendererInterface $listRenderer,
        protected BaseFeatureService $baseFeatureService
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
                $totalRecords = $this->repository->countAll();
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
        if ($this->scrap->getRouteType() === "account") {
            $userId = $this->scrap->getUserId();
            $records = $this->repository->findByUserIdWithFields(
                $userId,
                $listFields,
                [$sortField => $sortDirection],
                $limit,
                $offset
            );

            // $totalRecords = $this->repository->countByUserId($userId);
        } else {
            if ($pageEntity === 'user') {
                $records = $this->repository->findAllWithFields(
                    $listFields,
                    [$sortField => $sortDirection],
                    $limit,
                    $offset
                );

                // $totalRecords = $this->repository->countAll();
            } else {
                $storeId = $this->scrap->getStoreId();

                $records = $this->repository->findByStoreIdWithFields(
                    $storeId,
                    $listFields,
                    [$sortField => $sortDirection],
                    $limit,
                    $offset
                );

                // $totalRecords = $this->repository->countByStoreId($storeId);
            }
        }

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

        $this->overrideFormTypeRenderOptions();

        $recordId = isset($this->route_params['id']) ? (int)$this->route_params['id'] : null;
        if ($recordId === null) {
            $this->flash22->add("Invalid record ID.", FlashMessageType::Error);
            return $this->redirect($this->feature->baseUrlEnum->view());
        }

        // ✅ NEW: Inject form URLs and route context via render options
        $routeType = $this->scrap->getRouteType();

        // $this->formType->mergeRenderOptions([
        //     'url_enums' => [
        //         'action' => $this->feature->editUrlEnum, // Form action URL enum
        //         'cancel' => $this->feature->listUrlEnum, // Cancel/back button URL enum
        //         'delete' => $this->feature->deleteUrlEnum ?? null, // Optional delete button
        //     ],
        //     'action_url' => $this->feature->editUrlEnum->url(['id' => $recordId], $routeType), // Full action URL
        //     'cancel_url' => $this->feature->listUrlEnum->url([], $routeType), // Full cancel URL
        //     'route_type' => $routeType, // Current route context
        //     'record_id' => $recordId, // Current record ID (for AJAX updates)
        //     'ajax_update_url' => $this->feature->editUrlEnum->url(['id' => $recordId], $routeType) . '/update',
        // ]);
        $this->formType->mergeRenderOptions([
            'action_url' => $this->feature->editUrlEnum->url(['id' => $recordId], $routeType),
            'cancel_url' => $this->feature->listUrlEnum->url([], $routeType),
            'route_type' => $routeType,
        ]);


        // 1. Define all columns needed for this request (form fields + permission fields).
        $formFields = $this->formType->getFields();

        $ownerForeignKey = $this->feature->ownerForeignKey;
        if (isset($ownerForeignKey)) {
            $requiredFields = array_unique(array_merge($formFields, [$ownerForeignKey]));
        }

        if ($request->getMethod() === 'GET') {
            // 2. Fetch the required data ONCE as an array.
            $recordArray = $this->repository->findByIdWithFields($recordId, $requiredFields);

            // 3. Check for existence and permissions using the fetched array.
            if (!$this->scrap->isAdmin()) {
                $this->checkForEditPermissions($recordArray);
            }

            $recordArray = $this->baseFeatureService->transformToDisplay($recordArray, $pageKey, $pageEntity);
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

            if ($this->repository->updateFields($recordId, $data)) {
                $this->flash22->add("Record updated successfully", FlashMessageType::Success);
                return $this->redirect($this->getRedirectUrlAfterSave($recordId));
            } else {
                $form->addError('_form', 'Failed to update the record in the database.');
            }

            // } catch (\PDOException $e) {
            //     if ($e->getCode() === '23000') { // Integrity constraint violation
            //         // if (str_contains($e->getMessage(), 'gender_id')) {
            //         //     $form->addError('gender_id', 'Gender is required.');
            //         // } else {
            //             $form->addError('_form', 'A required field is missing or invalid.');
            //         // }
            //     } else {
            //         // For other DB errors, you may want to log and show a generic error
            //         $form->addError('_form', 'A database error occurred. Please try again.');
            //     }
            // }
        }

        // This block handles the initial page load (GET) or a failed submission
        $viewData = [
            'title' => 'Edit Record',
            'form' => $form,
            'formTheme' => $form->getCssFormThemeFile(),
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

        $this->overrideFormTypeRenderOptions();


        // ✅ NEW: Inject form URLs and route context via render options
        $routeType = $this->scrap->getRouteType();

        // $this->formType->mergeRenderOptions([
        //     'url_enums' => [
        //         'action' => $this->feature->createUrlEnum, // Form action URL enum (for store)
        //         'cancel' => $this->feature->listUrlEnum, // Cancel/back button URL enum
        //     ],
        //     'action_url' => $this->feature->createUrlEnum->url([], $routeType) . '/store', // Full action URL
        //     'cancel_url' => $this->feature->listUrlEnum->url([], $routeType), // Full cancel URL
        //     'route_type' => $routeType, // Current route context
        //     'ajax_store_url' => $this->feature->createUrlEnum->url([], $routeType) . '/store',
        // ]);
        $this->formType->mergeRenderOptions([
            'action_url' => $this->feature->createUrlEnum->url([], $routeType),
            'cancel_url' => $this->feature->listUrlEnum->url([], $routeType),
            'route_type' => $routeType,
        ]);



        // 1. Define all columns needed for this request (form fields + permission fields).
        $ownerForeignKey = $this->feature->ownerForeignKey;
        // $requiredFields = array_unique(array_merge($formFields, [$ownerForeignKey]));

        // // 2. For create, no record array is needed (new record).
        // $recordArray = null;

        // For create, no record array is needed
        $result = $this->processForm($request, null);
        $form = $result['form'];

        // Prepare the form for JavaScript
        //$form->setAttribute('data-ajax-action', $this->feature->createUrlEnum->url() . '/store');



        // This block handles the submission AFTER the form has been processed
        if (
            $result['handled']
            && $result['valid']
            && $request->getHeaderLine('X-Requested-With') !== 'XMLHttpRequest'
        ) {
            $data = $form->getUpdatableData();

            // Add owner foreign key for new records
            $currentUserId = $this->scrap->getUserId();
            // $currentUserId = 4; // Hack because not logged in
            $data[$ownerForeignKey] = $currentUserId;

            if ($this->scrap->getPageFeature() !== 'User') {
                $data['store_id'] = $this->scrap->getStoreId(); // Hack because not logged in
            }

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
            $data = $this->baseFeatureService->transformForStorage($data, $pageKey);


            // foreach ($data as $name => $field) {
            //     if ($field->getAttribute('type') === 'hidden' ||
            //                             $field->getAttribute('disabled') || $field->getAttribute('readonly')) {
            //         unset($data[$name]);
            //     }
            // }


            $newRecordId = $this->repository->insertFields($data);
            if ($newRecordId) {
                $this->flash22->add("Record added successfully", FlashMessageType::Success);
                return $this->redirect($this->getRedirectUrlAfterSave((int)$newRecordId));
            }

            $form->addError('_form', 'Failed to save the record in the database.');
        }



        // This block handles the initial page load (GET)
        $viewData = [
            'title' => 'Create New Record',
            'form' => $form,
            'formTheme' => $form->getCssFormThemeFile(),
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
            $recordArray = $this->repository->findByIdWithFields($recordId, $requiredFields);

            // 3. Check permissions.
            $this->checkForEditPermissions($recordArray);

            // 4. Pass the array to the form processor.
            $result = $this->processForm($request, $recordArray);
            $form = $result['form'];

            if ($result['handled'] && $result['valid']) {
                $data = $form->getUpdatableData();

                if ($this->repository->updateFields($recordId, $data)) {
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


        // Create the form instance.
        // If it's a GET request, it will be populated with $initialData.
        // If it's a POST request, $initialData is empty, and the formHandler will populate it.
        $form = $this->formFactory->create(
            formType: $this->formType,
            data: $initialData
        );



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

    abstract protected function overrideFormTypeRenderOptions(): void;

    // // Notes-: this is an example of a Helper Method
    // /**
    //  * Determines where to redirect after a successful save.
    //  *
    //  * @param int $recordId
    //  * @return ResponseInterface
    //  */
    // protected function redirectAfterSave(int $recordId): ResponseInterface
    // {
    //     if ($this->feature->redirectAfterSave === 'list') {
    //         return $this->redirect($this->feature->baseUrlEnum->url());
    //     }

    //     return $this->redirect($this->feature->editUrlEnum->url(['id' => $recordId]));
    // }

    // Notes-: this is an example of a Helper Method
    /**
     * Determines where to redirect after a successful save.
     *
     * @param int $recordId
     * @return string
     */
    protected function getRedirectUrlAfterSave(int $recordId): string
    {
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
}
