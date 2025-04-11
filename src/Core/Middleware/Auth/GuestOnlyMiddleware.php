<?php

declare(strict_types=1);

namespace Core\Middleware\Auth;

use Core\Auth\AuthenticationServiceInterface;
use Core\Http\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware that only allows guests (non-authenticated users)
 * Useful for login, registration, password reset pages
 */
class GuestOnlyMiddleware extends AuthMiddleware
{
    /**
     * @var string
     */
    private string $redirectUrl;

    /**
     * @var ResponseFactory
     */
    private ResponseFactory $responseFactory;

    /**
     * Constructor
     */
    public function __construct(
        AuthenticationServiceInterface $authService,
        ResponseFactory $responseFactory,
        string $redirectUrl = '/'
    ) {
        parent::__construct($authService);
        $this->responseFactory = $responseFactory;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * Process the request
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // If user is already authenticated, redirect away from login/registration pages
        if ($this->authService->isAuthenticated()) {
            // Check for return URL parameter
            $queryParams = $request->getQueryParams();
            $return = $queryParams['return'] ?? $this->redirectUrl;

            return $this->responseFactory->redirect($return);
        }

        // User is not authenticated, allow access to guest-only pages
        return $handler->handle($request);
    }
}
