<?php

declare(strict_types=1);

namespace App\Features\Posts;

use App\Enums\FlashMessageType;
use App\Enums\PostFields2;
use App\Enums\Url;
use App\Features\Posts\Form\PostsFormType;
use App\Features\Posts\List\PostsListType;
use App\Helpers\DebugRt;
use App\Repository\PostRepositoryInterface;
use Core\Controller;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Constants\ListOptions;
use Core\Constants\ListRenderOptions;
use Core\Context\CurrentContext;
use Core\Enum\SortDirection;
use Core\Form\FormFactoryInterface;
use Core\Form\FormHandlerInterface;
use Core\Http\HttpFactory;
use Core\List\ListFactory;
use Core\Traits\AuthorizationTrait;
use Core\Traits\EntityNotFoundTrait;
use Core\View;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Core\Form\Constants\ErrorDisplay as CONST_ED;
use Core\Form\Constants\Layouts as CONST_L;
use Core\Form\Constants\SecurityLevels as CONST_SL;

/**
 * Posts controller
 *
 */
class PostsController extends Controller
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
        private PostRepositoryInterface $repository,
        private PostsFormType $formType,
        private ListFactory $listFactory,
        private PostsListType $listType
    ) {
        parent::__construct(
            $route_params,
            $flash,
            $view,
            $httpFactory,
            $container,
            $scrap
        );
        $this->formFactory  = $formFactory;
        $this->formHandler  = $formHandler;
        $this->repository   = $repository;
        $this->formType     = $formType;
        $this->listFactory  = $listFactory;
        $this->listType     = $listType;
        $this->listType->routeType = $scrap->getRouteType();
    }


    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        $filter = (string)($request->getQueryParams()['filter'] ?? "DDDD");

        $storeId = $this->scrap->getStoreId();

        $routeType = $this->scrap->getRouteType();
        if ($routeType === 'account') {
            $filter = 'user';
            $url = Url::ACCOUNT_POST;
        } elseif ($routeType === 'store') {
            $filter = 'store';
            $url = Url::STORE_POST;
        } else {
            $filter = 'user';
            $url = Url::CORE_POST;
        }

        $configService = $this->container->get('config');

        $view = 'posts_index';
        $configOptions = $configService->get('view_options/' . $view) ?? ['a']; // loads "list_fields/posts.php"

        $options            = $configOptions['options'] ?? [];
        $renderOptions      = $configOptions['render_options'] ?? [];
        $paginationOptions  = $configOptions['pagination'] ?? [];
        $paginationOptions  = $configOptions['pagination'] ?? [];
        $listColumns        = $configOptions['list_columns'] ?? [];

        $listColumns = ['id', 'title', 'ffffff', 'fffeebbb', 'created_at'];

        $sortField = $options['default_sort_key']
            ?? $this->listType->getListOptions()['default_sort_key'] ?? PostFields2::TITLE->value;
        $sortDirection = $options['default_sort_direction']
            ?? $this->listType->getListOptions()['default_sort_direction'] ?? SortDirection::ASC->value;

        // Get the record with pagination
        $page = isset($this->route_params['page']) ? (int)$this->route_params['page'] : 1;
        $limit = $paginationOptions['per_page']
            ?? $this->listType->getListPaginationOptions()['per_page'] ?? 2;
        // $limit = $paginationOptions['per_page'] ?? 20;
        $offset = ($page - 1) * $limit;

        ## todo introduce filters. At the moment we are not.
        if ($filter === "user") {
            $userId = $this->scrap->getUserId();
            $records = $this->repository->findByUserId($userId, [$sortField => $sortDirection], $limit, $offset);

            $totalRecords = $this->repository->countByUserId($userId);
        } else {
            $records = $this->repository->findByStoreId($storeId, [$sortField => $sortDirection], $limit, $offset);
            $totalRecords = $this->repository->countByStoreId($storeId);
        }

        $totalPages = ceil($totalRecords / $limit);
        $paginationOptions['current_page'] = $page;
        $paginationOptions['total_pages'] = $totalPages;
        $paginationOptions['total_items'] = $totalRecords;

        // Map entities to simple arrays for view
        // $dataRecords = array_map(
        //     fn($record) => $this->repository->toArray($record, $listColumns),
        //     $records
        // );
        $cols = !empty($listColumns) ? $listColumns : $this->listType->getListColumns();
        if (!empty($listColumns)) {
            $cols = $listColumns;
        } else {
            $cols = $this->listType->getListColumns();
        }

        // we need to ipdate listType when incoming Col from controller
        $this->listType->setListColumns($cols);
        // $validColumns = $this->fieldRegistryService->filterAndValidateFields($listColumns);
        $dataRecords = array_map(
            function ($record) use ($cols) {
                return $this->repository->toArray($record, $cols);
            },
            $records
        );


        $list = $this->listFactory->create(
            listType: $this->listType,
            data: $dataRecords,
            options:  array_merge(
                $options,
                [
                    'pagination' => $paginationOptions,
                    'render_options' => $renderOptions + ['list_columns' => $listColumns],
                ]
            )
        );

        return $this->view($url->view(), [
            'title' => 'LoCAL Blog Posts',
            'postsList' => $list,
        ]);
    }


    /**
     * Edit an existing post
     */
    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
         // Get record ID from route parameters
        $recordId = isset($this->route_params['id']) ? (int)$this->route_params['id'] : null;

        if (!$recordId) {
            $this->throwPostNotFound($recordId); //fixme
        }

        // Get the record from the database
        $record = $this->repository->findById($recordId);

        if (!$record) {
            $this->throwPostNotFound($recordId);
        }


        if (!$this->isUserAuthorized($record->getPostUserId())) {
            $this->flash->add("You don't have permission to edit this record", FlashMessageType::Error);
            return $this->redirect(Url::CORE_POST->url()); //fixme
        }


        // OVERRIDES config files
        // We should not use this, unless we want to. USE `src/config/view_options/viewName.php/` file instead
        // makes development easy without messing with config files.
        // Important!!! - Advice, leave it commented out
        $options = [
            // 'ip_address' => $this->getIpAddress(),
            // 'boo' => 'boo',
            'render_options' => [
                'error_display' => 'inline',        // 'summary, inline'
                'layout_type'   =>  'sequential',   // fieldsets / sections / sequential
                // submit_text'   => "add fook",
                'form_fields'   => [
                    'title', 'content',
                ],
                'layout'        => [
                    [
                        'title' => 'This is the Title',
                        'fields' => ['title', 'content'],
                        'divider' => true
                    ],
                ],
            ]
        ];


        // Create the form with existing post data
        $form = $this->formFactory->create(
            formType: $this->formType,
            data: [
                'title'     => $record->getTitle(),
                'content'   => $record->getContent(),
            ],
            options: $options ?? [],
        );


        $formTheme = $form->getCssFormThemeFile();

        // Process form submission
        $formHandled = $this->formHandler->handle($form, $request);
        if ($formHandled && $form->isValid()) {
            $data = $form->getData();

            $record->setTitle($data['title']);
            $record->setContent($data['content']);
            $record->setSlug($this->generateSlug($data['title']));
            // Don't update user_id as this would change ownership

            // Update the post in the database
            $success = $this->repository->update($record);
            // $success = false;
            // throw new \PDOException();
            // throw new \Exception();

            if ($success) {
                $this->flash->add("post.update.success", FlashMessageType::Success);
                return $this->redirect(Url::CORE_POST->url());
            } else {
                $form->addError('_form', 'Failed to update your post. Please try again.');
            }
        }

        // Prepare view data
        $viewData = [
            'title' => 'Edit Post',
            'post' => $record,
            'form' => $form,
            'formTheme' => $formTheme
        ];

        // Create response with appropriate status code
        $response = $this->view(Url::CORE_POST_EDIT->view(), $viewData); //fixme make generic

        // Set 422 Unprocessable Entity status for form failures
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;
    }


    /**
     * Show the posts form
     */
    public function createAction(ServerRequestInterface $request): ResponseInterface
    {
        // Get the current user ID - We use a trait
        $userId = $this->getCurrentUserId();

        $form = $this->formFactory->create(
            $this->postsFormType,
            [],
            [
                // 'force_captcha' => $this->isForcedCaptcha(), // fix-force-captcha '';
                'ip_address' => $this->getIpAddress(),
                // Notes-: There below could be set here, but don't. Use the appropriate FormType
                // 'error_display' => 'inline',
                // 'submit_text' => 'Submit',
                // 'css_form_theme_class' => "form-theme-christmas",
                // 'title_heading' => 'Send us a message',
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
                // Create a Post entity from the form data
                $post = new \App\Entities\Post();
                $post->setPostUserId($userId);
                $post->setSlug($this->generateSlug($data['title']));
                $post->setPostStatus('P'); // Published by default
                $post->setTitle($data['title']);
                $post->setContent($data['content']);

                // Insert the post into the database
                $postId = $this->postRepository->create($post);

                if ($postId) {
                    // Add success flash message
                    $this->flash->add("Your post has been published successfully", FlashMessageType::Success);

                    // Redirect to the posts list
                    // return $this->redirect('/posts');
                    return $this->redirect(Url::CORE_POST->url());
                } else {
                    // Handle failed insertion
                    $form->addError('_form', 'Failed to save your post. Please try again.');
                }
            } catch (\Exception $e) {
                // Add error message to form
                $form->addError('_form', 'An error occurred: ' . $e->getMessage());
            }
        }

        // Prepare view data - pass the form directly instead of FormView
        $viewData = [
            'title' => 'Create New Post',
            'form' => $form,
            'formTheme' => $formTheme // if $formTheme isset/used
        ];

        // Create response with appropriate status code
        $response = $this->view(Url::CORE_POST_CREATE->view(), $viewData);

        // Set 422 Unprocessable Entity status for form failures
        if ($form->hasErrors()) {
            return $response->withStatus(422);
        }

        return $response;
    }


}
# 455 347 317
