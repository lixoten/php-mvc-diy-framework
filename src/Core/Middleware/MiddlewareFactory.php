<?php

declare(strict_types=1);

namespace Core\Middleware;

use Core\FrontController;
use Psr\Container\ContainerInterface;

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

        // Create the pipeline with the front controller as fallback
        $pipeline = new MiddlewarePipeline($frontController);

        // Add middleware in the correct processing order

        // TimingMiddleware should be first to accurately measure total execution time
        $pipeline->pipe($container->get(TimingMiddleware::class));

        // ErrorHandlerMiddleware comes after timing to ensure errors are handled
        // but still get timing information
        $pipeline->pipe($container->get(ErrorHandlerMiddleware::class));

        // SessionMiddleware should come early in the stack to make session available
        // to most other middleware and all controllers
        $pipeline->pipe($container->get(SessionMiddleware::class));

        // In the createPipeline method, add this after SessionMiddleware:
        $pipeline->pipe($container->get(CSRFMiddleware::class));

        // Future middleware can be added here in the desired order
        // $pipeline->pipe($container->get(SecurityMiddleware::class));
        // $pipeline->pipe($container->get(CsrfMiddleware::class));
        // $pipeline->pipe($container->get(AuthenticationMiddleware::class));

        return $pipeline;
    }
}
