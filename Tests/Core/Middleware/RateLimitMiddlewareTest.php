<?php

declare(strict_types=1);

namespace Tests\Core\Middleware;

use App\Enums\FlashMessageType;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Auth\Exception\AuthenticationException;
use Core\Http\HttpFactory;
use Core\Middleware\RateLimitMiddleware;
use Core\Security\RateLimitServiceInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitMiddlewareTest extends TestCase
{
    private $rateLimitService;
    private $httpFactory;
    private $flash;
    private $middleware;
    private $request;
    private $handler;
    private $uri;
    private $response;

    protected function setUp(): void
    {
        $this->rateLimitService = $this->createMock(RateLimitServiceInterface::class);
        $this->httpFactory = $this->createMock(HttpFactory::class);
        $this->flash = $this->createMock(FlashMessageServiceInterface::class);

        $this->middleware = new RateLimitMiddleware(
            $this->rateLimitService,
            $this->httpFactory,
            $this->flash,
            [
                'path_mappings' => [
                    '/login' => 'login',
                    '/registration' => 'registration',
                    '/forgot-password' => 'password_reset',
                    '/verify-email/resend' => 'activation_resend',
                    '/verify-email/verify' => 'email_verification'
                ]
            ]
        );

        // Common request/response mocks
        $this->uri = $this->createMock(UriInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->handler = $this->createMock(RequestHandlerInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);

        // Default behavior
        $this->request->method('getServerParams')->willReturn(['REMOTE_ADDR' => '127.0.0.1']);
        $this->request->method('getUri')->willReturn($this->uri);
        $this->handler->method('handle')->willReturn($this->response);
        $this->response->method('getStatusCode')->willReturn(200);
    }

    public function testProcessAllowsNonRateLimitedPath(): void
    {
        // Setup a path that's not rate limited
        $this->uri->method('getPath')->willReturn('/about');
        $this->request->method('getMethod')->willReturn('GET');

        // Service shouldn't be called
        $this->rateLimitService->expects($this->never())
            ->method('checkRateLimit');

        // Process should pass to handler
        $result = $this->middleware->process($this->request, $this->handler);

        // Should return response from handler
        $this->assertSame($this->response, $result);
    }

    public function testProcessRateLimitedPathSuccessfully(): void
    {
        // Setup login path
        $this->uri->method('getPath')->willReturn('/login');
        $this->request->method('getMethod')->willReturn('POST');

        // Service should check rate limit and not throw
        $this->rateLimitService->expects($this->once())
            ->method('checkRateLimit')
            ->with('127.0.0.1', 'login', '127.0.0.1');

        // Record attempt should be called
        $this->rateLimitService->expects($this->once())
            ->method('recordAttempt')
            ->with('127.0.0.1', 'login', '127.0.0.1', false);

        // Update status should be called for success
        $this->rateLimitService->expects($this->once())
            ->method('updateLastAttemptStatus')
            ->with('127.0.0.1', 'login', true);

        // Process should pass to handler and get response
        $result = $this->middleware->process($this->request, $this->handler);

        // Should return handler's response
        $this->assertSame($this->response, $result);
    }

    public function testProcessHandlesRateLimitExceeded(): void
    {
        // Setup login path
        $this->uri->method('getPath')->willReturn('/login');
        $this->request->method('getMethod')->willReturn('POST');

        // Service throws rate limit exception
        $this->rateLimitService->expects($this->once())
            ->method('checkRateLimit')
            ->willThrowException(new AuthenticationException('Too many attempts'));

        // Flash message should be added
        $this->flash->expects($this->once())
            ->method('add')
            ->with('Too many attempts. Please try again later.', FlashMessageType::Error);

        // Response should be created for redirect
        $this->httpFactory->expects($this->once())
            ->method('createResponse')
            ->with(302)
            ->willReturn($this->response);

        $this->response->expects($this->once())
            ->method('withHeader')
            ->with('Location', '/')
            ->willReturn($this->response);

        // Process should handle the exception
        $result = $this->middleware->process($this->request, $this->handler);

        // Should return redirect response
        $this->assertSame($this->response, $result);
    }
}
