<?php
// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\Core\Middleware\ModelBindingMiddleware.php

declare(strict_types=1);

namespace Core\Middleware;

use App\Enums\FlashMessageType;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Exceptions\ForbiddenException;
use Core\Exceptions\NotFoundException;
use Core\Http\ResponseFactory;
use Core\Interfaces\ConfigInterface; // ✅ Use your existing ConfigService
use Core\Services\ModelBindingResolverService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * Model Binding Middleware
 *
 * Automatically resolves route parameters (e.g., {id}, {testy}) to their corresponding
 * entity objects by:
 * 1. Reading route parameters from the request (set by RoutingMiddleware)
 * 2. Looking up binding configuration from feature-specific config files
 * 3. Fetching the entity from the repository
 * 4. Running authorization checks if configured
 * 5. Attaching the resolved model(s) to the request as attributes
 *
 * This middleware must run AFTER RoutingMiddleware and BEFORE controllers.
 */
class ModelBindingMiddleware implements MiddlewareInterface
{
    /**
     * Constructor
     *
     * @param ConfigInterface $configService Configuration service for loading binding rules
     * @param ModelBindingResolverService $resolverService Service for resolving models from repositories
     * @param FlashMessageServiceInterface $flash Flash message service for user feedback
     * @param ResponseFactory $responseFactory Factory for creating redirect responses
     * @param LoggerInterface $logger Logger for debugging and error tracking
     */
    public function __construct(
        private ConfigInterface $configService, // ✅ Use your existing ConfigService
        private ModelBindingResolverService $resolverService,
        private FlashMessageServiceInterface $flash,
        private ResponseFactory $responseFactory,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Process the request to perform model binding
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // findme Model Binding
        // ✅ Step 1: Get route parameters (set by RoutingMiddleware)
        $routeParams = $request->getAttribute('route_params');

        if ($routeParams === null || empty($routeParams)) {
            return $handler->handle($request);
        }

        // ✅ Step 2: Extract controller and action for config lookup
        $controller = $routeParams['controller'] ?? null;
        $action = $routeParams['action'] ?? null;


        // ✅ Step 3: Validate before transformation
        if ($controller === null || $action === null) {
            return $handler->handle($request);
        }


        // ✅ Step 4: Transform action name to match config keys
        // Router gives us: 'edit', 'view', 'delete'
        // Config expects: 'editAction', 'viewAction', 'deleteAction'
        $action = $action . 'Action';


        // ✅ Step 5: Feature name is just the capitalized controller name
        $featureName = ucfirst($controller); // 'testy' → 'Testy'

        // $this->logger->debug("Model binding: processing route", [
        //     'controller' => $controller,
        //     'action' => $action,
        //     'feature_name' => $featureName,
        // ]);

        // ✅ Step 6: Load binding config using your existing ConfigService
        // Looks for: src/App/Features/Testy/Config/model_bindings.php
        $bindingConfig = $this->configService->getFromFeature(
            $featureName,
            "model_bindings.actions.{$action}" // ✅ Use dot notation for nested lookup
        );

        if ($bindingConfig === null) {
            $this->logger->debug("No model bindings configured for action", [
                'feature' => $featureName,
                'controller' => $controller,
                'action' => $action,
            ]);
            return $handler->handle($request);
        }

        // ✅ Step 5: Resolve models based on configuration
        $boundModels = [];

        foreach ($bindingConfig as $parameterName => $config) {
            try {
                // Get the ID value from route parameters
                $idParameterName = $config['parameter_name'] ?? 'id';
                $id = $routeParams[$idParameterName] ?? null;

                if ($id === null) {
                    $this->logger->warning("Model binding failed: Missing parameter '{$idParameterName}'", [
                        'controller' => $controller,
                        'action' => $action,
                        'route_params' => $routeParams,
                    ]);

                    throw new NotFoundException("Required parameter '{$idParameterName}' not found in route.");
                }

                // Resolve the model using the resolver service
                $model = $this->resolverService->resolve(
                    entityName: $parameterName,
                    id: (int)$id,
                    config: $config,
                    request: $request
                );

                if ($model === null) {
                    $entityLabel = ucfirst($parameterName);
                    $this->flash->add("{$entityLabel} not found.", FlashMessageType::Error);

                    throw new NotFoundException("{$entityLabel} with ID {$id} not found.");
                }

                // ✅ Step 6: Run authorization checks if configured
                if (isset($config['authorization']) && $config['authorization']['check'] === true) {
                    $isAuthorized = $this->resolverService->checkAuthorization(
                        model: $model,
                        config: $config['authorization'],
                        request: $request
                    );

                    if (!$isAuthorized) {
                        $entityLabel = ucfirst($parameterName);
                        $this->flash->add("You don't have permission to access this {$entityLabel}.", FlashMessageType::Error);

                        $this->logger->warning("Authorization failed for model binding", [
                            'entity' => $parameterName,
                            'id' => $id,
                            'user_id' => $request->getAttribute('user_id'),
                        ]);

                        throw new ForbiddenException("You don't have permission to access this resource.");
                    }
                }

                // ✅ Step 7: Store the resolved model
                $boundModels[$parameterName] = $model;

                $this->logger->debug("Model binding successful", [
                    'entity' => $parameterName,
                    'id' => $id,
                    'controller' => $controller,
                    'action' => $action,
                ]);

            } catch (NotFoundException | ForbiddenException $e) {
                throw $e;
            } catch (\Throwable $e) {
                $this->logger->error("Unexpected error during model binding", [
                    'entity' => $parameterName,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                throw new NotFoundException("Failed to resolve {$parameterName}: " . $e->getMessage(), 0, $e);
            }
        }

        // ✅ Step 8: Attach all resolved models to the request
        if (!empty($boundModels)) {
            $request = $request->withAttribute('bound_models', $boundModels);

            foreach ($boundModels as $name => $model) {
                $request = $request->withAttribute($name, $model);
            }
        }

        return $handler->handle($request);
    }

    /**
     * Extract feature name from controller class name
     *
     * @param string $controller Fully qualified controller class name
     * @return string|null Feature name or null if pattern doesn't match
     */
    private function xxxextractFeatureName(string $controller): ?string
    {
        // Example: 'App\Features\Testy\TestyController' → 'Testy'
        if (preg_match('/App\\\\Features\\\\([^\\\\]+)\\\\[^\\\\]+Controller$/', $controller, $matches)) {
            return $matches[1];
        }

        return null;
    }


    /**
     * Extract feature name from controller parameter
     *
     * Supports:
     * 1. FQCN: 'App\Features\Testy\TestyController' → 'Testy'
     * 2. Short name: 'testy' → 'Testy' (capitalize first letter)
     * 3. Short name with Controller suffix: 'TestyController' → 'Testy'
     *
     * @param string $controller Controller name from route_params
     * @return string|null Feature name (capitalized) or null if cannot extract
     */
    private function extractFeatureName(string $controller): ?string
    {
        // ✅ Attempt 1: Try FQCN pattern (App\Features\{FeatureName}\{FeatureName}Controller)
        if (preg_match('/App\\\\Features\\\\([^\\\\]+)\\\\[^\\\\]+Controller$/', $controller, $matches)) {
            $this->logger->debug("Feature name extracted from FQCN", [
                'controller' => $controller,
                'feature_name' => $matches[1],
            ]);
            return $matches[1];
        }

        // ✅ Attempt 2: Remove 'Controller' suffix if present (TestyController → Testy)
        if (preg_match('/^([A-Z][a-zA-Z0-9_]*)Controller$/', $controller, $matches)) {
            $featureName = $matches[1];
            $this->logger->debug("Feature name extracted by removing 'Controller' suffix", [
                'controller' => $controller,
                'feature_name' => $featureName,
            ]);
            return $featureName;
        }

        // ✅ Attempt 3: Assume it's a lowercase short name (testy → Testy)
        // This is the most likely case based on your router's behavior
        if (preg_match('/^[a-z][a-z0-9_]*$/', $controller)) {
            $featureName = ucfirst($controller); // ✅ Capitalize first letter
            $this->logger->debug("Feature name extracted from lowercase short name", [
                'controller' => $controller,
                'feature_name' => $featureName,
            ]);
            return $featureName;
        }

        // ❌ No pattern matched
        $this->logger->warning("Failed to extract feature name from controller", [
            'controller' => $controller,
            'expected_formats' => [
                'FQCN' => 'App\Features\Testy\TestyController',
                'Short with suffix' => 'TestyController',
                'Lowercase short' => 'testy',
            ],
        ]);

        return null;
    }

}
