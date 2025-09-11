<?php

declare(strict_types=1);

namespace Core\Middleware;

use App\Helpers\DebugRt;
use Core\Auth\Exception\AuthenticationException;
use Core\Http\HttpFactory;
use App\Services\Interfaces\FlashMessageServiceInterface;
use App\Enums\FlashMessageType;
use Core\Interfaces\ConfigInterface;
use Core\Security\RateLimitServiceInterface;
use Core\Security\BruteForceProtectionService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    private RateLimitServiceInterface $rateLimitService;
    // private BruteForceProtectionService $protectionService; // foofee
    private HttpFactory $httpFactory;
    private FlashMessageServiceInterface $flash;
    private ConfigInterface $configService;

    public function __construct(
        RateLimitServiceInterface $rateLimitService,
        // BruteForceProtectionService $protectionService, // foofee
        HttpFactory $httpFactory,
        FlashMessageServiceInterface $flash,
        ConfigInterface $configService,
    ) {
        $this->rateLimitService = $rateLimitService;
        // $this->protectionService = $protectionService;  // foofee
        $this->httpFactory = $httpFactory;
        $this->flash = $flash;
        $this->configService = $configService;
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // config-ok
        if (!$this->configService->getConfigValue('security', 'rate_limits.enabled', true)) {
            return $handler->handle($request);
        }

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
                $this->rateLimitService->checkRateLimit(
                    $identifier,
                    $actionType,
                    $ipAddress
                );
                //DebugRt::j('0', 'method', $method);
                // Record the attempt (success will be updated later)
                // if ($method === 'POST') {
                if ($method === 'POST' || ($method === 'GET' && $actionType === 'email_verification')) {
                    // Record both POST and verification GET requests
                    $this->rateLimitService->recordAttempt(
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
                    $this->rateLimitService->updateLastAttemptStatus(
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
        // Get security rate_limits path mappings
        // config-ok
        $pathMappings = $this->configService->getConfigValue('security', 'rate_limits.path_mappings', []);
        //DebugRt::j('0', 'Path mappings', $pathMappings);

        // Clean up path for comparison
        $path = '/' . ltrim($path, '/');
        //DebugRt::j('0', 'Path to match', $path);

        // Check for exact matches
        if (isset($pathMappings[$path])) {
            //DebugRt::j("1", 'sssss', $pathMappings[$path]);
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
        //DebugRt::j('1', '123path', $path);

        return null;
    }
}
