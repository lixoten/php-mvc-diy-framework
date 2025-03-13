<?php

namespace Core;

use App\Helpers\DebugRt as Debug;
use DI\Container as DIContainer;
use InvalidArgumentException;

class Router implements RouterInterface
{
    // Rest of the Router implementation remains the same
    // ...

    protected $params = [];
    protected $routes = [];
    protected $container;


    // Manual DI // public function __construct(Container $container = null)
    public function __construct(\Psr\Container\ContainerInterface $container = null)
    {
        $this->container = $container;
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



    public function dispatch($url)
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
                    return; // Add this return statement to exit after handling the route
                } else {
                    throw new InvalidArgumentException(
                        "WTF1..Method $action (in controller $controllerClass) not found",
                        404
                    );
                    //Fixme : Exception logic
                }
            }
        }

        // Route not found
        //header('HTTP/1.1 404 Not Found');
        //echo '404 Page Not Found';
        throw new \Core\Exceptions\PageNotFoundException('Page not found');
    }


    protected function getNamespace(): string
    {
        //Debug::p($this->params);
        // TEMP LOGIC while switching to a FEATURE tree structure //TODO
        // if (Config::USE_CLASSIC_TREE) {
        //     $namespace = 'App\Controllers\\';
        //     if (array_key_exists('namespace', $this->params)) {
        //         $namespace .= $this->params['namespace'] . '\\';
        //         //print "<br />level>>>> " .$this->params['level'];
        //     }
        // } else {
        $baseNamespace = 'App\Features\\';
        if (array_key_exists('controller', $this->params)) {
            //$namespace = 'App\Features\\' . $this->params['controller'] . '\Controllers\\'; < In Folder Controller
            //$namespace = 'App\Features\\' . $this->params['controller'] . '\\';
            if (array_key_exists('namespace', $this->params)) {
                $baseNamespace .= $this->params['namespace'] . '\\';
            } else {
                $baseNamespace = $baseNamespace . $this->params['controller'] . '\\';
            }
        }
        //}

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

        // Add this helper function to convert to PascalCase
    protected function toPascalCase($string)
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));
    }
}
