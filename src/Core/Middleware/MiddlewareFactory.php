<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\FrontController;
use Core\Middleware\Auth\GuestOnlyMiddleware;
use Core\Middleware\Auth\RequireAuthMiddleware;
use Core\Middleware\Auth\RequireRoleMiddleware;
use Psr\Container\ContainerInterface;
use Core\Middleware\ContextPopulationMiddleware;
use Core\Middleware\RoutingMiddleware;

/**
 * Factory for creating and configuring middleware pipelines
 */
class MiddlewareFactory
{
    /**
     * Create a configured middleware pipeline
     *
     * @param ContainerInterface $container DI container
     * @return MiddlewarePipeline The configured pipeline
     */
    public static function createPipeline(ContainerInterface $container): MiddlewarePipeline
    {
        // Get front controller as the fallback handler
        $frontController = $container->get(FrontController::class);
        $pipeline = new MiddlewarePipeline($frontController);

        // 1. Timing
        $pipeline->pipe($container->get(TimingMiddleware::class));

        // 2. Error Handling
        $pipeline->pipe($container->get(ErrorHandlerMiddleware::class));

        // 3. Session Management
        $pipeline->pipe($container->get(SessionMiddleware::class));

        // 4. CSRF Protection
        $pipeline->pipe($container->get(CSRFMiddleware::class));

        // 5. Rate Limiting
        $pipeline->pipe($container->get(RateLimitMiddleware::class));

        // 6. Routing Middleware
        $pipeline->pipe($container->get(RoutingMiddleware::class));


        // 7. Authentication/Authorization Middleware (using RoutePatternMiddleware)
        // These now rely on attributes set by RoutingMiddleware
        // Guest-only middleware for login/registration pages
        $pipeline->pipe(new RoutePatternMiddleware('/login', $container->get(GuestOnlyMiddleware::class)));
        $pipeline->pipe(new RoutePatternMiddleware('/registration', $container->get(GuestOnlyMiddleware::class)));
        $pipeline->pipe(new RoutePatternMiddleware('/forgot-password', $container->get(GuestOnlyMiddleware::class)));

        // ... other RoutePatternMiddleware for auth/roles ...
        $storeContextPatterns = [
            '/posts*',
            // add more as needed
        ];


        $pipeline->pipe(new RoutePatternMiddleware('/account/*', $container->get(RequireAuthMiddleware::class)));
        $pipeline->pipe(new RoutePatternMiddleware('/admin/*', $container->get(RequireAuthMiddleware::class)));
        $pipeline->pipe(new RoutePatternMiddleware('/profile/*', $container->get(RequireAuthMiddleware::class)));
        $pipeline->pipe(new RoutePatternMiddleware('/stores/*', $container->get(StoreContextMiddleware::class)));
        //$pipeline->pipe(new RoutePatternMiddleware('/posts', $container->get(StoreContextMiddleware::class)));
        foreach ($storeContextPatterns as $pattern) {
            $pipeline->pipe(new RoutePatternMiddleware($pattern, $container->get(RequireAuthMiddleware::class)));
            $pipeline->pipe(new RoutePatternMiddleware($pattern, $container->get(StoreContextMiddleware::class)));
        }
        $pipeline->pipe(new RoutePatternMiddleware('/admin/*', $container->get(RequireRoleMiddleware::class)));
        $pipeline->pipe(new RoutePatternMiddleware('/users', $container->get(RequireRoleMiddleware::class)));


        // 8. Context Population (runs after routing and auth checks)
        $pipeline->pipe($container->get(ContextPopulationMiddleware::class));

        // Future middleware can be added here in the desired order
        // $pipeline->pipe($container->get(SecurityMiddleware::class));
        // $pipeline->pipe($container->get(AuthenticationMiddleware::class));

        return $pipeline;
    }
}
