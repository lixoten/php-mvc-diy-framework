<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\ErrorHandler;
use Core\Http\HttpFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * Error Handler Middleware
 *
 * Catches exceptions thrown during request processing and converts them
 * to appropriate HTTP responses, ensuring middleware after this one
 * still get to process the response.
 */
class ErrorHandlerMiddleware implements MiddlewareInterface
{
    private ErrorHandler $errorHandler;

    public function __construct(ErrorHandler $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            // Try to handle the request normally
            return $handler->handle($request);
        } catch (\Throwable $e) {
            // Delegate to the application's error handler
            return $this->errorHandler->handleException($e);
        }
    }
}
