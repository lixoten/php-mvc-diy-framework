<?php

declare(strict_types=1);

namespace Core;

use App\Enums\FlashMessageType;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Http\HttpFactory;
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
    protected FlashMessageServiceInterface $flash;
    protected View $view;
    protected HttpFactory $httpFactory;
    protected ?SessionManagerInterface $session = null;

    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container
    ) {
        $this->route_params = $route_params;
        $this->flash = $flash;
        $this->view = $view;
        $this->httpFactory = $httpFactory;
        $this->container = $container;
    }


    /**
     * Initialize controller with request data
     */
    public function initialize(ServerRequestInterface $request): void
    {
        // Get session from request attributes
        $this->session = $request->getAttribute('session');
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
            throw new InvalidArgumentException("Method $name (in controller) not found", 404);
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
        //exit();
        $args['flash'] = $this->flash;
        $content = $this->view->renderWithLayout($template, $args);

        $response = $this->httpFactory->createResponse($statusCode);
        $response->getBody()->write($content);

        $body = (string)$response->getBody();
        //error_log("Response Body Length: " . strlen($body));
        //error_log("Response Body Preview: " . substr($body, 0, 200));
        //error_log("Response Headers: " . json_encode($response->getHeaders()));

        return $response;
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
        $args['flash'] = $this->flash;

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

    protected function getActionLinks(string $page, array $needArr = []): array
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
}
# End of File 1919 430 149
