<?php

declare(strict_types=1);

namespace Core;

use App\Helpers\DebugRt;
use App\Enums\FlashMessageType;
use App\Enums\Url;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Constants\Urls;
use Core\Context\CurrentContext;
use Core\Exceptions\RecordNotFoundException;
use Core\Http\HttpFactory;
use Core\Services\ConfigService;
use Core\Session\SessionManagerInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

//use App\Views\Components\Page\MessageBox;
//use App\Views\HtmlTable;
//use App\Helpers\RtClass_ResV2;
//use Exception;
//use http\Url;
//use Twig_Environment;
//use Twig_Loader_Filesystem;
//use Pimple\Container;

/**
 * Base controller
 *
 * PHP version 7.0
 */
//abstract class Controller extends DIP
abstract class Controller
{
    protected ContainerInterface $container;
    protected string $pageTitle;
    public array $route_params;
    // protected FlashMessageServiceInterface $flash22;
    protected View $view;
    protected HttpFactory $httpFactory;
    protected ?SessionManagerInterface $session = null;
    protected ?ServerRequestInterface $request = null;
    //protected ConfigService $request; // This is incorrectly typed as ConfigService
    protected CurrentContext $scrap;

    public function __construct(
        array $route_params,
        protected FlashMessageServiceInterface $flash22, // constructor promotion php8+
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        CurrentContext $scrap
    ) {
        $this->route_params = $route_params;
        $this->flash22 = $flash22;
        $this->view = $view;
        $this->httpFactory = $httpFactory;
        $this->container = $container;
        $this->scrap = $scrap;
    }


    /**
     * Initialize controller with request data
     */
    public function initialize(ServerRequestInterface $request): void
    {
        // Get session from request attributes
        $this->session = $request->getAttribute('session');
        $this->request = $request; // The base Controller sets this when initialized
    }


    /**
     * Magic method called when a non-existent or inaccessible method is
     * called on an object of this class.
     */
    public function __call(string $name, array $args)
    {
        $method = $name . "Action";
        $request = $args[0] ?? null; // Get request from args if available

        if (method_exists($this, $method)) {
            if ($this->before() !== false) {
                $result = call_user_func_array([$this, $method], $args);
                $this->after();

                // Convert result to ResponseInterface
                if ($result instanceof ResponseInterface) {
                    return $result;
                } elseif (is_string($result)) {
                    $response = $this->httpFactory->createResponse();
                    $response->getBody()->write($result);
                    return $response;
                } else {
                    // Return empty response for null or other types
                    return $this->httpFactory->createResponse();
                }
            }
        } else {
            throw new InvalidArgumentException("Method $name (in controller) not found...", 404);
        }
    }



    // TOxDO
    // private function requiredLogin()
    // {
    //     if (! isset($_SESSION['user_id'])) {
    //         $this->flash->add('Please log in to access this page.', FlashMessageType::Warning);
    //         $this->returnPageManagerObj->setReturnToPage('/login');
    //         $this->redirectObj->to($this->returnPageManagerObj->getReturnToPage());
    //         return; // Important: Exit the method to prevent further execution
    //     }

    //     // Check user status for "pending"
    //     if (isset($_SESSION['status_code']) && $_SESSION['status_code'] === "P") {
    //         $controllerAction = $this->route_params['controller'] . '/' . $this->route_params['action'];
    //         if (!in_array($controllerAction, $this->pending_access)) {
    //             // Redirect or display access denied message
    //             $this->flash->add(
    //                 'Your account is pending approval.  Limited access.',
    //                 FlashMessageType::Warning
    //             );
    //             $this->returnPageManagerObj->setReturnToPage('/'); // Redirect to home or a specific "pending" page
    //             $this->redirectObj->to($this->returnPageManagerObj->getReturnToPage());
    //             exit; // Stop further execution
    //         }
    //     }
    // }

    /**
     * Before filter - called before an action method.
     *
     * @return void
     */
    protected function before()
    {
        // DANGER
        // if (in_array($this->route_params['controller'].'/'. $this->route_params['action'], $this->login_required)) {
        //     $this->requiredLogin();//TOxDO
        // }
        return true; // Default to allowing the action
    }

    /**
     * After filter - called after an action method.
     *
     * @return void
     */
    protected function after()
    {
    }


    // Update view method to return Response
    protected function view(string $template, array $args = [], int $statusCode = 200): ResponseInterface
    {
        // Add debug to see which template is being loaded
        // DebugRt::j('0', 'Template being rendered', $template);
        // DebugRt::j('0', 'Template args', array_keys($args));
        $args += $this->getScrapInfoViewData();

        //exit();
        //Debug::p($template);
        $args['flash'] = $this->flash22;
        // DebugRt::j('1', '', 111);
        $content = $this->view->renderWithLayout($template, $args);
        // DebugRt::j('1', '', 111);
        $response = $this->httpFactory->createResponse($statusCode);
        $response->getBody()->write($content);

        $body = (string)$response->getBody();
        //error_log("Response Body Length: " . strlen($body));
        //error_log("Response Body Preview: " . substr($body, 0, 200));
        //error_log("Response Headers: " . json_encode($response->getHeaders()));

        return $response;
    }


    /**
     * Scrap info for view data
     */
    protected function getScrapInfoViewData(): array
    {
        if ($_ENV['APP_ENV'] === 'development') {
            $viewData['scrapInfo'] = (array) $this->scrap->printIt();
            $viewData['scrap'] = $this->scrap;
        }
        return $viewData;
    }

    /**
     * Create a JSON response
     */
    protected function json($data, int $statusCode = 200): ResponseInterface
    {
        $response = $this->httpFactory->createResponse($statusCode);
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Create a redirect response
     */
    protected function redirect(string $url, int $statusCode = 302): ResponseInterface
    {
        return $this->httpFactory->createResponse($statusCode)
            ->withHeader('Location', $url);
    }

    //// Keep your existing helper methods
    //protected function convertToCamelCase(string $string): string
    //{
    //    return lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $string))));
    //}


    protected function oldview(string $template, array $args = []): void
    {
        $args['flash'] = $this->flash22;

        //......$args += ["scrapsArr" => $this->route_params];
        $this->view->renderWithLayout($template, $args);

        // Get common data (keep this part as it's controller-specific logic)
        //..$commonData = $this->getCommonData();
    }

    /**
     * Convert string with hyphens to camelCase
     * e.g. post-authors => postAuthors
     *
     * @param string $string The string to convert
     * @return string
     */
    protected function convertToCamelCase(string $string): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $string))));
    }



    // TODO - create a helper servive class that has DI.
    // Possible names: include TextHelper, StringUtil, or StringFormatter.
    // But StringHelper is the most common and widely understood convention for a class that contains
    // general-purpose string-related methods.
    // 1. URL-Related Methods
    // 2. Text & String Formatting
    // MOVE generateSlug method would be in there too
    /**
     * Generate a slug from a title
     */
    protected function generateSlug(string $title): string
    {
        // Convert to lowercase
        $slug = strtolower($title);

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




    protected function xxxgetActionLinks(string $page, array $needArr = []): array
    {
        if ($needArr === null || !is_array($needArr) || empty($needArr)) {
            return [];
        }

        $contentArr = [];

        foreach ($needArr as $key => $need) {
            $contentArr[$need . 'Action' ] = "$page/$need";
        }

        return $contentArr;
    }

    protected function getActionLinks(Url ...$urls): array
    {
        $contentArr = [];

        foreach ($urls as $url) {
            $contentArr[] = [
                'url' => $url,
                'text' => $url->label()
            ];
        }

        return $contentArr;
    }

    /**
     * Get client IP address from current request
     */
    protected function getIpAddress(): string
    {
        return $this->request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Check if CAPTCHA is being forced via query parameter
     */
    protected function isForcedCaptcha(): bool
    {
        return (bool)($this->request->getQueryParams()['show_captcha'] ?? false);
    }


    /**
     * Throw a record not found exception with standard links
     *
     * @param string $entityType Type of entity (Post, Product, Gallery, etc.)
     * @param mixed $entityId ID of the entity (can be null for missing ID cases)
     * @param string $message Custom error message (optional, defaults to standard message)
     * @param array $additionalLinks Additional helpful links beyond the standard ones
     * @throws RecordNotFoundException
     */
    protected function txxxxxxxxxxxxxxxxxxxxxxxxxxxhrowRecordNotFound(
        string $entityType,
        $entityId = null,
        string $message = null,
        array $additionalLinks = []
    ): void {
        // If no message provided, generate a standard one
        if ($message === null) {
            $message = $entityId === null
                ? "{$entityType} ID isxxx missing from the request"
                : "{$entityType} not found. It may have been deleted or never existed.";
        }


        // Start with standard links that make sense for all entity types
        $helpfulLinks = [
            'Go to Dashboard' => Urls::USER_DASHBOARD
        ];

        // Add entity-specific standard links based on entity type
        switch (strtolower($entityType)) {
            case 'post':
                $helpfulLinks['Return to Posts List'] = Urls::STORE_POSTS;
                $helpfulLinks['Create a New Post'] = Urls::STORE_POSTS_ADD;
                break;

            case 'product':
                $helpfulLinks['Return to Products List'] = Urls::STORE_PRODUCTS;
                $helpfulLinks['Create a New Product'] = Urls::STORE_PRODUCTS_ADD;
                break;

            case 'gallery':
                $helpfulLinks['Return to Galleries'] = Urls::STORE_GALLERIES;
                $helpfulLinks['Create a New Gallery'] = Urls::STORE_GALLERIES_ADD;
                break;

            // Add more cases as needed for other entity types
        }

        // Add any additional custom links
        $helpfulLinks = array_merge($helpfulLinks, $additionalLinks);

        throw new RecordNotFoundException(
            message: $message,
            entityType: $entityType,
            entityId: $entityId,
            helpfulLinks: $helpfulLinks
        );
    }
}
# End of File 1919 430 149
