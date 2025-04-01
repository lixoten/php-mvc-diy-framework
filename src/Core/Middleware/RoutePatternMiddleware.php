<?php

declare(strict_types=1);

namespace Core\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware that conditionally applies another middleware based on route pattern
 */
class RoutePatternMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    private string $pattern;

    /**
     * @var MiddlewareInterface
     */
    private MiddlewareInterface $middleware;

    /**
     * Constructor
     *
     * @param string $pattern Route pattern to match (supports * as wildcard)
     * @param MiddlewareInterface $middleware Middleware to apply if pattern matches
     */
    public function __construct(string $pattern, MiddlewareInterface $middleware)
    {
        $this->pattern = $pattern;
        $this->middleware = $middleware;
    }

    /**
     * Process the request
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $path = $request->getUri()->getPath();

        // Check if route matches pattern
        if ($this->matchesPattern($path)) {
            // Apply the inner middleware
            return $this->middleware->process($request, $handler);
        }

        // Pattern doesn't match, skip this middleware
        return $handler->handle($request);
    }

    /**
     * Check if path matches the pattern
     *
     * @param string $path
     * @return bool
     */
    private function matchesPattern(string $path): bool
    {
        // Convert pattern to regex
        $pattern = str_replace('/', '\/', $this->pattern);
        $pattern = str_replace('*', '.*', $pattern);
        $pattern = '/^' . $pattern . '$/';

        return (bool) preg_match($pattern, $path);
    }
}
