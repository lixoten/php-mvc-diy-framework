<?php

declare(strict_types=1);

namespace Core;

use App\Enums\FlashMessageType;
use App\Enums\PostFields2;
use App\Enums\Url;
use App\Features\Testy\Form\TestyFormType;
use App\Repository\TestyRepositoryInterface;
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

/**
 * Provides a base for controllers that handle standard CRUD operations.
 */
abstract class AbstractCrudController extends Controller
{
    protected FeatureMetadataService $feature;
    protected FormFactoryInterface $formFactory;
    protected FormHandlerInterface $formHandler;
    protected FormTypeInterface $formType;
    protected BaseRepositoryInterface $repository;

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
        // Add the service to the constructor
        FeatureMetadataService $feature,
        FormFactoryInterface $formFactory,
        FormHandlerInterface $formHandler,
        FormTypeInterface $formType, // Change to interface//dangerdanger
        private ListFactoryInterface $listFactory,
        private ListTypeInterface $listType,

        BaseRepositoryInterface $repository,
        protected TypeResolverService $typeResolver,
    ) {
        parent::__construct($route_params, $flash, $view, $httpFactory, $container, $scrap);
        $this->feature = $feature;
        $this->formFactory = $formFactory;
        $this->formHandler = $formHandler;
        $this->formType = $formType;//dangerdanger
        $this->listFactory = $listFactory;
        $this->listType = $listType;

        $this->repository = $repository;
        $this->typeResolver = $typeResolver;
    }


   /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function listAction(ServerRequestInterface $request): ResponseInterface
    {
        // $debugBar = $this->getDebugBar();

        // $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        // $navData = $this->getNavigationData($currentPath);



        //var_dump($request);
        // $this->formType = $this->listType;
        //$tmpEnum = $this->feature->baseUrlEnum;
        $pageConfigKey = $this->scrap->getPageConfigKey();

        // $tmp = $tmpEnum->data()['view'];
        $xpl = explode('_', $pageConfigKey);
        // $pageNm   = $xpl[0] . '_' . $xpl[1];
        $entityNm = $xpl[0]; // hack dangerdanger - i do not like how i get table name

        $this->listType->setFocus(
            $pageConfigKey,
            $entityNm
        );




        $options            = $this->listType->getOptions() ?? [];
        $renderOptions      = [];
        $paginationOptions  = $this->listType->getPaginationOptions() ?? [];
        $listFields         = $this->listType->getFields() ?? [];







        $filter = (string)($request->getQueryParams()['filter'] ?? "DDDD");

        $this->scrap->setRouteType('store');
        $storeId = 4;// $this->scrap->getStoreId();//fixme


        //$EE = $this->feature->baseUrlEnum;

        $url = $this->feature->baseUrlEnum;


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
        if ($filter === "user") {
            $userId = $this->scrap->getUserId();
            // $records = $this->repository->findByUserId($userId,
            //                                              [$sortField => $sortDirection], $limit, $offset);
            $records = $this->repository->findByUserIdWithFields(
                $userId,
                [$sortField => $sortDirection],
                $limit,
                $offset
            );

            $totalRecords = $this->repository->countByUserId($userId);
        } else {
            //$recordsxx = $this->repository->findByStoreId($storeId, [$sortField => $sortDirection], $limit, $offset);
            $records = $this->repository->findByStoreIdWithFields(
                $storeId,
                $listFields,
                [$sortField => $sortDirection],
                $limit,
                $offset
            );


            $totalRecords = $this->repository->countByStoreId($storeId);
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

        return $this->view(Url::CORE_TESTY_LIST->view(), $this->buildCommonViewData($viewData));
    }




    /**
     * Edit an existing record. Handles standard GET and POST requests.
     *
     * @param ServerRequestInterface $request The incoming server request.
     * @return ResponseInterface The response, either rendering the view or redirecting.
     */
    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
        // $tmpEnum = $this->feature->editUrlEnum;

        // $tmp = $tmpEnum->data()['view'];
        // $xpl = explode('/', $tmp);
        // $pageNm   = $xpl[0] . '_' . $xpl[1];
        // $entityNm = $xpl[0];

        // $this->formType->setFocus(
        //     $pageNm,
        //     $entityNm
        // );

        $pageConfigKey = $this->scrap->getPageConfigKey();

        $xpl = explode('_', $pageConfigKey);
        $entityNm = $xpl[0]; // hack dangerdanger - i do not like how i get table name

        $this->formType->setFocus(
            $pageConfigKey,
            $entityNm
        );




        // $this->formType->setFocus(
        //     $this->feature->pageName ?? 'testy_edit',
        //     $this->feature->entityName ?? 'testy',
        // );


        $this->overrideFormTypeRenderOptions();


       // $url = $this->feature->baseUrlEnum;




        $recordId = isset($this->route_params['id']) ? (int)$this->route_params['id'] : null;
        if ($recordId === null) {
            $this->flash22->add("Invalid record ID.", FlashMessageType::Error);
            // return $this->redirect(Url::CORE_TESTY->url());
            return $this->redirect($this->feature->baseUrlEnum->view());
        }



        // 1. Define all columns needed for this request (form fields + permission fields).
        // $formFields = $this->formType->getFields();
        $formFields = $this->formType->getFields();

        $ownerForeignKey = $this->feature->ownerForeignKey;
        $requiredFields = array_unique(array_merge($formFields, [$ownerForeignKey]));

        if ($request->getMethod() === 'GET') {
            // 2. Fetch the required data ONCE as an array.
            $recordArray = $this->repository->findByIdWithFields($recordId, $requiredFields);

            // 3. Check for existence and permissions using the fetched array.
            $this->checkForEditPermissions($recordArray);
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
            //'geo_region' => $regionCode, // <-- Add this line
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
        //$pageKey = $this->scrap->getPageKey();
        $pageConfigKey = $this->scrap->getPageConfigKey();

        $xpl = explode('_', $pageConfigKey);
        $entityNm = $xpl[0]; // hack dangerdanger - i do not like how i get table name

        $this->formType->setFocus(
            $pageConfigKey,
            $entityNm
        );





        $this->overrideFormTypeRenderOptions();

        // $formType2 = $this->typeResolver->resolveFormType('testy');
        // $this->formType = $formType2;


        // 1. Define all columns needed for this request (form fields + permission fields).
        // $formFields = $this->formType->getFields();
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
            $currentUserId = 4; // Hack because not logged in
            $data[$ownerForeignKey] = $currentUserId;
            $data['store_id'] = $currentUserId; // Hack because not logged in



            // Slug regeneration if needed
            if (isset($data['title'])) {
                // Regenerate slug if the current slug was auto-generated before or no slug supplied
                $data['slug'] = $this->generateSlug((string)$data['title']);
            } else {
                $data['title'] = $this->generateTitle(3, 6);
                // protected function generateTitle(int $wordCount = 2, int $wordLength = 6): string

                $data['slug'] = $this->generateSlug((string)$data['title']);
            }

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
            return $this->feature->baseUrlEnum->url();
        }

        return $this->feature->editUrlEnum->url(['id' => $recordId]);
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
        $currentUserId = 4; //hack because i am not logged in

        // 3. Get the owner foreign key from the metadata service (e.g., 'user_id', 'user_id')
        $ownerForeignKey = $this->feature->ownerForeignKey;

        // 4. Compare the record's owner ID with the current user's ID.
        if (!isset($recordArray[$ownerForeignKey]) || $recordArray[$ownerForeignKey] !== $currentUserId) {
            // 5. If they don't match or the key isn't present, throw an exception.
            throw new ForbiddenException("You do not have permission to edit this record.", 403);
        }
    }
}
