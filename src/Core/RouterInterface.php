<?php

// namespace Core;

// interface RouterInterface
// {
//     public function add($route, array $controller = []);
//     public function dispatch($url);
// }

// filepath: d:\xampp\htdocs\mvclixo\src\Core\RouterInterface.php

namespace Core;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

interface RouterInterface
{
    /**
     * Dispatch the route for the given request
     *
     * @param ServerRequestInterface $request The request object
     * @return ResponseInterface The response
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface;

    /**
     * Add a route to the routing table
     *
     * @param string $route The route URL
     * @param array $params Parameters (controller, action, etc.)
     * @return void
     */
    public function add($route, array $params = []);

    // Any other methods your interface requires
}