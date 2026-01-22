<?php
// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\Core\Services\ModelBindingResolverService.php

declare(strict_types=1);

namespace Core\Services;

use Core\Context\CurrentContext;
use Core\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Model Binding Resolver Service
 *
 * Resolves route parameters to entity objects by:
 * 1. Fetching the appropriate repository from the DI container
 * 2. Calling the configured repository method (e.g., findById)
 * 3. Checking authorization rules if configured
 *
 * Responsibilities (SRP):
 * - Fetch models from repositories based on configuration
 * - Run authorization checks using configured policies or simple owner checks
 * - Return resolved models or null if not found
 *
 * This service does NOT:
 * - Load configuration (that's ConfigService's job)
 * - Orchestrate the binding process (that's ModelBindingMiddleware's job)
 * - Handle HTTP responses (that's the middleware's job)
 *
 * @package Core\Services
 */
class ModelBindingResolverService
{
    /**
     * Constructor
     *
     * @param ContainerInterface $container DI container for resolving repository instances
     * @param CurrentContext $currentContext Current user/store context for authorization
     * @param LoggerInterface $logger Logger for debugging model resolution
     */
    public function __construct(
        private ContainerInterface $container,
        private CurrentContext $currentContext,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Resolve a model from a repository based on configuration
     *
     * This method:
     * 1. Retrieves the repository from the DI container
     * 2. Calls the configured method (e.g., 'findById' or 'findByIdWithFields')
     * 3. Returns the resolved model as an object (full entity) or array (selective fields), or null if not found
     *
     * @param string $entityName Entity name for logging (e.g., 'testy', 'post')
     * @param int $id The ID of the entity to fetch
     * @param array<string, mixed> $config Binding configuration for this entity
     * @param ServerRequestInterface $request The current request (for future extensions)
     * @return object|array<string, mixed>|null The resolved model (object if full fetch, array if selective fetch), or null if not found
     * @throws NotFoundException If the repository or method is not found
     *
     * Example config (full entity fetch):
     * [
     *     'repository' => 'App\Features\Testy\TestyRepositoryInterface',
     *     'method' => 'findById', // Returns Testy object
     * ]
     *
     * Example config (selective field fetch):
     * [
     *     'repository' => 'App\Features\Testy\TestyRepositoryInterface',
     *     'method' => 'findByIdWithFields', // Returns array ['id' => 2, 'user_id' => 5, ...]
     *     'fields' => ['id', 'user_id', 'store_id'],
     * ]
     */
    public function resolve(
        string $entityName,
        int $id,
        array $config,
        ServerRequestInterface $request
    ): object|array|null { // ✅ FIXED: Can return object, array, or null
            // ✅ Step 1: Extract repository interface from config
        $repositoryInterface = $config['repository'] ?? null;

        if ($repositoryInterface === null) {
            $this->logger->error("Model binding config missing 'repository' key", [
                'entity' => $entityName,
                'config' => $config,
            ]);
            throw new NotFoundException("Binding configuration for '{$entityName}' is missing 'repository' key.");
        }

        // ✅ Step 2: Resolve repository from DI container
        try {
            $repository = $this->container->get($repositoryInterface);
        } catch (\Throwable $e) {
            $this->logger->error("Failed to resolve repository from container", [
                'entity' => $entityName,
                'repository_interface' => $repositoryInterface,
                'error' => $e->getMessage(),
            ]);
            throw new NotFoundException("Repository '{$repositoryInterface}' not found in container.", 0, $e);
        }

        // ✅ Step 3: Extract method name from config (default: 'findById')
        $method = $config['method'] ?? 'findById';
        $fields = $config['fields'] ?? []; // ✅ May be auto-discovered by middleware

        if (!method_exists($repository, $method)) {
            $this->logger->error("Repository method does not exist", [
                'entity' => $entityName,
                'repository' => get_class($repository),
                'method' => $method,
            ]);
            throw new NotFoundException("Method '{$method}' does not exist on repository '" . get_class($repository) . "'.");
        }

        // ✅ Step 4: Call the repository method to fetch the model
        try {
            if (!empty($fields)) {
                // ✅ Use selective fetch if fields are configured
                if (method_exists($repository, 'findByIdWithFields')) {
                    $model = $repository->findByIdWithFields($id, $fields);
                } else {
                    // ✅ Fallback: Repository doesn't support selective fetch, use full fetch
                    $this->logger->warning("Repository does not support findByIdWithFields, using full fetch", [
                        'repository' => get_class($repository),
                    ]);
                    $model = $repository->$method($id);
                }
            } else {
                // ✅ No fields specified, use default repository method
                $model = $repository->$method($id);
            }

            if ($model === null) {
                $this->logger->debug("Model not found in repository", [
                    'entity' => $entityName,
                    'id' => $id,
                    'repository' => get_class($repository),
                    'method' => $method,
                ]);
            } else {
                $this->logger->debug("Model resolved successfully", [
                    'entity' => $entityName,
                    'id' => $id,
                    'model_type' => is_array($model) ? 'array' : get_class($model),
                    'fields_fetched' => !empty($fields) ? count($fields) : 'all',
                ]);
            }

            return $model;  // ✅ Returns object|array|null
        } catch (\Throwable $e) {
            $this->logger->error("Unexpected error calling repository method", [
                'entity' => $entityName,
                'id' => $id,
                'repository' => get_class($repository),
                'method' => $method,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new NotFoundException("Failed to resolve '{$entityName}': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Check if the current user is authorized to access the resolved model
     *
     * This method supports two authorization strategies:
     * 1. **Simple owner check:** Compare the model's owner field with the current user ID
     * 2. **Policy-based check:** Call a dedicated authorization policy class (future extension)
     *
     * @param object|array<string, mixed> $model The resolved model to check authorization for
     * @param array<string, mixed> $config Authorization configuration
     * @param ServerRequestInterface $request The current request (for future extensions)
     * @return bool True if authorized, false otherwise
     *
     * Example config (simple owner check):
     * [
     *     'check' => true,
     *     'owner_field' => 'user_id',
     *     'allowed_roles' => ['admin'],
     * ]
     *
     * Example config (policy-based, future):
     * [
     *     'check' => true,
     *     'policy' => 'App\Features\Testy\TestyAuthorizationPolicy',
     *     'method' => 'canEdit',
     * ]
     */
    public function checkAuthorization(
        object|array $model, // ✅ UPDATED: Can be object or array
        array $config,
        ServerRequestInterface $request
    ): bool {
        // ✅ Step 1: Get current user ID from context
        $currentUserId = $this->currentContext->getUserId();

        if ($currentUserId === null) {
            $this->logger->warning("Authorization check failed: No authenticated user", [
                'model_class' => get_class($model),
            ]);
            return false; // Not authenticated = not authorized
        }

        // ✅ Step 2: Check if a policy class is configured (future extension)
        if (isset($config['policy'])) {
            return $this->checkPolicyAuthorization($model, $config, $currentUserId, $request);
        }

        // ✅ Step 3: Fallback to simple owner check
        return $this->checkOwnerAuthorization($model, $config, $currentUserId);
    }


    /**
     * Simple owner-based authorization check
     *
     * Checks if:
     * 1. The current user is the owner of the resource (compares model's owner field)
     * 2. OR the current user has one of the allowed roles (e.g., 'admin')
     * 3. (Optional) For store-scoped resources, checks store context (store_id match)
     *
     * @param object|array<string, mixed> $model The resolved model (object or array)
     * @param array<string, mixed> $config Authorization configuration
     * @param int $currentUserId The current authenticated user's ID
     * @return bool True if authorized, false otherwise
     */
    private function checkOwnerAuthorization(
        object|array $model,
        array $config,
        int $currentUserId
    ): bool {
        // ✅ Step 1: Normalize model to array (handle both object and array)
        $modelArray = is_array($model) ? $model : $this->objectToArray($model);

        // ✅ Step 2: Extract owner field name from config (default: 'user_id')
        $ownerField = $config['owner_field'] ?? 'user_id';
        $resourceOwnerId = $modelArray[$ownerField] ?? null;

        if ($resourceOwnerId === null) {
            $this->logger->error("Owner field not found in model", [
                'owner_field' => $ownerField,
                'available_fields' => array_keys($modelArray),
            ]);
            return false; // Cannot verify ownership = deny access
        }

        // ✅ Step 3: Check if current user is the owner
        if ($resourceOwnerId === $currentUserId) {
            $this->logger->debug("Authorization successful: User is resource owner", [
                'user_id' => $currentUserId,
                'resource_owner_id' => $resourceOwnerId,
            ]);
            return true;
        }

        // ✅ Step 4: (Optional) Check store context for multi-tenant resources
        $storeField = $config['store_field'] ?? null;

        if ($storeField !== null) {
            $resourceStoreId = $modelArray[$storeField] ?? null;
            $currentStoreId = $this->currentContext->getStoreId();

            // If resource doesn't belong to current store context, deny access
            if ($resourceStoreId !== null && $resourceStoreId !== $currentStoreId) {
                $this->logger->warning("Authorization failed: Store context mismatch", [
                    'user_id' => $currentUserId,
                    'resource_store_id' => $resourceStoreId,
                    'current_store_id' => $currentStoreId,
                ]);
                return false;
            }
        }

        // ✅ Step 5: Check if current user has an allowed role (e.g., 'admin', 'store_owner')
        $allowedRoles = $config['allowed_roles'] ?? [];
        $userRoles = [];

        if (!empty($allowedRoles)) {
            $userRoles = $this->currentContext->getCurrentUserRoles();

            foreach ($allowedRoles as $allowedRole) {
                if (in_array($allowedRole, $userRoles, true)) {
                    // ✅ For 'store_owner' role, verify store context matches
                    if ($allowedRole === 'store_owner' && $storeField !== null) {
                        $resourceStoreId = $modelArray[$storeField] ?? null;
                        $currentStoreId = $this->currentContext->getStoreId();

                        if ($resourceStoreId !== $currentStoreId) {
                            // Store owner, but different store - deny access
                            continue;
                        }
                    }

                    $this->logger->debug("Authorization successful: User has allowed role", [
                        'user_id' => $currentUserId,
                        'user_roles' => $userRoles,
                        'allowed_role' => $allowedRole,
                    ]);
                    return true;
                }
            }
        }

        // ❌ Authorization failed: Not owner, no allowed role, or store context mismatch
        $this->logger->warning("Authorization failed: Not owner and no allowed role", [
            'user_id' => $currentUserId,
            'resource_owner_id' => $resourceOwnerId,
            'user_roles' => $userRoles,
            'allowed_roles' => $allowedRoles,
        ]);

        return false;
    }

    /**
     * Simple owner-based authorization check
     *
     * Checks if:
     * 1. The current user is the owner of the resource (compares model's owner field)
     * 2. OR the current user has one of the allowed roles (e.g., 'admin')
     * 3. (Optional) For store-scoped resources, checks store context (store_id match)
     *
     * @param object|array<string, mixed> $model The resolved model (object or array)
     * @param array<string, mixed> $config Authorization configuration
     * @param int $currentUserId The current authenticated user's ID
     * @return bool True if authorized, false otherwise
     */
    private function checkOwnerAuthorizationxxx(
        object|array $model, // ✅ UPDATED: Can be object or array
        array $config,
        int $currentUserId
    ): bool {
        // ✅ Step 1a: Normalize model to array (handle both object and array)
        $modelArray = is_array($model) ? $model : $this->objectToArray($model);

        // ✅ Step 1b: Extract owner field name from config (default: 'user_id')
        $ownerField = $config['owner_field'] ?? 'user_id';


        // ✅ Step 2: Get the owner ID from the model (handle both object and array)
        if (is_array($model)) {
            // ✅ Model is an array (selective fetch)
            $resourceOwnerId = $model[$ownerField] ?? null;

            if ($resourceOwnerId === null) {
                $this->logger->error("Owner field not found in model array", [
                    'owner_field' => $ownerField,
                    'available_fields' => array_keys($model),
                ]);
                return false; // Cannot verify ownership = deny access
            }
        } else {
            // ✅ Model is an object (full entity fetch)
            $getterMethod = 'get' . str_replace('_', '', ucwords($ownerField, '_'));

            if (!method_exists($model, $getterMethod)) {
                $this->logger->error("Owner field getter method does not exist on model", [
                    'model_class' => get_class($model),
                    'owner_field' => $ownerField,
                    'expected_getter' => $getterMethod,
                ]);
                return false; // Cannot verify ownership = deny access
            }

            $resourceOwnerId = $model->$getterMethod();
        }


        // ✅ Step 3: Check if current user is the owner
        if ($resourceOwnerId === $currentUserId) {
            $this->logger->debug("Authorization successful: User is resource owner", [
                'user_id' => $currentUserId,
                'resource_owner_id' => $resourceOwnerId,
            ]);
            return true;
        }

        // ✅ Step 4: (Optional) Check store context for multi-tenant resources
        // If the model has a 'store_id' field and config specifies 'store_field',
        // verify the resource belongs to the user's current store
        $storeField = $config['store_field'] ?? null;

        if ($storeField !== null) {
            // ✅ Get store ID from model (handle both object and array)
            if (is_array($model)) {
                $resourceStoreId = $model[$storeField] ?? null;
            } else {
                $storeGetterMethod = 'get' . str_replace('_', '', ucwords($storeField, '_'));

                if (method_exists($model, $storeGetterMethod)) {
                    $resourceStoreId = $model->$storeGetterMethod();
                } else {
                    $resourceStoreId = null;
                }
            }

            $currentStoreId = $this->currentContext->getStoreId();

            // If resource doesn't belong to current store context, deny access
            if ($resourceStoreId !== null && $resourceStoreId !== $currentStoreId) {
                $this->logger->warning("Authorization failed: Store context mismatch", [
                    'user_id' => $currentUserId,
                    'resource_store_id' => $resourceStoreId,
                    'current_store_id' => $currentStoreId,
                ]);
                return false;
            }
        }


        // ✅ Step 5: Check if current user has an allowed role (e.g., 'admin', 'store_owner')
        $allowedRoles = $config['allowed_roles'] ?? []; // ✅ FIXED: Define before using
        $userRoles = []; // ✅ FIXED: Initialize outside if block

        if (!empty($allowedRoles)) {
            $userRoles = $this->currentContext->getCurrentUserRoles();

            foreach ($allowedRoles as $allowedRole) {
                if (in_array($allowedRole, $userRoles, true)) {
                    // ✅ For 'store_owner' role, verify store context matches
                    if ($allowedRole === 'store_owner' && $storeField !== null) {
                        // ✅ Get store ID from model (handle both object and array)
                        if (is_array($model)) {
                            $resourceStoreId = $model[$storeField] ?? null;
                        } else {
                            $storeGetterMethod = 'get' . str_replace('_', '', ucwords($storeField, '_'));
                            if (method_exists($model, $storeGetterMethod)) {
                                $resourceStoreId = $model->$storeGetterMethod();
                            } else {
                                $resourceStoreId = null;
                            }
                        }

                        $currentStoreId = $this->currentContext->getStoreId();

                        if ($resourceStoreId !== $currentStoreId) {
                            // Store owner, but different store - deny access
                            continue;
                        }
                    }

                    $this->logger->debug("Authorization successful: User has allowed role", [
                        'user_id' => $currentUserId,
                        'user_roles' => $userRoles,
                        'allowed_role' => $allowedRole,
                    ]);
                    return true;
                }
            }
        }

        // ❌ Authorization failed: Not owner, no allowed role, or store context mismatch
        $this->logger->warning("Authorization failed: Not owner and no allowed role", [
            'user_id' => $currentUserId,
            'resource_owner_id' => $resourceOwnerId,
            'user_roles' => $userRoles ?? [],
            'allowed_roles' => $allowedRoles,
        ]);

        return false;
    }


    // important!!! FUTURE Feature....what ifs
    // -- Business Rule: "Users can only edit Testy records that are less than 7 days old."
    // -- Business Rule: "Users can only edit Testy records that are in 'draft' status. Published records cannot be edited."
    // Note: policies should be Feature Specific? AI told me yes....
    // important!!! FUTURE Feature
    /**
     * Policy-based authorization check (future extension)
     *
     * Calls a dedicated authorization policy class to determine access.
     * This is more flexible than simple owner checks and supports complex business rules.
     *
     * @param object|array<string, mixed> $model The resolved model (object or array)
     * @param array<string, mixed> $config Authorization configuration
     * @param int $currentUserId The current authenticated user's ID
     * @param ServerRequestInterface $request The current request
     * @return bool True if authorized, false otherwise
     */
    private function checkPolicyAuthorization(
        object|array $model, // ✅ FIXED: Accept both object and array
        array $config,
        int $currentUserId,
        ServerRequestInterface $request
    ): bool {
        // ✅ Step 1: Normalize model to array (same pattern as checkOwnerAuthorization)
        $modelArray = is_array($model) ? $model : $this->objectToArray($model);

        // ✅ Step 2: Extract policy class and method from config
        $policyClass = $config['policy'];
        $policyMethod = $config['method'] ?? 'authorize';

        // ✅ Step 3: Resolve policy from container
        try {
            $policy = $this->container->get($policyClass);
        } catch (\Throwable $e) {
            $this->logger->error("Failed to resolve authorization policy from container", [
                'policy_class' => $policyClass,
                'error' => $e->getMessage(),
            ]);
            return false; // Cannot verify = deny access
        }

        // ✅ Step 4: Check if policy method exists
        if (!method_exists($policy, $policyMethod)) {
            $this->logger->error("Policy method does not exist", [
                'policy_class' => $policyClass,
                'method' => $policyMethod,
            ]);
            return false;
        }

        // ✅ Step 5: Call policy method with normalized array
        try {
            // ✅ Pass array to policy (policies should work with arrays, not entities)
            $isAuthorized = $policy->$policyMethod($currentUserId, $modelArray, $request);

            $this->logger->debug("Policy-based authorization check completed", [
                'policy_class' => $policyClass,
                'method' => $policyMethod,
                'user_id' => $currentUserId,
                'model_type' => is_array($model) ? 'array' : get_class($model),
                'authorized' => $isAuthorized,
            ]);

            return (bool)$isAuthorized;
        } catch (\Throwable $e) {
            $this->logger->error("Error calling authorization policy", [
                'policy_class' => $policyClass,
                'method' => $policyMethod,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false; // Error = deny access
        }
    }


    /**
     * Convert entity object to array for authorization checks
     *
     * Uses reflection to extract all property values (public, protected, private)
     *
     * @param object $model The entity object to convert
     * @return array<string, mixed> Associative array of property name => value
     */
    private function objectToArray(object $model): array
    {
        $reflection = new \ReflectionClass($model);
        $properties = $reflection->getProperties();

        $data = [];
        foreach ($properties as $property) {
            $property->setAccessible(true); // ✅ Allow access to private/protected properties
            $data[$property->getName()] = $property->getValue($model);
        }

        return $data;
    }
}