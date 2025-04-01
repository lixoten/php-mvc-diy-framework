<?php

declare(strict_types=1);

namespace Core\Middleware\Auth;

use Core\Auth\AuthenticationServiceInterface;
use Core\Http\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware that requires authentication for specific routes
 */
class RequireAuthMiddleware extends AuthMiddleware
{
    /**
     * @var string
     */
    private string $loginUrl;

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
        string $loginUrl = '/login'
    ) {
        parent::__construct($authService);
        $this->responseFactory = $responseFactory;
        $this->loginUrl = $loginUrl;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Check if user is authenticated
        if (!$this->authService->isAuthenticated()) {
            // Store original URL in SESSION (not just in URL)
            $currentUrl = (string) $request->getUri();
            $_SESSION['auth_intended_url'] = $currentUrl;

            // Redirect to login page with return URL
            $redirectUrl = $this->loginUrl;
            if ($currentUrl !== '/' && $currentUrl !== $this->loginUrl) {
                $redirectUrl .= '?return=' . urlencode($currentUrl);
            }

            return $this->responseFactory->redirect($redirectUrl);
        }

        // User is authenticated, proceed with request
        return $handler->handle($request);
    }
}
