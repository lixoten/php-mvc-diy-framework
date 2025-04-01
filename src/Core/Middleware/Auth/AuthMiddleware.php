<?php

declare(strict_types=1);

namespace Core\Middleware\Auth;

use Core\Auth\AuthenticationServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Base class for authentication-related middleware
 */
abstract class AuthMiddleware implements MiddlewareInterface
{
    /**
     * @var AuthenticationServiceInterface
     */
    protected AuthenticationServiceInterface $authService;

    /**
     * Constructor
     */
    public function __construct(AuthenticationServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Process the request through the middleware
     */
    abstract public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface;
}
