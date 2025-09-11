<?php

declare(strict_types=1);

namespace App\Features\Albums;

use App\Helpers\DebugRt;
use App\Enums\FlashMessageType;
use App\Enums\Url;
use App\Features\Albums\Form\AlbumsFormType;
use App\Features\Albums\List\AlbumsListType;
// use App\Features\Albums\Form\AlbumsFormType;
// use App\Features\Albums\List\AlbumsListType;
use App\Features\Albums\AlbumsConst;
use App\Repository\AlbumRepositoryInterface;
use Core\Controller;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Auth\AuthenticationServiceInterface;
use Core\Constants\Urls;
use Core\Context\CurrentContext;
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

/**
 * Albums controller
 *
 */
class AlbumsController extends Controller
{
    use AuthorizationTrait;
    use EntityNotFoundTrait;

    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        CurrentContext $scrap,

        private FormFactoryInterface $formFactory,
        private FormHandlerInterface $formHandler,
        private AlbumRepositoryInterface $albumRepository,
        private AlbumsFormType $albumsFormType,
        private ListFactory $listFactory,
        private AlbumsListType $albumsListType,
        private AuthenticationServiceInterface $authService,
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
        $this->albumRepository = $albumRepository;
        $this->albumsFormType = $albumsFormType;
        $this->listFactory = $listFactory;
        $this->albumsListType = $albumsListType;
        $this->authService = $authService;
    }


    /**
     * Show the Albums index page
     */
    public function indexActionxxx(): ResponseInterface
    {
        //$filter = (string)($request->getQueryParams()['filter'] ?? "DDDD");
        //$storeId = $this->request->getAttribute('store_id');
        $storeId = $this->scrap->getStoreId();
        $routeType = $this->scrap->getRouteType();
        // $eee = $this->postRepository->`
        if ($routeType === 'account') {
            $filter = 'user';
            $url = Url::ACCOUNT_ALBUMS;
        } elseif ($routeType === 'store') {
            $filter = 'store';
            $url = Url::STORE_ALBUMS;
        } else {
            $filter = 'user';
            $url = Url::ACCOUNT_ALBUMS;
        }


        // return $this->view(Url::STORE_ALBUMS->view(), [
        return $this->view($url->view(), [
            'title' => 'Local Albums Placeholder'
        ]);
    }

    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function indexAction(): ResponseInterface
    {
        //$filter = (string)($request->getQueryParams()['filter'] ?? "DDDD");
        //$storeId = $this->request->getAttribute('store_id');
        $storeId = $this->scrap->getStoreId();
        $routeType = $this->scrap->getRouteType();
        // $eee = $this->postRepository->`
        if ($routeType === 'account') {
            $filter = 'user';
            $url = Url::ACCOUNT_ALBUMS;
        } elseif ($routeType === 'store') {
            $filter = 'store';
            $url = Url::STORE_ALBUMS;
        } else {
            $filter = 'user';
            $url = Url::ACCOUNT_ALBUMS;
        }



        // $this->flash->add("Welcome to Albums");
        $entityType = $this->scrap->getPageKey(); // Use context
        $entityTypezz = $this->scrap->getRouteType();

        // Get current route context
        $path = $_SERVER['REQUEST_URI'];
        $isAdminRoute = strpos($path, '/admin/') === 0;
        $isStoreRoute = strpos($path, '/stores/') === 0;
        $isAccountRoute = strpos($path, '/account/') === 0 && !$isStoreRoute;

        // Set default criteria - no filtering
        $criteria = [];

        // Apply context-specific filtering and verify permissions
        if ($isAdminRoute) {
            // Check if user has admin privileges

            if (!$this->authService->hasRole('admin')) {
                $this->flash->add("You don't have permission to access the admin area", FlashMessageType::Error);
                return $this->redirect(Url::ACCOUNT_DASHBOARD->url());
            }
            // Admin sees all albums, no filter needed
        } elseif ($isStoreRoute) {
            // Check if user is a store owner
            if (!$this->authService->hasRole('store_owner')) {
                $this->flash->add("You don't have permission to access the store area", FlashMessageType::Error);
                return $this->redirect(Url::ACCOUNT_DASHBOARD->url());
            }
            // Store owners see albums for their store
            $storeId = $_SESSION['active_store_id'] ?? null;
            if ($storeId) {
                $criteria['album_store_id'] = $storeId;
            }
        } elseif ($isAccountRoute) {
            // Regular users see only their own albums
            $userId = $this->getCurrentUserId();
            $criteria['album_user_id'] = $userId;
        }

        // Get the albums with pagination
        $page = isset($this->route_params['page']) ? (int)$this->route_params['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        // $criteria = [];

        $albums = $this->albumRepository->findBy(
            $criteria,
            ['created_at' => 'DESC'],
            $limit,
            $offset
        );
        $totalAlbums = $this->albumRepository->countBy();

        $totalPages = ceil($totalAlbums / $limit);

        // Map entities to simple arrays for view
        $fields = $this->albumsListType->getColumns();
        $albumRecords = array_map(function ($album) use ($fields) {
            return $this->albumRepository->toArray($album, $fields);
        }, $albums);

        // Create our list using the list factory
        $albumsList = $this->listFactory->create(
            $this->albumsListType,
            $albumRecords,
            [
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_items' => $totalAlbums,
                    'per_page' => $limit
                ],
                'render_options' => [
                    'pagination_url' => BASE_URL . '/stores/albums/page/{page}',
                    'pagination_url3333' => BASE_URL . '/stores/albums/page/{page}',
                    'add_url' => '/stores/albums/add333',
                    'test_value' => 'high',             // RemoveMe remove This was for me later for testing
                    'test_value_only_high' => 'high'    // RemoveMe remove This was for me later for testing
                ]
            ]
        );
        $rrr2 = Url::STORE_ALBUMS->view();
        $rrr = Url::STORE_ALBUMS_EDIT->view();
        $rrrrr = Url::STORE_ALBUMS->url();

        // // return $this->view(AlbumsConst::VIEW_ALBUMS_INDEX, [
        // return $this->view(Url::STORE_ALBUMS->view(), [
        //     'name' => 'Blog Albums',
        //     // 'actionLinks' => $this->getActionLinks('albums', ['index']),
        //     'albumsList' => $albumsList,
        // ]);

                // return $this->view(Url::STORE_ALBUMS->view(), [
        return $this->view($url->view(), [
            'title' => 'Localxxxxx Albums Placeholder',
            'name' => 'Blog Albums',
            // 'actionLinks' => $this->getActionLinks('albums', ['index']),
            'albumsList' => $albumsList,
        ]);
    }


    /**
     * Edit an existing album
     */
    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
        // Get album ID from route parameters
        $albumId = isset($this->route_params['id']) ? (int)$this->route_params['id'] : null;

        // Important!!! Manaual Tests.
        // $albumId = 333;  // Test - Record does not exist
        // $albumId = null; // Test - Route missing Record ID
        // $albumId = 3;    // Test - Pointing to a Record that belongs to another User ID

        if (!$albumId) {
            $this->throwAlbumNotFound($albumId);
        }

        // Get the album from the database
        $album = $this->albumRepository->findById($albumId);

        if (!$album) {
            $this->throwAlbumNotFound($albumId);
        }

        if (!$this->isUserAuthorized($album->getAlbumUserId())) {
            $this->flash->add("You don't have permission to edit this album", FlashMessageType::Error);
            return $this->redirect(Url::STORE_ALBUMS->url());
        }

        // Create the form with existing album data
        $form = $this->formFactory->create(
            $this->albumsFormType,
            [
                'name' => $album->getName(),
                'description' => $album->getDescription(),
                // Add other fields as needed
            ],
            [
                'ip_address' => $this->getIpAddress(),
                // 'submit_text' => 'cccccccccddSave',
            ]
        );
        $formTheme = $form->getCssFormThemeFile();

        // Process form submission
        $formHandled = $this->formHandler->handle($form, $request);
        if ($formHandled && $form->isValid()) {
            $data = $form->getData();

            $album->setName($data['name']);
            $album->setDescription($data['description']);
            $album->setSlug($this->generateSlug($data['name']));
            // Don't update user_id as this would change ownership

            // Update the album in the database
            $success = $this->albumRepository->update($album);
            // $success = false;
            // throw new \PDOException();
            // throw new \Exception();

            if ($success) {
                $this->flash->add("Album updated successfully", FlashMessageType::Success);
                return $this->redirect(Url::STORE_ALBUMS->url());
            } else {
                $form->addError('_form', 'Failed to update your album. Please try again.');
            }
        }

        // Prepare view data
        $viewData = [
            'name' => 'Edit Album',
            'album' => $album,
            'form' => $form,
            'formTheme' => $formTheme
        ];


        // Create response with appropriate status code
        // $response = $this->view(AlbumsConst::VIEW_ALBUMS_EDIT, $viewData);
        $response = $this->view(Url::STORE_ALBUMS_EDIT->view(), $viewData);

        // Set 422 Unprocessable Entity status for form failures
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;
    }

    /**
     * Edit an existing album
     */
    public function createAction(ServerRequestInterface $request): ResponseInterface
    {
        // if (!$this->isUserAuthorized($album->getAlbumUserId())) {
            // $this->flash->add("You don't have permission to edit this album", FlashMessageType::Error);
            // return $this->redirect(Url::STORE_ALBUMS->url());
        // }

        // Create the form with existing album data
        $form = $this->formFactory->create(
            $this->albumsFormType,
            [],
            // [
            //     'name' => $album->getName(),
            //     'description' => $album->getDescription(),
            //     // Add other fields as needed
            // ],
            [
                'ip_address' => $this->getIpAddress(),
                'form_heading' => 'wwAdd wwAlbum',
                'submit_text' => 'wwAdd',
            ]
        );
        $formTheme = $form->getCssFormThemeFile();


        // Get the current user ID - We use a trait
        $userId = $this->getCurrentUserId();



        // Process form submission
        $formHandled = $this->formHandler->handle($form, $request);
        if ($formHandled && $form->isValid()) {
            $data = $form->getData();

            try {
                // Create a Post entity from the form data
                $album = new \App\Entities\Album();
                $album->setAlbumUserId($userId);
                $album->setAlbumStoreId(2); // TODO hardcoded strore id
                $album->setSlug($this->generateSlug($data['name']));
                $album->setAlbumStatus('P');
                $album->setName($data['name']);
                $album->setDescription($data['description']);

                // Insert the post into the database
                $albumId = $this->albumRepository->create($album);

                if ($albumId) {
                    // Add success flash message
                    $this->flash->add("Your album has been created successfully", FlashMessageType::Success);

                    // Redirect to the album list
                    // return $this->redirect('/stores/posts');
                    return $this->redirect(Url::STORE_ALBUMS->url());
                } else {
                    // Handle failed insertion
                    $form->addError('_form', 'Failed to save your album. Please try again.');
                }
            } catch (\Exception $e) {
                // Add error message to form
                $form->addError('_form', 'An error occurred: ' . $e->getMessage());
            }

            // $album->setName($data['name']);
            // $album->setDescription($data['description']);
            // $album->setSlug($this->generateSlug($data['name']));
            // // Don't update user_id as this would change ownership

            // // Update the album in the database
            // $success = $this->albumRepository->update($album);
            // // $success = false;
            // // throw new \PDOException();
            // // throw new \Exception();

            // if ($success) {
            //     $this->flash->add("Album updated successfully", FlashMessageType::Success);
            //     return $this->redirect(Url::STORE_ALBUMS->url());
            // } else {
            //     $form->addError('_form', 'Failed to update your album. Please try again.');
            // }
        }

        // Prepare view data
        $viewData = [
            'name' => 'Create New Album',
            // 'album' => $album,
            'form' => $form,
            'formTheme' => $formTheme
        ];


        // Create response with appropriate status code
        // $response = $this->view(AlbumsConst::VIEW_ALBUMS_EDIT, $viewData);
        $response = $this->view(Url::STORE_ALBUMS_CREATE->view(), $viewData);

        // Set 422 Unprocessable Entity status for form failures
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;
    }


    public function deleteAction(ServerRequestInterface $request): ResponseInterface
    {
        // Get album ID from route parameters
        // $albumId = isset($this->route_params['id']) ? (int)$this->route_params['id'] : null;

        // if (!$albumId) {
        //     $this->throwAlbumNotFound($albumId);
        // }
        $data = $request->getParsedBody();
        $albumId = isset($data['id']) ? (int)$data['id'] : (
            isset($this->route_params['id']) ? (int)$this->route_params['id'] : null
        );

        // Get the album from the database
        $album = $this->albumRepository->findById($albumId);

        if (!$album) {
            $this->throwAlbumNotFound($albumId);
        }

        // Check authorization
        if (!$this->isUserAuthorized($album->getAlbumUserId())) {
            $this->flash->add("You don't have permission to delete this album", FlashMessageType::Error);
            return $this->redirect(Url::STORE_ALBUMS->url());
        }

        // Delete the album
        $success = $this->albumRepository->delete($albumId);

        if ($success) {
            $this->flash->add("Album deleted successfully", FlashMessageType::Success);
        } else {
            $this->flash->add("Failed to delete album", FlashMessageType::Error);
        }

        // Redirect back to the album list
        return $this->redirect(Url::STORE_ALBUMS->url());
    }




    /**
     * Show the albums form
     */
    public function xxaddAction(ServerRequestInterface $request): ResponseInterface
    {
        // Get the current user ID - We use a trait
        $userId = $this->getCurrentUserId();

        $form = $this->formFactory->create(
            $this->albumsFormType,
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

            try {
                // Create a Album entity from the form data
                $album = new \App\Entities\Album();
                $album->setAlbumUserId($userId);
                $album->setSlug($this->generateSlug($data['name']));
                $album->setAlbumStatus('P'); // Published by default
                $album->setName($data['name']);
                $album->setDescription($data['description']);

                // Insert the album into the database
                $albumId = $this->albumRepository->create($album);

                if ($albumId) {
                    // Add success flash message
                    $this->flash->add("Your album has been published successfully", FlashMessageType::Success);

                    // Redirect to the albums list
                    // return $this->redirect('/stores/albums');
                    return $this->redirect(Url::STORE_ALBUMS->url());
                } else {
                    // Handle failed insertion
                    $form->addError('_form', 'Failed to save your album. Please try again.');
                }
            } catch (\Exception $e) {
                // Add error message to form
                $form->addError('_form', 'An error occurred: ' . $e->getMessage());
            }
        }

        // Prepare view data - pass the form directly instead of FormView
        $viewData = [
            'name' => 'Create New Album',
            'form' => $form,
            'formTheme' => $formTheme // if $formTheme isset/used
        ];

        // Create response with appropriate status code
        $response = $this->view(Url::STORE_ALBUMS_EDIT->view(), $viewData);

        // Set 422 Unprocessable Entity status for form failures
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;
    }


    /**
     * Generate a slug from a name
     */
    private function generateSlug(string $name): string
    {
        // Convert to lowercase
        $slug = strtolower($name);

        // Replace spaces with hyphens
        $slug = str_replace(' ', '-', $slug);

        // Remove special characters
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);

        // Remove multiple hyphens
        $slug = preg_replace('/-+/', '-', $slug);

        // Trim hyphens from beginning and end
        $slug = trim($slug, '-');

        return $slug;
    }
}
# 455 347 317
