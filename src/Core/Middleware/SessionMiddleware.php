<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Session\SessionManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware for managing session state
 *
 * Starts the session and makes it available to downstream middleware and handlers
 * via request attributes. Also handles session cleanup after the response is ready.
 */
class SessionMiddleware implements MiddlewareInterface
{
    private SessionManagerInterface $sessionManager;

    public function __construct(SessionManagerInterface $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Start the session
        $this->sessionManager->start();

        // Add the session manager to the request attributes
        // so controllers can access it directly from the request
        $request = $request->withAttribute('session', $this->sessionManager);

        // Process the request through the remaining middleware stack
        $response = $handler->handle($request);

        // Session cleanup could go here if needed
        // For example, certain flash messages might be cleared automatically

        return $response;
    }
}
