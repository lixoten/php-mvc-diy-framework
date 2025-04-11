<?php

declare(strict_types=1);

namespace Core\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// use Psr\Http\Message\ResponseInterface;
// use Psr\Http\Message\ServerRequestInterface;

/**
 * Timing Middleware
 *
 * A simple middleware that measures request execution time and
 * adds an X-Execution-Time header to the response.
 *
 * This serves as an example middleware implementation and provides
 * useful debugging information.
 */
class TimingMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     * @return ResponseInterface The response
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Record start time
        $startTime = microtime(true);

        // Process the request through the rest of the middleware stack
        //error_log('111 TimingMiddleware executed for: ' . $request->getUri()->getPath());
        $response = $handler->handle($request);
        //error_log('222 TimingMiddleware executed for: ' . $request->getUri()->getPath());

        // Calculate execution time in milliseconds
        $executionTime = (microtime(true) - $startTime) * 1000;

        // Add execution time header to response
        return $response->withHeader(
            'X-Execution-Time',
            sprintf('%.2fms', $executionTime)
        );
    }
}
