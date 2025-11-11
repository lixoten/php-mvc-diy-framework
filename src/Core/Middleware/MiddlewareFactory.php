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



        // TODO
        // attaching middleware d1irectly to routes in the router (as in your example below) is
        //    the more explicit and scalable pattern, does not rely on pipe-RoutePatternMiddleware
        // $router->add('/checkout', [
        //     'controller' => 'Checkout',
        //     'action' => 'index',
        //     'middleware' => [GeoLocationMiddleware::class]
        // ]);
        // TODO

        // 7.
        // for per-route geolocation ---
        $pipeline->pipe(
            new RoutePatternMiddleware(
                '/checkout*', // Adjust pattern as needed
                $container->get(GeoLocationMiddleware::class)
            )
        );
        // 8.
        // Enable geolocation for /testy* routes as well
        $pipeline->pipe(
            new RoutePatternMiddleware(
                '/testy*',
                $container->get(GeoLocationMiddleware::class)
            )
        );


        // 9. Authentication/Authorization Middleware (using RoutePatternMiddleware)
        // These now rely on attributes set by RoutingMiddleware
        // Guest-only middleware for login/registration pages
        // 9.
        // fixme  bypass temp
        // $pipeline->pipe(new RoutePatternMiddleware('/login', $container->get(GuestOnlyMiddleware::class)));
        // 10.
        // fixme  bypass temp
        // $pipeline->pipe(new RoutePatternMiddleware('/registration', $container->get(GuestOnlyMiddleware::class)));
        // 11.
        // fixme  bypass temp
        // $pipeline->pipe(new RoutePatternMiddleware('/forgot-password', $container->get(GuestOnlyMiddleware::class)));

        // ... other RoutePatternMiddleware for auth/roles ...
        // Important!!!
        // This is what allows us to use 'CORE' as i NOT 'STORE/' or 'ACCOUNT/' in urls and still populate STORE ID
        // --- http://mvclixo.tv/testy/list             <<<<< CORE
        // --- http://mvclixo.tv/store/testy/list       <<<<< STORE/
        // --- http://mvclixo.tv/account/testy/list     <<<<< ACCOUNT/ ---- no store id needed
        // Important!!!
        $storeAccountContextPatterns = [
            '/testy*', // dynamic-fix
            '/gallery*', // dynamic-fix
            '/image*', // dynamic-fix
            // '/posts*', // dynamic-fix
            // add more as needed
        ];


        // 12.
        $pipeline->pipe(new RoutePatternMiddleware('/account/*', $container->get(RequireAuthMiddleware::class)));
        // 13.
        $pipeline->pipe(new RoutePatternMiddleware('/admin/*', $container->get(RequireAuthMiddleware::class)));
        // 14.
        $pipeline->pipe(new RoutePatternMiddleware('/profile/*', $container->get(RequireAuthMiddleware::class)));
        // 15.
        $pipeline->pipe(new RoutePatternMiddleware('/store/*', $container->get(StoreContextMiddleware::class)));
        //$pipeline->pipe(new RoutePatternMiddleware('/posts', $container->get(StoreContextMiddleware::class)));
        foreach ($storeAccountContextPatterns as $pattern) {
            $pipeline->pipe(new RoutePatternMiddleware($pattern, $container->get(RequireAuthMiddleware::class)));
            $pipeline->pipe(new RoutePatternMiddleware($pattern, $container->get(StoreContextMiddleware::class)));
        }
        // 16.
        $pipeline->pipe(new RoutePatternMiddleware('/admin/*', $container->get(RequireRoleMiddleware::class)));
        // 17.
        $pipeline->pipe(new RoutePatternMiddleware('/user', $container->get(RequireRoleMiddleware::class)));


        // 18. Context Population (runs after routing and auth checks)
        $pipeline->pipe($container->get(ContextPopulationMiddleware::class));

        // Future middleware can be added here in the desired order
        // $pipeline->pipe($container->get(SecurityMiddleware::class));
        // $pipeline->pipe($container->get(AuthenticationMiddleware::class));

        return $pipeline;
    }
}
