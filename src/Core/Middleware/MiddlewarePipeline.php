<?php

declare(strict_types=1);

namespace Core\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware Pipeline
 *
 * Acts as both a middleware dispatcher and a request handler.
 * Processes middleware in FIFO (First In, First Out) order and delegates
 * to a fallback handler when the middleware stack is empty.
 */
class MiddlewarePipeline implements RequestHandlerInterface
{
    /**
     * @var MiddlewareInterface[] Array of middleware instances
     */
    private array $middleware = [];

    /**
     * @var RequestHandlerInterface Handler to invoke when middleware stack is empty
     */
    private RequestHandlerInterface $fallbackHandler;

    /**
     * Create a new middleware pipeline with a fallback handler
     *
     * @param RequestHandlerInterface $fallbackHandler The handler to use when middleware stack is empty
     */
    public function __construct(RequestHandlerInterface $fallbackHandler)
    {
        $this->fallbackHandler = $fallbackHandler;
    }

    /**
     * Add middleware to the pipeline
     *
     * @param MiddlewareInterface $middleware The middleware to add
     * @return self
     */
    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Handle the request by processing it through middleware
     *
     * @param ServerRequestInterface $request The request to handle
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // If there's no middleware left, use the fallback handler
        if (empty($this->middleware)) {
            return $this->fallbackHandler->handle($request);
        }

        // Take the first middleware from the stack
        $middleware = array_shift($this->middleware);

        // dangerdanger
        $sc = $_SERVER['SCRIPT_NAME'];
        file_put_contents('exit.log', 'EXIT pipil  HIT: ' . date('c') . ' ' . $sc . ' '. ($_SERVER['REQUEST_URI'] ?? '') . PHP_EOL, FILE_APPEND);
        // Process the request through this middleware
        return $middleware->process($request, $this);
    }
}
