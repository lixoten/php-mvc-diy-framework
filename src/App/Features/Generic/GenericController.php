<?php

declare(strict_types=1);

// namespace App\Features\Admin\Generic;
namespace App\Features\Generic;

use App\Helpers\DebugRt;
use App\Enums\FlashMessageType;
use App\Enums\Url;
// use App\Features\Admin\Generic\Form\GenericFormType;
// use App\Features\Admin\Generic\Form\GenericFieldRegistry;
// use App\Features\Admin\Generic\List\GenericColumnRegistry;
// use App\Features\Admin\Generic\List\GenericListType;
use App\Features\Generic\Form\GenericFormType;
use App\Features\Generic\Form\GenericFieldRegistry;
use App\Features\Generic\List\GenericColumnRegistry;
use App\Features\Generic\List\GenericListType;
use App\Repository\PostRepositoryInterface;
use App\Repository\RepositoryRegistryInterface;
use Core\Controller;
use App\Services\Interfaces\FlashMessageServiceInterface;
use App\Services\Interfaces\GenericDataServiceInterface;
use Core\Exceptions\RecordNotFoundException;
use Core\Form\FormFactoryInterface;
use Core\Form\FormHandlerInterface;
use Core\Http\HttpFactory;
use Core\List\ListFactory;
use Core\List\ListFactoryInterface;
use Core\Traits\AuthorizationTrait;
use Core\Traits\EntityNotFoundTrait;
use Core\View;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Core\Auth\AuthenticationServiceInterface;
use Core\Context\CurrentContext;

/**
 * generics controller
 *
 */
class GenericController extends Controller
{
    use AuthorizationTrait;
    use EntityNotFoundTrait;

    private FormFactoryInterface $formFactory;
    private FormHandlerInterface $formHandler;

    private GenericFormType $genericFormType;

    private GenericDataServiceInterface $dataService;
    private ListFactoryInterface $listFactory;
    private GenericListType $genericListType;
    private GenericColumnRegistry $columnRegistry;
    private GenericFieldRegistry $fieldRegistry;
    private PostRepositoryInterface $postRepository;
    private RepositoryRegistryInterface $repositoryRegistry;
    private AuthenticationServiceInterface $authService;

    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        CurrentContext $scrap,
        //-----------------------------------------
        // private FeatureMetadataService $featureMetadataService,
        FormFactoryInterface $formFactory,
        FormHandlerInterface $formHandler,
        GenericFormType $genericFormType,
        GenericDataServiceInterface $dataService,

        ListFactory $listFactory,
        GenericListType $genericListType,
        GenericColumnRegistry $columnRegistry,
        GenericFieldRegistry $fieldRegistry,//<<<<<<<<<<<<<<<<<<<<<
        PostRepositoryInterface $postRepository,
        RepositoryRegistryInterface $repositoryRegistry,
        AuthenticationServiceInterface $authService,
    ) {
        parent::__construct(
            $route_params,
            $flash,
            $view,
            $httpFactory,
            $container,
            $scrap
        );
        $this->formFactory = $formFactory;
        $this->formHandler = $formHandler;
        $this->genericFormType = $genericFormType;
        $this->dataService = $dataService;
        $this->listFactory = $listFactory;
        $this->genericListType = $genericListType;
        $this->columnRegistry = $columnRegistry;
        $this->fieldRegistry = $fieldRegistry;
        $this->postRepository = $postRepository;
        $this->repositoryRegistry = $repositoryRegistry;
        $this->authService = $authService;
    }


    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function indexAction(): ResponseInterface
    {
        // Get the page key from route params
        // $entityType = $this->route_params['page_name'] ?? null;
        $entityType = $this->scrap->getPageKey(); // Use context

        if ($entityType === null) {
            throw new \RuntimeException("Entity type (page_name) not provided in route parameters.");
        }

        // --- Get Fields from Column Registry ---
        $fields = $this->columnRegistry->getDisplayColumnsForEntity($entityType);
        if (empty($fields)) {
            // Handle case where 'display' array is missing or empty in config
            // throw new \RuntimeException("Display columns not configured for entity type: " . htmlspecialchars($entityType));
            //throw new \Core\Exceptions\PageNotFoundException("Method name does not exist");
            throw new \Core\Exceptions\ConfigurationException(
                "Display columns not configured for entity type: " . htmlspecialchars($entityType),
                "list_columns.php",
                $entityType,
                "Add an 'entities.{$entityType}.display' array to your list_columns.php configuration file."
            );
        }

        // Get the records with pagination
        $page = isset($this->route_params['page']) ? (int)$this->route_params['page'] : 1;
        $limit = 10;
        //$offset = ($page - 1) * $limit;

        // Define sorting (optional)
        $orderBy = ['created_at' => 'DESC'];

        // --- Determine Criteria ---
        $criteria = []; // Default: fetch all (e.g., for admin)

        // Example: If the entity type is 'posts' and the user is NOT an admin,
        // filter by the current user's ID.
        // This requires knowing the current user and their roles.
        $currentUser = $this->scrap->getCurrentUser();
        $isUserAdmin = $this->scrap->isAdmin();

        if ($entityType === 'posts' && $currentUser && !$isUserAdmin) {
            // Assuming your Post entity has a 'userId' property/column
            //$criteria['userId'] = $currentUser->getUserId();
            //DebugRt::j('1', '', $criteria['userId']);
        }

        // Fetch data using the GenericDataService
        $listData = $this->dataService->fetchListData(
            $entityType,
            $fields,
            $criteria, // Pass the criteria array
            $page,
            $limit,
            $orderBy
        );

        $itemRecords = $listData['items'];
        $totalRecords = $listData['totalRecords'];
        $totalPages = $listData['totalPages'];

        // Create our list using the list factory
        $list = $this->listFactory->create(
            $this->genericListType,
            $itemRecords, // Use the mapped items directly
            [
                'entity_type' => $entityType,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_items' => $totalRecords,
                    'per_page' => $limit
                ],
                'render_options' => [ //dangerdanger posts hardcoded
                    'pagination_url' => Url::STORE_POST->paginationUrl() // Example, adjust if needed // DANGER
                ]
            ]
        );


        // return $this->view('admin/generic/index', [
        // return $this->view('generic/index', [
        return $this->view(Url::GENERIC->view(), [
            'title' => 'Generic.. but generic what?',
            'list' => $list,
        ]);
    }


    /**
     * Edit an existing post
     */
    public function editAction(ServerRequestInterface $request): ResponseInterface
    {

        //$entityType = $this->route_params['page_name'] ?? null;
        $entityType = $this->scrap->getPageKey(); // Use context
        $routePath = $this->scrap->getRouteTypePath();

        $entityId     = isset($this->route_params['id']) ? (int)$this->route_params['id'] : null;

        if ($entityType === null) {
            throw new RuntimeException("Entity type (page_name) not provided in route parameters.");
        }
        if (!$entityId) {
            //throw new RecordNotFoundException("Missing ID for editing.");
            $this->throwEntityNotFound($entityType, $entityId); // Use generic helper
        }

        // --- Get Entity using GenericDataService ---
        $entity = $this->dataService->fetchEntityById($entityType, $entityId);
        if (!$entity) {
            $this->throwEntityNotFound($entityType, $entityId);
        }

        // --- Authorization (Example - Adapt as needed) ---
        // ... (keep authorization logic, using $entity) ...
        if (method_exists($entity, 'getUserId') && !$this->isUserAuthorized($entity->getUserId())) {
            $this->flash->add("You don't have permission to edit this " . $entityType, FlashMessageType::Error);
            return $this->redirect($routePath . $entityType);
        }

        // --- Prepare Initial Form Data ---
        // Get the list of fields expected for this entity's form
        $formFields = $this->fieldRegistry->getFieldsForEntity($entityType);
        $initialData = [];
        foreach ($formFields as $fieldName) {
            // Generate standard getter name (e.g., 'title' -> 'getTitle', 'created_at' -> 'getCreatedAt')
            $getter = 'get' . str_replace('_', '', ucwords($fieldName, '_'));
            // Handle specific cases if your getters don't follow the standard pattern
            if ($fieldName === 'id') {
                 $getter = 'getRecordId'; // Example: Adjust if your ID getter is different
            }
            // Add other specific getter adjustments if needed

            if (method_exists($entity, $getter)) {
                $initialData[$fieldName] = $entity->$getter();
            } else {
                // Field exists in config but no getter on entity? Set to null or log warning.
                $initialData[$fieldName] = null;
                // trigger_error("Getter method '$getter' not found on entity for field '$fieldName' of type '$entityType'", E_USER_WARNING);
            }
        }
        // --- End Prepare Initial Form Data ---

        // Create the form with existing post data
        $form = $this->formFactory->create(
            $this->genericFormType,
            $initialData, // Pass initial data extracted from $entity
            [
                'content_type' => $entityType,
                'action_type' => $entityType . '_edit',
                'ip_address' => $this->getIpAddress(),
                'title_heading' => 'zzEdit ' . ucfirst($entityType),
                // 'submit_text' => 'zzUpdate ' . ucfirst($entityType),
            ]
        );
        $formTheme = $form->getCssFormThemeFile();

        // Process form submission
        $formHandled = $this->formHandler->handle($form, $request);
        if ($formHandled && $form->isValid()) {
            $data = $form->getData();


            // --- Update Entity ---
            // Populate the fetched entity object with validated form data
            foreach ($data as $fieldName => $value) {
                // Generate standard setter name (e.g., 'title' -> 'setTitle')
                $setter = 'set' . str_replace('_', '', ucwords($fieldName, '_'));
                 // Add specific setter adjustments if needed (e.g., setRecordId)

                if (method_exists($entity, $setter)) {
                    // Add any special handling before setting (e.g., slug generation)
                    // if ($fieldName === 'title' && method_exists($entity, 'setSlug')) {
                    //     $entity->setSlug($this->generateSlug($value));
                    // }
                    $entity->$setter($value);
                } else {
                    // Form submitted a field that has no setter? Log warning.
                    // trigger_error("Setter method '$setter' not found on entity for field '$fieldName' of type '$entityType'", E_USER_WARNING);
                }
            }
            // --- End Update Entity ---

            // --- Get Repository and Save ---
            $repository = $this->repositoryRegistry->getRepository($entityType); // Get repo via registry
            if (!method_exists($repository, 'update')) {
                 throw new RuntimeException("Repository for '$entityType' missing 'update' method.");
            }
            $success = $repository->update($entity); // Save the updated entity

            if ($success) {
                $this->flash->add(ucfirst($entityType) . " updated successfully", FlashMessageType::Success);
                return $this->redirect($routePath . $entityType);
            } else {
                $form->addError('_form', 'Failed to update ' . $entityType . '. Please try again.');
            }
        }

        // --- Prepare view data ---
        $viewData = [
            'title' => 'Edit ' . ucfirst($entityType),
            'entityType' => $entityType,
            'entity' => $entity, // Pass the fetched entity to the view
            'form' => $form,
            'formTheme' => $formTheme,
            'actionLinks' => $this->getActionLinks($entityType, ['index', 'add']),
        ];



        // DebugRt::j('1', '', 111);
        // Create response with appropriate status code
        //$response = $this->view(Url::STORE_POST_EDIT->view(), $viewData);
        // $response = $this->view('generic/edit', $viewData);
        $response = $this->view(Url::GENERIC_EDIT->view(), $viewData);

        // Set 422 Unprocessable Entity status for form failures
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;
    }


    /**
     * Edit an existing post
     */
    public function addAction(ServerRequestInterface $request): ResponseInterface
    {
        // DebugRt::j('1', '', 111);
        //$entityType = $this->route_params['page_name'] ?? null;
        $entityType = $this->scrap->getPageKey(); // Use context
        $routePath = $this->scrap->getRouteTypePath();

        //$entityId     = isset($this->route_params['id']) ? (int)$this->route_params['id'] : null;

        if ($entityType === null) {
            throw new RuntimeException("Entity type (page_name) not provided in route parameters.");
        }
        //if (!$entityId) {
        //    //throw new RecordNotFoundException("Missing ID for editing.");
        //    $this->throwEntityNotFound($entityType, $entityId); // Use generic helper
        //}

        // --- Get Entity using GenericDataService ---
        //$entity = $this->dataService->fetchEntityById($entityType, $entityId);
        //if (!$entity) {
        //    $this->throwEntityNotFound($entityType, $entityId);
        //}

        // --- Authorization (Example - Adapt as needed) ---
        // ... (keep authorization logic, using $entity) ...
        //if (method_exists($entity, 'getUserId') && !$this->isUserAuthorized($entity->getUserId())) {
        //    $this->flash->add("You don't have permission to edit this " . $entityType, FlashMessageType::Error);
        //    return $this->redirect($routePath . $entityType);
        //}

        // Get current user ID
        $userId = $this->getCurrentUserId();

        // --- Prepare Initial Form Data ---
        // Get the list of fields expected for this entity's form
        $formFields = $this->fieldRegistry->getFieldsForEntity($entityType);
        $initialData = [];
        // foreach ($formFields as $fieldName) {
        //     // Generate standard getter name (e.g., 'title' -> 'getTitle', 'created_at' -> 'getCreatedAt')
        //     $getter = 'get' . str_replace('_', '', ucwords($fieldName, '_'));
        //     // Handle specific cases if your getters don't follow the standard pattern
        //     if ($fieldName === 'id') {
        //          $getter = 'getRecordId'; // Example: Adjust if your ID getter is different
        //     }
        //     // Add other specific getter adjustments if needed

        //     if (method_exists($entity, $getter)) {
        //         $initialData[$fieldName] = $entity->$getter();
        //     } else {
        //         // Field exists in config but no getter on entity? Set to null or log warning.
        //         $initialData[$fieldName] = null;
        //         // trigger_error("Getter method '$getter' not found on entity for field '$fieldName' of type '$entityType'", E_USER_WARNING);
        //     }
        // }
        // --- End Prepare Initial Form Data ---

        // Create the form with existing post data
        $form = $this->formFactory->create(
            $this->genericFormType,
            $initialData, // Pass initial data extracted from $entity
            [
                'content_type' => $entityType,
                'action_type' => $entityType . '_add',
                'ip_address' => $this->getIpAddress(),
                'title_heading' => 'zzAdd ' . ucfirst($entityType),
                // 'submit_text' => 'zzUpdate ' . ucfirst($entityType),
            ]
        );
        $formTheme = $form->getCssFormThemeFile();

        // Process form submission
        $formHandled = $this->formHandler->handle($form, $request);
        if ($formHandled && $form->isValid()) {
            $data = $form->getData();

            try {
                // Create new entity instance - use the repository registry to get the correct repository
                $repository = $this->repositoryRegistry->getRepository($entityType);

                // Create a new entity using reflection or a factory method in your data service
                // This is a simplification - you'll need to adapt this to your entity creation mechanism
                $entity = $this->dataService->createNewEntity($entityType);

                // Populate entity with form data
                foreach ($data as $fieldName => $value) {
                    $setter = 'set' . str_replace('_', '', ucwords($fieldName, '_'));

                    if (method_exists($entity, $setter)) {
                        // Special handling for certain fields
                        if ($fieldName === 'title' || $fieldName === 'name') {
                            // Generate slug from title/name if the method exists
                            if (method_exists($entity, 'setSlug')) {
                                $entity->setSlug($this->generateSlug($value));
                            }
                        }

                        // Set the actual value
                        $entity->$setter($value);
                    }
                }


                $rrrrr = $this->scrap->getStoreId();
                $rrr = $this->scrap->getRouteType();
                if ($this->scrap->isStoreRoute()) {
                    if (method_exists($entity, 'setPostStoreId')) {
                        $entity->setPostStoreId($this->scrap->getStoreId());
                    }
                }

                // Set user ID and creation timestamp if appropriate methods exist
                if (method_exists($entity, 'setUserId')) {
                    $entity->setUserId($userId);
                } elseif (method_exists($entity, 'setPostUserId')) {
                    $entity->setPostUserId($userId);
                } elseif (method_exists($entity, 'setAlbumUserId')) {
                    $entity->setAlbumUserId($userId);
                }

                if (method_exists($entity, 'setStatus')) {
                    $entity->setStatus('A'); // 'A' for Active
                } elseif (method_exists($entity, 'setPostStatus')) {
                    $entity->setPostStatus('A');
                } elseif (method_exists($entity, 'setAlbumStatus')) {
                    $entity->setAlbumStatus('A');
                }


                // Save to database
                $success = false;
                if (method_exists($repository, 'create')) {
                    $result = $repository->create($entity);
                    $success = $result !== null;
                }

                if ($success) {
                    $this->flash->add(ucfirst($entityType) . " created successfully", FlashMessageType::Success);
                    return $this->redirect($routePath . $entityType);
                } else {
                    $form->addError('_form', 'Failed to create ' . $entityType . '. Please try again.');
                }
            } catch (\Exception $e) {
                $form->addError('_form', 'Error: ' . $e->getMessage());
            }
            // // --- Update Entity ---
            // // Populate the fetched entity object with validated form data
            // foreach ($data as $fieldName => $value) {
            //     // Generate standard setter name (e.g., 'title' -> 'setTitle')
            //     $setter = 'set' . str_replace('_', '', ucwords($fieldName, '_'));
            //      // Add specific setter adjustments if needed (e.g., setRecordId)

            //     if (method_exists($entity, $setter)) {
            //         // Add any special handling before setting (e.g., slug generation)
            //         // if ($fieldName === 'title' && method_exists($entity, 'setSlug')) {
            //         //     $entity->setSlug($this->generateSlug($value));
            //         // }
            //         $entity->$setter($value);
            //     } else {
            //         // Form submitted a field that has no setter? Log warning.
            //         // trigger_error("Setter method '$setter' not found on entity for field '$fieldName' of type '$entityType'", E_USER_WARNING);
            //     }
            // }
            // --- End Update Entity ---

            // --- Get Repository and Save ---
            // $repository = $this->repositoryRegistry->getRepository($entityType); // Get repo via registry
            // if (!method_exists($repository, 'update')) {
            //      throw new RuntimeException("Repository for '$entityType' missing 'update' method.");
            // }
            // $success = $repository->update($entity); // Save the updated entity

            // if ($success) {
            //     $this->flash->add(ucfirst($entityType) . " updated successfully", FlashMessageType::Success);
            //     return $this->redirect($routePath . $entityType);
            // } else {
            //     $form->addError('_form', 'Failed to update ' . $entityType . '. Please try again.');
            // }
        }

        // --- Prepare view data ---
        $viewData = [
            'title' => 'addddddddddd ' . ucfirst($entityType),
            'entityType' => $entityType,
            'entity' => null, //$entity, // Pass the fetched entity to the view
            'form' => $form,
            'formTheme' => $formTheme,
            'actionLinks' => $this->getActionLinks($entityType, ['index', 'add']),
        ];



        // DebugRt::j('1', '', 111);
        // Create response with appropriate status code
        //$response = $this->view(Url::STORE_POST_EDIT->view(), $viewData);
        // $response = $this->view('generic/edit', $viewData);
        $response = $this->view(Url::GENERIC_EDIT->view(), $viewData);

        // Set 422 Unprocessable Entity status for form failures
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;
    }


    public function booAction(ServerRequestInterface $request): ResponseInterface
    {
        // return $this->view(Url::STORE_PzOSTS->view(), [
        return $this->view('admin/generic/boo', [
            'title' => 'xxxxx',
            'actionLinks' => $this->getActionLinks('posts', ['index']),
            // 'list' => $list,
        ]);
    }



    // // --- Helper Methods ---

    // /**
    //  * Get content type configuration from the registry.
    //  */
    // private function getContentTypeConfig(?string $contentTypeSlug): array
    // {
    //     if (!$contentTypeSlug || !$this->contentTypeRegistry->hasContentType($contentTypeSlug)) {
    //         throw new RecordNotFoundException("Content type '$contentTypeSlug' is not configured.");
    //     }
    //     return $this->contentTypeRegistry->getContentType($contentTypeSlug);
    // }


    // /**
    //  * Generate a slug from a string
    //  */
    // private function generateSlug(string $text): string
    // {
    //     // Convert to lowercase
    //     $slug = strtolower($text);

    //     // Replace spaces with hyphens
    //     $slug = str_replace(' ', '-', $slug);

    //     // Remove special characters
    //     $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);

    //     // Remove multiple hyphens
    //     $slug = preg_replace('/-+/', '-', $slug);

    //     // Trim hyphens from beginning and end
    //     $slug = trim($slug, '-');

    //     return $slug;
    // }
}
# 455 347 317
