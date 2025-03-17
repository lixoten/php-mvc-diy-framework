<?php

declare(strict_types=1);

namespace Core\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PSR-15 compatible middleware interface
 *
 * Middleware components participate in processing a request and generating a response.
 * Each middleware may either terminate the request handling process and generate its
 * own response, or delegate to another request handler (typically another middleware).
 */
interface MiddlewareInterface
{
    /**
     * Process an incoming server request
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @param ServerRequestInterface $request The request to process
     * @param RequestHandlerInterface $handler The handler to delegate to if needed
     * @return ResponseInterface The processed response
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface;
}
