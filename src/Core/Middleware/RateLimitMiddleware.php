<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\Security\BruteForceProtectionService;
use Core\Auth\Exception\AuthenticationException;
use Core\Http\HttpFactory;
use App\Services\Interfaces\FlashMessageServiceInterface;
use App\Enums\FlashMessageType;
use App\Helpers\DebugRt;
// use App\Helpers\DebugRt as Debug;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    private BruteForceProtectionService $protectionService;
    private HttpFactory $httpFactory;
    private FlashMessageServiceInterface $flash;
    private array $configPath;

    public function __construct(
        BruteForceProtectionService $protectionService,
        HttpFactory $httpFactory,
        FlashMessageServiceInterface $flash,
        array $configPath = []
    ) {
        $this->protectionService = $protectionService;
        $this->httpFactory = $httpFactory;
        $this->flash = $flash;
        $this->configPath = $configPath;
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();
        $ipAddress = $_SERVER['REMOTE_ADDR'];

        // Determine action type based on path
        $actionType = $this->getActionTypeForPath($path, $method);
        //DebugRt::j('0', 'actionType', $actionType);
        if ($actionType) {
            // Use IP as identifier for pre-auth requests
            $identifier = $ipAddress;

            try {
                // Check rate limit
                $this->protectionService->checkRateLimit(
                    $identifier,
                    $actionType,
                    $ipAddress
                );
                //DebugRt::j('0', 'method', $method);
                // Record the attempt (success will be updated later)
                // if ($method === 'POST') {
                if ($method === 'POST' || ($method === 'GET' && $actionType === 'email_verification')) {
                    // Record both POST and verification GET requests
                    $this->protectionService->recordAttempt(
                        $identifier,
                        $actionType,
                        $ipAddress,
                        false, // Assume failure initially
                        $_SERVER['HTTP_USER_AGENT'] ?? null
                    );
                }

                // Process the request
                $response = $handler->handle($request);

                // Mark as successful if response code indicates success
                if (
                    ($method === 'POST' || ($method === 'GET' && $actionType === 'email_verification')) &&
                    $response->getStatusCode() < 400
                ) {
                    // Update the existing record instead of creating a duplicate
                    $this->protectionService->updateLastAttemptStatus(
                        $identifier,
                        $actionType,
                        true // Success
                    );
                }

                return $response;
            } catch (AuthenticationException $e) {
                // Add error message to flash
                $this->flash->add(
                    "Too many attempts. Please try again later.",
                    FlashMessageType::Error
                );

                // Redirect back or to home page
                $response = $this->httpFactory->createResponse(302);
                $response = $response->withHeader('Location', '/');

                return $response;
            }
        }

        // No rate limiting for this path
        return $handler->handle($request);
    }

    /**
     * {@inheritdoc}
     */
    private function getActionTypeForPath(string $path, string $method): ?string
    {
        // Default mapping if no specific configPath provided
        $pathMappings = $this->configPath['path_mappings'] ?? [
            '/registration' => 'registration',
            '/login' => 'login',
            '/forgot-password' => 'password_reset',
            '/verify-email/resend' => 'activation_resend',
            '/verify-email/verify' => 'email_verification',
        ];

        // Clean up path for comparison
        $path = '/' . ltrim($path, '/');
        //DebugRt::j("0", '', $pathMappings[$path]);
        // Check for exact matches
        if (isset($pathMappings[$path])) {
            return $pathMappings[$path];
        }
        // http://mvclixo.tv/verify-email/resend
        // http://mvclixo.tv/verify-email/verify?token=c27ff561fd24df4929512bcda6cb2487823bd69647c9c515da2318df787c0ce0

        // Check for pattern matches
        foreach ($pathMappings as $pattern => $action) {
            if (strpos($pattern, '*') !== false) {
                $regex = '#^' . str_replace('*', '.*', $pattern) . '$#';
                if (preg_match($regex, $path)) {
                    return $action;
                }
            }
        }

        return null;
    }
}
