<?php

declare(strict_types=1);

namespace Core;

use App\Helpers\DebugRt as Debug;
use Core\Http\HttpFactory;
use DI\Container as DIContainer;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Core\Exceptions\PageNotFoundException;

class Router implements RouterInterface
{
    // Rest of the Router implementation remains the same
    // ...

    protected $params = [];
    protected $routes = [];
    protected $container;
    protected $httpFactory;

    // Manual DI // public function __construct(Container $container = null)
    public function __construct(\Psr\Container\ContainerInterface $container = null, HttpFactory $httpFactory = null)
    {
        $this->container = $container;
        $this->httpFactory = $httpFactory;
    }

    public function add($route, array $params = [])
    {
        // Convert the route to a regular expression: escape forward slashes
        $route = preg_replace('/\//', '\\/', $route);

        // Convert variables e.g. {controller}
        $route = preg_replace('/\{([a-z]+\/?)}/', '(?P<\1>[a-z-]+\/?)', $route);

        // Convert variables with custom regular expressions e.g. {id:\d+}
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

        // Add start and end delimiters, and case-insensitive flag
        //$route = '/^' . $route . '$/i';


        // Add start and end delimiters, and case-insensitive flag
        $route = '#^' . $route . '$#i';
        $this->routes[$route] = $params;
    }

   /**
     * Match the route to the routes in the routing table, setting the $params
     * property if a route is found.
     *
     * @param string $url The route URL
     *
     * @return boolean  true if a match found, false otherwise
     */
    protected function match(string $url): bool
    {
        // Extract query string if present
        // $urlParts = parse_url($url);
        // $path = $urlParts['path'] ?? $url;

        // Process query string if it exists
        // $queryParams = [];
        // if (isset($urlParts['query'])) {
            // parse_str($urlParts['query'], $queryParams);
        // }
//Debug::p($url,0);
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                // Get named capture group values
                //Debug::p($matches, 0);
                foreach ($matches as $key => $match) {
                    // Check if this is our special 'args' parameter for repetitive params
                    if ($key === 'args' && strpos($url, '/param/') !== false) {
                        // Extract the part after /param/
                        $argsString = $match;
                        $segments = explode('/', $argsString);

                        // Process segments in pairs as key-value
                        for ($i = 0; $i < count($segments) - 1; $i += 2) {
                            $paramKey = $segments[$i];
                            $paramValue = $segments[$i + 1] ?? null;
                            $params[$paramKey] = $paramValue;
                        }

                        // Store original args too for reference
                        $params['args'] = $argsString;
                    } else {
                        // Normal parameter handling
                        $params[$key] = $match;
                    }
                }
                //Debug::p($path);


                $params["url"] = $url;
                //Debug::p($params,0);
                $this->params = $params;
                //Debug::p($params, 0);
                return true;
            }
        }

        return false;
    }


    // Update dispatch method to work with PSR-7
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $url = ltrim($request->getUri()->getPath(), '/');

        // Default to root if URL is empty
        if ($url == '') {
            $url = 'home/index';
        }

        if ($this->match($url)) {
            $this->params['controller'] = $this->toPascalCase($this->params['controller']);

            $qualifiedNamespace = $this->getNamespace();

            // For admin routes, add the controller name as a subfolder too
            if (array_key_exists('namespace', $this->params)) {
                if ($this->params['namespace'] === 'Admin') {
                    $qualifiedNamespace .= $this->params['controller'] . '\\';
                }
                $this->params['namespace'] = $qualifiedNamespace;
            }

            $controllerClass = $qualifiedNamespace
                . $this->convertToStudlyCaps($this->params['controller'])
                . 'Controller';

            if (class_exists($controllerClass)) {
                // Add route params to request attributes
                foreach ($this->params as $key => $value) {
                    if (is_string($key)) {
                        $request = $request->withAttribute($key, $value);
                    }
                }

                // Store updated request and route params in container
                if ($this->container) {
                    if ($this->container instanceof \DI\Container) {
                        $this->container->set('route_params', $this->params);
                        $this->container->set('current_request', $request);
                    }
                    //$this->container->set('route_params', $this->params);
                    //$this->container->set('current_request', $request);
                }

                // Create controller
                if ($this->container instanceof \DI\Container) {
                    $controllerObject = $this->container->make($controllerClass, [
                        'route_params' => $this->params
                    ]);
                } else {
                    $controllerObject = new $controllerClass($this->params);
                }

                // initialize controller with request if method exists
                if (method_exists($controllerObject, 'initialize')) {
                    $controllerObject->initialize($request);
                }

                $action = $this->convertToCamelCase($this->params['action']);

                if (is_callable([$controllerObject, $action])) {
                    // Call the action with the request
                    $result = $controllerObject->$action($request);

                    // Return result if it's already a ResponseInterface
                    if ($result instanceof ResponseInterface) {
                        return $result;
                    }

                    // Convert other return types to Response
                    if ($this->httpFactory) {
                        $response = $this->httpFactory->createResponse();
                        if (is_string($result)) {
                            $response->getBody()->write($result);
                        }
                        return $response;
                    } else {
                        throw new \RuntimeException("HttpFactory not available for response creation");
                    }
                } else {
                    throw new InvalidArgumentException(
                        "Method $action (in controller $controllerClass) not found",
                        404
                    );
                }
            }
        }

        throw new PageNotFoundException('Page not found..');
    }


    public function dispatchOld($url)
    {
        $url = rtrim($url, '/');
        // Default to root if URL is empty
        // if ($url == '') {
        //     $url = '/';
        // }

        if ($this->match($url)) {
            //DebugRt::p($this->routes);
            ## fix to make sure controller is always Pascalcase as in "Posts" vs "posts", required
            ##for comparing controller name again controller::class in Feature tree structure
            $this->params['controller'] = $this->toPascalCase($this->params['controller']);

            $qualifiedNamespace = $this->getNamespace();

            // For admin routes, add the controller name as a subfolder too
            if (array_key_exists('namespace', $this->params)) {
                if ($this->params['namespace'] === 'Admin') {
                    $qualifiedNamespace .= $this->params['controller'] . '\\';
                }
                $this->params['namespace'] = $qualifiedNamespace;
            }
            $controllerClass = $this->params['controller'];

            $controllerClass = $qualifiedNamespace
                . $this->convertToStudlyCaps($this->params['controller'])
                . 'Controller';

            if (class_exists($controllerClass)) {
                // Create the controller
                // Manual DI - Start //
                // if ($this->container) {
                //     // Use container to create controller if available
                //     $controllerObject = $this->container->get($controllerClass, [
                //         'route_params' => $this->params
                //     ]);
                // } else {
                //     // Fallback to direct instantiation
                //     $controllerObject = new $controllerClass();
                // }
                // Manual DI - End //

                //phpDI In dispatch method:
                if ($this->container instanceof \DI\Container) {
                    // Use make() to create object with runtime parameters
                    $controllerObject = $this->container->make($controllerClass, [
                        'route_params' => $this->params
                    ]);
                } else {
                    // Fallback for other container types
                    $controllerObject = new $controllerClass($this->params);
                }


                // $action = $this->params['action'];
                // $action = $this->convertToCamelCase($action);
                $action = $this->convertToCamelCase($this->params['action']);
                ## Step 3 : We check to see if the action exists in class
                if (is_callable([$controllerObject, $action])) {
                    $controllerObject->$action();
                    return;
                } else {
                    throw new InvalidArgumentException(
                        "WTF1..Method $action (in controller $controllerClass) not found..",
                        404
                    );
                    //Fixme : Exception logic
                }
            }
        }

        // Route not found
        //header('HTTP/1.1 404 Not Found');
        //echo '404 Page Not Found';
        throw new \Core\Exceptions\PageNotFoundException('Page not foundzz');
    }


    protected function getNamespace(): string
    {
        $baseNamespace = 'App\Features\\';
        if (array_key_exists('controller', $this->params)) {
            if (array_key_exists('namespace', $this->params)) {
                $baseNamespace .= $this->params['namespace'] . '\\';
            } else {
                $baseNamespace = $baseNamespace . $this->params['controller'] . '\\';
            }
        }

        return $baseNamespace;
    }

    /**
     * Convert the string with hyphens to StudlyCaps,
     * e.g. post-authors => PostAuthors
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    protected function convertToStudlyCaps(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    /**
     * Convert the string with hyphens to camelCase,
     * e.g. add-new => addNew
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    protected function convertToCamelCase(string $string): string
    {
        return lcfirst($this->convertToStudlyCaps($string));
    }


    protected function toPascalCase($string)
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));
    }
}
