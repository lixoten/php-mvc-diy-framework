<?php

declare(strict_types=1);

namespace App\Features\Stores\Profile;

use App\Enums\FlashMessageType;
use App\Features\Stores\Profile\Form\ProfileFormType;
use App\Helpers\DebugRt;
use App\Repository\StoreRepositoryInterface;
use Core\Controller;
use Core\View;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Constants\Consts;
use Core\Constants\Urls;
use Core\Context\CurrentContext;
use Core\Form\FormFactoryInterface;
use Core\Form\FormHandlerInterface;
use Core\Http\HttpFactory;
use PharIo\Manifest\Url;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * MyStore controller
 */
class ProfileController extends Controller
{
    private FormFactoryInterface $formFactory;
    private FormHandlerInterface $formHandler;

    private StoreRepositoryInterface $storeRepository;
    private ProfileFormType $profileFormType;
    /**
     * Constructor
     */
    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        CurrentContext $scrap,
        FormFactoryInterface $formFactory,
        FormHandlerInterface $formHandler,
        StoreRepositoryInterface $storeRepository,
        ProfileFormType $profileFormType,
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
        $this->storeRepository = $storeRepository;
        $this->profileFormType = $profileFormType;
    }

    // /**
    //  * Show the Store index page
    //  */
    // public function indexAction(): ResponseInterface
    // {
    //     return $this->view('stores/index', [
    //         'title' => 'Store'
    //     ]);
    // }

    // In StoreController.php
    public function indexAction(): ResponseInterface
    {
        //DebugRt::j('1', '', 111);
        // Get current user's store
        // $currentUser =  $this->authService->getCurrentUser();
        // $currentUser =  $this->getCurrentUser();
        $currentUserId = $this->getCurrentUserId();
        //$store = $this->storeRepository->findByUserId($currentUser->getUserId());
        $store = $this->storeRepository->findByUserId($currentUserId);

        if (!$store) {
            $this->flash->add('You do not have a store yet.', FlashMessageType::Warning);
            // return $this->redirect('/home');
            return $this->redirect(Urls::NO_STORES);
        }

        // Get store statistics
        $productCount = 22; //$this->productRepository->countByStoreId($store->getId());
        $orderCount = 1; //$this->orderRepository->countByStoreId($store->getId());
        $recentOrders = []; //$this->orderRepository->findRecentByStoreId($store->getId(), 5);

        // Render the dashboard view
        return $this->view(ProfileConst::VIEW_PROFILE_INDEX, [
            'title' => 'Store Profile Placeholder',
            'store' => $store,
            'productCount' => $productCount,
            'orderCount' => $orderCount,
            'recentOrders' => $recentOrders
        ]);
    }


    /**
     * Show the Store create page
     */
    public function createAction(ServerRequestInterface $request): ResponseInterface
    {

        //DebugRt::j('1', '', 111);
        // // Form creation and handling logic
        // $form = $this->formFactory->create(
        //     StoreFormType::class,
        //     [],
        //     ['ip_address' => $this->getIpAddress()]
        // );

        // // Process form submission
        // $formHandled = $this->formHandler->handle($form, $this->request);
        // if ($formHandled && $form->isValid()) {
        //     // Create new store entity
        //     // Assign the store to current user
        //     // Update user role to store_owner
        //     // Redirect to store dashboard
        // }


        // Get the current user ID - assuming you have an auth service
        $userId = $this->getCurrentUserId();

        $form = $this->formFactory->create(
            $this->profileFormType,
            [],
            [
                // 'force_captcha' => $this->isForcedCaptcha(), // fix-force-captcha '';
                'ip_address' => $this->getIpAddress(),
                // Notes-: There below could be set here, but don't. Use the appropriate FormType
                // 'error_display' => 'inline',
                // 'submit_text' => 'sssssaaaaaaaaaaaaaSubmit',
                // 'css_form_theme_class' => "form-theme-christmas",
                // 'form_heading' => 'Send us a message',
                // 'html5_validation' => false,
                // 'css_form_theme_file' => 'christmas',
            ]
        );
        $formTheme = $form->getCssFormThemeFile();

        // Process form submission
        $formHandled = $this->formHandler->handle($form, $request);
        if ($formHandled && $form->isValid()) {
            $data = $form->getData();

            // Example: log the submission
            //.........................$this->logger->info('Contssact form submitted (direct rendering)', $data);
            // Prepare data for database insertion
            // $storeData = [
            //     'store_user_id' => $userId,
            //     'store_name' => $this->generateSlug($data['title']),
            //     'store_status' => 'P', // Published by default
            //     'name' => $data['name'],
            //     'description' => $data['description'],
            //     // timestamps will be added automatically by the database
            // ];

            try {
                // Create a Store entity from the form data
                $store = new \App\Entities\Store();
                $store->setUserId($userId);
                $store->setStoreStatus('P'); // Published by default
                $store->setSlug($this->generateSlug($data['title']));
                $store->setName($data['name']);
                $store->setDescription($data['content']);

                // Insert the Store into the database
                $storeId = $this->storeRepository->create($store);

                if ($storeId) {
                    // Add success flash message
                    $this->flash->add("Your Store has been created successfully", FlashMessageType::Success);

                    // Redirect to the store list
                    return $this->redirect('/storesccccccc'); // TODO fix
                } else {
                    // Handle failed insertion
                    $form->addError('_form', 'Failed to create your store. Please try again.');
                }
            } catch (\Exception $e) {
                // Add error message to form
                $form->addError('_form', 'An error occurred: ' . $e->getMessage());
            }
        }

        // Prepare view data - pass the form directly instead of FormView
        $viewData = [
            'title' => 'Create New Store',
            'form' => $form,
            'formTheme' => $formTheme // if $formTheme isset/used
        ];

        // Create response with appropriate status code
        $response = $this->view(ProfileConst::VIEW_PROFILE_CREATE, $viewData);

        // Set 422 Unprocessable Entity status for form failures
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;

       // // Render the form view
        // return $this->view('stores/create', [
        //     'title' => 'Create Your Store',
        //     // 'form' => $form
        // ]);
    }


    /**
     * Update basic store information
     */
    public function editAction(): ResponseInterface
    {
        // Update store details
        return $this->view('', []); // placeholder
        return $this->view(ProfileConst::VIEW_PROFILE_EDIT, [
            'title' => 'Store Dashboard',
            'store' => $store,
            'productCount' => $productCount,
            'orderCount' => $orderCount,
            'recentOrders' => $recentOrders
        ]);

    }

    /**
     * Manage store branding
     */
    public function brandingAction(): ResponseInterface
    {
        // Logo, colors, theme settings
        return $this->view('', []); // placeholder
    }

    /**
     * Configure store payment methods
     */
    public function paymentsAction(): ResponseInterface
    {
        // Payment gateway settings
        return $this->view('', []); // placeholder
    }

    /**
     * Set up shipping options
     */
    public function shippingAction(): ResponseInterface
    {
        // Shipping configuration
        return $this->view('', []); // placeholder
    }

    /**
     * Manage store staff/team members
     */
    public function teamAction(): ResponseInterface
    {
        // Add/remove/edit staff accounts
        return $this->view('', []); // placeholder
    }


    /**
     * Get the current user ID
     */
    private function getCurrentUserId(): int // TODO - needs to move maybe to crontroller? duplicate code
    {
        // This is a placeholder - replace with your actual authentication logic
        // Typically you would get this from an auth service
        // Example: return $this->authService->getCurrentUser()->getId();

        // For now, return a default user ID (1)
        //return 1;


        /** @var \Core\Auth\AuthenticationServiceInterface $authService */
        $authService = $this->container->get(\Core\Auth\AuthenticationServiceInterface::class);

        if (!$authService->isAuthenticated()) {
            throw new \RuntimeException('User must be logged in to perform this action');
        }

        $currentUser = $authService->getCurrentUser();
        if (!$currentUser) {
            throw new \RuntimeException('Unable to retrieve current user');
        }
        //DebugRt::j('1', '', $currentUser->getUserId());
        return $currentUser->getUserId();
    }
}
