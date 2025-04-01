<?php

declare(strict_types=1);

namespace Core\Middleware\Auth;

use Core\Auth\AuthenticationServiceInterface;
use Core\Http\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware that requires specific role for access
 */
class RequireRoleMiddleware extends AuthMiddleware
{
    /**
     * @var string|array
     */
    private $requiredRoles;

    /**
     * @var string
     */
    private string $unauthorizedUrl;

    /**
     * @var ResponseFactory
     */
    private ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param AuthenticationServiceInterface $authService
     * @param ResponseFactory $responseFactory
     * @param string|array $requiredRoles Single role or array of roles (any match grants access)
     * @param string $unauthorizedUrl URL to redirect to if user lacks required role
     */
    public function __construct(
        AuthenticationServiceInterface $authService,
        ResponseFactory $responseFactory,
        $requiredRoles,
        string $unauthorizedUrl = '/unauthorized'
    ) {
        parent::__construct($authService);
        $this->responseFactory = $responseFactory;
        $this->requiredRoles = $requiredRoles;
        $this->unauthorizedUrl = $unauthorizedUrl;
    }

    /**
     * Process the request
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // First check if user is authenticated
        if (!$this->authService->isAuthenticated()) {
            // Use RequireAuthMiddleware for this check instead
            return $handler->handle($request);
        }

        // Check if user has the required role(s)
        $hasRequiredRole = false;

        if (is_array($this->requiredRoles)) {
            // Check if user has any of the required roles
            foreach ($this->requiredRoles as $role) {
                if ($this->authService->hasRole($role)) {
                    $hasRequiredRole = true;
                    break;
                }
            }
        } else {
            // Check for single role
            $hasRequiredRole = $this->authService->hasRole($this->requiredRoles);
        }

        // If user doesn't have required role, redirect to unauthorized page
        if (!$hasRequiredRole) {
            return $this->responseFactory->redirect($this->unauthorizedUrl);
        }

        // User has required role, proceed with request
        return $handler->handle($request);
    }
}
