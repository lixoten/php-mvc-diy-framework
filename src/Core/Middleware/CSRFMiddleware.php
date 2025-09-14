<?php

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

            // Auto Save / Draft Feature - JS FIX // js-feature
            // Get submitted token
            $body = $request->getParsedBody();
            if (empty($body)) {
                $body = json_decode((string)$request->getBody(), true) ?? [];
            }
            $submittedToken = $body['csrf_token'] ?? '';

            // error_log('Session status: ' . session_status());// js-feature
            // error_log('Session ID: ' . session_id());
            // error_log('Submitted token: ' . $submittedToken);
            // error_log('Expected token: ' . $this->csrfToken->getToken());

            // Validate the token
            if (!$submittedToken || !$this->csrfToken->validate($submittedToken)) {
                $isAjaxRequest = strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';

                // AJAX Save Feature - JS
                if ($isAjaxRequest) { // js-feature
                    $response = $this->httpFactory->createResponse(403);

                    error_log(
                        'TEST ERRORLOG in CSRF Middleware - isAjaxRequest is true'
                            . json_encode($response->getStatusCode())
                    );

                    $response->getBody()->write(json_encode([
                        'success' => false,
                        'message' => 'CSRF token validation failed. Please try again.'
                    ]));
                    return $response->withHeader('Content-Type', 'application/json');
                } else {
                    error_log('TEST ERRORLOG in CSRF Middleware - isAjaxRequest is false');

                    $response = $this->httpFactory->createResponse(403);
                    $response->getBody()->write('CSRF token validation failed. Please try again.');
                    return $response;
                }
            }
        }

        // Add the CSRF token manager to the request attributes for use in controllers
        $request = $request->withAttribute('csrf', $this->csrfToken);

        // Token is valid or not required, continue processing
        return $handler->handle($request);
    }
}
