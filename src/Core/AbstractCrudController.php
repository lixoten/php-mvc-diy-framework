<?php

declare(strict_types=1);

namespace Core;

use App\Enums\FlashMessageType;
use App\Enums\Url;
use App\Features\Testys\Form\TestysFormType;
use App\Repository\TestyRepositoryInterface;
use App\Services\FeatureMetadataService;
use Core\Context\CurrentContext;
use Core\Controller;
use Core\Exceptions\ForbiddenException;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Container\ContainerInterface;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Form\FormFactoryInterface;
use Core\Form\FormHandlerInterface;
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
    protected TestysFormType $formType;
    protected TestyRepositoryInterface $repository;

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
        // Add the service to the constructor
        FeatureMetadataService $feature,
        FormFactoryInterface $formFactory,
        FormHandlerInterface $formHandler,
        TestysFormType $formType,
        TestyRepositoryInterface $repository,
    ) {
        parent::__construct($route_params, $flash, $view, $httpFactory, $container, $scrap);
        $this->feature = $feature;
        $this->formFactory = $formFactory;
        $this->formHandler = $formHandler;
        $this->formType = $formType;
        $this->repository = $repository;
    }


    /**
     * Edit an existing record. Handles standard GET and POST requests.
     *
     * @param ServerRequestInterface $request The incoming server request.
     * @return ResponseInterface The response, either rendering the view or redirecting.
     */
    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
        $recordId = isset($this->route_params['id']) ? (int)$this->route_params['id'] : null;
        if ($recordId === null) {
            $this->flash22->add("Invalid record ID.", FlashMessageType::Error);
            // return $this->redirect(Url::CORE_TESTY->url());
            return $this->redirect($this->feature->baseUrlEnum->view());
        }

        // 1. Define all columns needed for this request (form fields + permission fields).
        $formFields = $this->formType->getFormFields();
        $ownerForeignKey = $this->feature->ownerForeignKey;
        $requiredFields = array_unique(array_merge($formFields, [$ownerForeignKey])); // Add user_id for permission check
        // 2. Fetch the required data ONCE as an array.
        $recordArray = $this->repository->findByIdWithFields($recordId, $requiredFields);

        // 3. Check for existence and permissions using the fetched array.
        $this->checkForEditPermissions($recordArray);

        // 4. Pass the fetched array to the form processor.
        $result = $this->processForm($request, $recordArray);
        $form   = $result['form'];

        // Prepare the form for JavaScript
        ///$form->setAttribute('data-ajax-action', '/testys/edit/' . $recordId . '/update');
        $form->setAttribute(
            'data-ajax-action',
            $this->feature->editUrlEnum->url(['id' => $recordId]) . '/update'
        );
        //$form->setAttribute('data-ajax-action', '/testys/edit/' . $recordId . '/update');
        //$form->setAttribute('data-ajax-save', 'true');

        // This block handles the submission AFTER the form has been processed
        if (
            $result['handled']
            && $result['valid']
            && $request->getHeaderLine('X-Requested-With') !== 'XMLHttpRequest'
        ) {
            $data = $form->getUpdatableData();

            if ($this->repository->updateFields($recordId, $data)) {
                $this->flash22->add("Record updated successfully", FlashMessageType::Success);
                // return $this->redirect(Url::CORE_TESTY->c());
                return $this->redirect($this->getRedirectUrlAfterSave($recordId));
                //return $this->redirectAfterSave($recordId);
            } else {
                $form->addError('_form', 'Failed to update the record in the database.');
            }
        }

        // This block handles the initial page load (GET) or a failed submission
        $viewData = [
            'title' => 'Edit Record',
            'form' => $form,
            'formTheme' => $form->getCssFormThemeFile()
        ];

        // $response = $this->view(Url::CORE_TESTY_EDIT->view(), $viewData);
        $url = $this->feature->editUrlEnum;
        $response = $this->view($url->view(), $viewData);

        // If the form has errors (on a failed POST), return a 422 status code
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;
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
        //DebugRt::j('1', '', 'boom');
        try {
            $recordId = isset($this->route_params['id']) ? (int)$this->route_params['id'] : null;
            if ($recordId === null) {
                return $this->json(['success' => false, 'message' => 'Record ID is missing.'], 400);
            }

            // 1. Define all columns needed.
            $formFields = $this->formType->getFormFields();
            $requiredFields = array_unique(array_merge($formFields, ['testy_user_id']));

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

        // 3. Get the owner foreign key from the metadata service (e.g., 'testy_user_id', 'post_user_id')
        $ownerForeignKey = $this->feature->ownerForeignKey;

        // 4. Compare the record's owner ID with the current user's ID.
        if (!isset($recordArray[$ownerForeignKey]) || $recordArray[$ownerForeignKey] !== $currentUserId) {
            // 5. If they don't match or the key isn't present, throw an exception.
            throw new ForbiddenException("You do not have permission to edit this record.", 403);
        }
    }
}
