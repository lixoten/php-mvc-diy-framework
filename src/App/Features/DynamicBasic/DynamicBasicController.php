<?php

declare(strict_types=1);

namespace App\Features\DynamicBasic;

use App\Enums\FlashMessageType;
use App\Helpers\DebugRt;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Controller;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Services\Interfaces\PageRegistryInterface;
use Core\Context\CurrentContext;

// Dynamic-me
/**
 * Dynamic Pages Controller
 *
 * This controller handles all static/dynamic pages like About, Terms, Privacy, etc.
 * Content is loaded from either a database or configuration file.
 */
class DynamicBasicController extends Controller
{
    protected PageRegistryInterface $pageRegistry;

    /**
     * Constructor
     */
    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        PageRegistryInterface $pageRegistry,
        CurrentContext $scrap,
    ) {
        parent::__construct(
            $route_params,
            $flash,
            $view,
            $httpFactory,
            $container,
            $scrap
        );
        $this->pageRegistry = $pageRegistry;
    }

    /**
     * Generic page action that handles any static page
     */
    public function pageAction(ServerRequestInterface $request): ResponseInterface
    {
        DebugRt::j('0', '', 111);
        // Get the page key from route params
        $pageName = $this->route_params['page_name'] ?? 'not-found';

        // Validate if the page key is valid and exists in the registry
        if ($pageName === null || !$this->pageRegistry->hasPage($pageName)) {
            throw new \Core\Exceptions\PageNotFoundException(
                "Page '$pageName' not found or invalid.",
                requestedRoute: $pageName ?? 'unknown'
            );
        }

        // Load page content from the appropriate source
        // Note: We already know the page *definition* exists from the check above.
        // This now focuses purely on loading the *content*.
        $pageData = $this->loadPageData($pageName);

        // Although we checked hasPage, content loading might still fail (e.g., DB error)
        if (!$pageData) {
            // Log this scenario as it might indicate a content loading issue
            error_log("Failed to load content for registered page: '$pageName'");
            throw new \Core\Exceptions\PageNotFoundException(
                "Content for page '$pageName' could not be loaded.",
                requestedRoute: $pageName
            );
        }

        // Return the view
        // Decide on view template: Use specific if defined, else default
        $viewTemplate = $pageData['template'] ?? "dynamic/index"; // Assuming 'dynamic/index' is your default

        // Return the view
        return $this->view($viewTemplate, [
            'title' => $pageData['title'] ?? ucfirst($pageName),
            'content' => $pageData['content'] ?? '',
            'meta_description' => $pageData['meta_description'] ?? '',
            'last_updated' => $pageData['last_updated'] ?? '',
            'page_name' => $pageName
        ]);
    }

    /**
     * Load page data from the appropriate source
     * (database, config file, or other storage)
     */
    private function loadPageData(string $pageName): ?array
    {
        // 1. Try loading from database (if implemented)
        $pageData = $this->loadPageFromDatabase($pageName);

        // 2. If not found in database, get it from the PageRegistry
        if ($pageData === null) {
            // The PageRegistry already loaded the config data
            $pageData = $this->pageRegistry->getPage($pageName);
        }

        // $pageData will be null here if the page exists in the registry
        // but somehow failed to load (which shouldn't happen with config-only source)
        // or if it wasn't found in the DB and also not in the registry (though hasPage should prevent this call).
        return $pageData;
    }

    /**
     * Load page content from database
     */
    private function loadPageFromDatabase(string $pageName): ?array
    {
        // If you have a database repository for pages, use it here
        try {
            if ($this->container->has('App\Repository\PageRepositoryInterface')) {
                $pageRepository = $this->container->get('App\Repository\PageRepositoryInterface');
                $page = $pageRepository->findBySlug($pageName);

                if ($page) {
                    return [
                        'title' => $page->getTitle(),
                        'content' => $page->getContent(),
                        'meta_description' => $page->getMetaDescription(),
                        'last_updated' => $page->getUpdatedAt()
                    ];
                }
            }
        } catch (\Exception $e) {
            // Log error but continue to try config
            error_log("Error loading page from database: " . $e->getMessage());
        }

        return null;
    }

    // /**
    //  * Load page content from config
    //  */
    // private function loadPageFromConfig(string $pageName): ?array
    // {
    //     // Get pages from configuration
    //     //$pagesConfig = $this->container->get('config')['pages'] ?? [];

    //     // Get the ConfigService instance
    //     /** @var \Core\Interfaces\ConfigInterface $configService */
    //     $configService = $this->container->get('config'); // Or ConfigInterface::class if registered that way

    //     // Use the get() method to retrieve the 'pages' config, providing a default empty array
    //     $//pagesConfig = $configService->get('pages', []);
    //     $pagesConfig = $configService->get('app.pages', []);

    //     // Now access the specific page key from the retrieved array
    //     return $pagesConfig[$pageName] ?? null;


    //     //return $pagesConfig[$pageName] ?? null;
    // }
}
