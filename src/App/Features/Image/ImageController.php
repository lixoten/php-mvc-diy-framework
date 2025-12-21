<?php

/**
 * ImageController.php
 *
 * This file contains the ImageController class, which handles various actions
 * such as logging, session management, database testing, and email testing.
 * It is part of the Image feature in the application.
 *
 * @package App\Features\Image
 */

declare(strict_types=1);

namespace App\Features\Image;

use Core\Services\FormatterService;
use App\Helpers\DebugRt;
use App\Enums\FlashMessageType;
use App\Enums\Url;
use App\Services\Email\EmailNotificationService;
use App\Services\FeatureMetadataService;
use App\Services\Interfaces\FlashMessageServiceInterface;
use App\Services\PaginationService;
use Core\AbstractCrudController;
use Core\Context\CurrentContext;
use Core\Services\ConfigService;
use stdClass;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Core\Form\FormFactoryInterface;
use Core\Form\FormHandlerInterface;
use Core\Form\FormInterface;
use Core\Form\FormTypeInterface;
use Core\Form\Renderer\FormRendererInterface;
use Core\Formatters\TextFormatter;
use Core\Interfaces\ConfigInterface;
use Core\List\ListFactoryInterface;
use Core\List\ListTypeInterface;
use Core\Services\TypeResolverService;
use Psr\Log\LoggerInterface;
use Core\List\Renderer\ListRendererInterface;
use Core\Services\BaseFeatureService;
use Core\Services\ReturnUrlManagerServiceInterface;
use Core\View\ViewFactoryInterface;
use Core\View\ViewTypeInterface;
use Core\View\Renderer\ViewRendererInterface;

/**
 * Image controller
 *
 */
class ImageController extends AbstractCrudController
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
        FormFactoryInterface $formFactory, // 1
        FormHandlerInterface $formHandler, //
        FormTypeInterface $formType,       // 2
        ListFactoryInterface $listFactory, // 1
        ListTypeInterface $listType,       // 2
        ViewFactoryInterface $viewFactory,
        ViewTypeInterface $viewType,
        ImageRepositoryInterface $repository,
        TypeResolverService $typeResolver,
        ListRendererInterface $listRenderer,
        FormRendererInterface $formRenderer,
        ViewRendererInterface $viewRenderer,
        BaseFeatureService $baseFeatureService,
        //-----------------------------------------
        protected ConfigInterface $config,
        protected LoggerInterface $logger,
        protected EmailNotificationService $emailNotificationService,
        private PaginationService $paginationService,
        private FormatterService $formatter,
        //-----------------------------------------
        ReturnUrlManagerServiceInterface $returnUrlManager,
        protected ImageService $imageService,
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
            $viewFactory,
            $viewType,
            $repository,
            $typeResolver,
            $listRenderer,
            $formRenderer,
            $viewRenderer,
            $baseFeatureService,
            //-----------------------------------------
            $returnUrlManager,
        );
        // constructor uses promotion php8+
        $this->imageService = $imageService;
    }

    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        $viewData = [
            'title' => 'Placeholder Action Page',
            'actionLinks' => [],
        ];

        // return $this->view(Url::CORE_IMAGE_LIST->view(), $this->buildCommonViewData($viewData));


        return parent::listAction(request: $request);
        // $viewData = [
        //     'title' => 'Image Index Action',
        //     'actionLinks' => $this->getReturnActionLinks(),
        // ];

        // return $this->view(Url::CORE_IMAGE->view(), $this->buildCommonViewData($viewData));
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
     * Overrides the default saveRecord method to delegate complex image data processing
     * (including file metadata updates) to the ImageService.
     *
     * @param array<string, mixed> $updatableData The validated and filtered data (e.g., from form->getUpdatableData()).
     * @param array<string, mixed> $extraProcessData The complete validated data from the form, including any auxiliary data (e.g., from form->getData()).
     * @param int|null $id The ID of the record to update, or null for a new record.
     * @return int The ID of the created or updated record.
     */
    protected function saveRecord(array $updatableData, array $extraProcessData, ?int $id = null): ?int
    {
        $fullFormData = $updatableData + $extraProcessData;

        // ✅ NEW: Get storeId from the reliable CurrentContext
        $currentStoreId = $this->scrap->getStoreId();

        // Delegate the complex image data processing (including metadata) to ImageService.
        // ImageService will need the full form data to find '_image_metadata_filename'.
        return $this->imageService->processImageData($fullFormData, $currentStoreId, $id);
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
            Url::CORE_IMAGE,
            Url::CORE_IMAGE_LIST,
            Url::CORE_IMAGE_CREATE,
            Url::CORE_IMAGE_EDIT,
        );
    }


    public function xxxeditAction(ServerRequestInterface $request): ResponseInterface
    {
        // $url = $this->feature->baseUrlEnum;
        // $url = Url::CORE_IMAGE;
        // $rrr = $url->action();
        // $rrr = $url->getSection('CORE');
        // $rrr = $url->url();
        // $rrr = $this->scrap();
        // $rrr = $url->action();
        // $rrr = $url->action();
        // $rrr = $url->action();

        return parent::editAction(request: $request);
    }





    public function viewAction(ServerRequestInterface $request): ResponseInterface
    {
        return parent::viewAction(request: $request);
    }


    /** {@inheritdoc} */
    protected function overrideFormTypeRenderOptions(?array $recordData = null): void
    {
        // findme - override field
        $options = []; // Initialize options array

        // Logic to determine if a filename exists for an existing record
        $currentFilenameExists = false;
        // ✅ Check if recordData exists and has a 'filename' key with a non-null value
        if ($recordData !== null && isset($recordData['filename']) && $recordData['filename'] !== null) {
            $currentFilenameExists = true;
        }

        // Set the override for the 'filename' field
        $options['options']['form_field_overrides']['filename'] = [
            'current_filename_exists' => $currentFilenameExists,
            'value_override'          => $recordData['original_filename']
        ];

        $this->formType->overrideConfig(options: $options);
    }


    /** {@inheritdoc} */
    protected function overrideViewTypeRenderOptions(): void
    {
        $this->viewType->overrideConfig(options: []);
    }


    /** {@inheritdoc} */
    protected function overrideListTypeRenderOptions(): void
    {
        // By default, no overrides are applied.
        // If you need to override list options, uncomment and use this:
        /*
        $options = [
            'options' => [
                'default_sort_key' => 'title',
                'default_sort_direction' => 'ASC',
            ],
            'pagination' => [
                'per_page' => 10,
            ],
            'render_options' => [
                'title' => 'Custom Image List Title',
                'show_action_edit' => false,
            ],
            // 'list_fields' => ['id', 'title', 'created_at'], // Override displayed fields
        ];
        $this->listType->overrideConfig(options: $options);
        */
        // Or, for an empty override:
        $this->listType->overrideConfig(options: []); // Call with empty array if no overrides
    }


    /**
     * Handles updating a resource via an AJAX request.
     * Responds to POST /image/edit/{id}/update
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
        //     'title' => 'Create New Image',
        //     'actionLinks' => $this->getReturnActionLinks(),
        // ];

        // return $this->view(Url::CORE_IMAGE_CREATE->view(), $viewData);
    }

    public function placeHolderAction(ServerRequestInterface $request): ResponseInterface
    {
        $viewData = [
            'title' => 'Placeholder Action Page',
            'actionLinks' => $this->getReturnActionLinks(),
        ];

        return $this->view(Url::CORE_IMAGE_PLACEHOLDER->view(), $this->buildCommonViewData($viewData));
    }



}
## 697 764 804 403 372
