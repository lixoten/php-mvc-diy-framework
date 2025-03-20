<?php

// filepath: d:\xampp\htdocs\mvclixo\src\Core\Middleware\CSRFMiddleware.php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Form\CSRF\CSRFToken;
use Core\Http\HttpFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CSRFMiddleware implements MiddlewareInterface
{
    private CSRFToken $csrfToken;
    private HttpFactory $httpFactory;
    private array $excludedPaths;

    /**
     * Create a new CSRF middleware
     *
     * @param CSRFToken $csrfToken CSRF token manager
     * @param HttpFactory $httpFactory HTTP factory for creating responses
     * @param array $excludedPaths URL paths to exclude from CSRF validation
     */
    public function __construct(
        CSRFToken $csrfToken,
        HttpFactory $httpFactory,
        array $excludedPaths = []
    ) {
        $this->csrfToken = $csrfToken;
        $this->httpFactory = $httpFactory;
        $this->excludedPaths = $excludedPaths;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Only validate POST, PUT, DELETE, PATCH requests
        $method = strtoupper($request->getMethod());

        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            // Check if path is excluded from CSRF protection
            $path = $request->getUri()->getPath();
            foreach ($this->excludedPaths as $excludedPath) {
                if (strpos($path, $excludedPath) === 0) {
                    // Skip CSRF validation for excluded paths
                    return $handler->handle($request);
                }
            }

            // Get submitted token
            $body = $request->getParsedBody();
            $submittedToken = $body['csrf_token'] ?? '';

            // Validate the token
            if (!$submittedToken || !$this->csrfToken->validate($submittedToken)) {
                // Token validation failed - create 403 response
                $response = $this->httpFactory->createResponse(403);
                $response->getBody()->write('CSRF token validation failed. Please try again.');
                return $response;
            }
        }

        // Add the CSRF token manager to the request attributes for use in controllers
        $request = $request->withAttribute('csrf', $this->csrfToken);

        // Token is valid or not required, continue processing
        return $handler->handle($request);
    }
}
