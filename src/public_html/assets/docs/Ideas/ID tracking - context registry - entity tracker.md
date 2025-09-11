






What you're describing is a pattern I'd call a "Context Registry" - a centralized service that tracks various current IDs across your application. This is a powerful concept when dealing with multi-entity contexts.


Other names we can call this class or service



## Core Concept

You could implement a `ContextRegistry` service that:

1. Maintains a registry of current IDs by type
2. Validates IDs on access or automatically
3. Handles invalidation when records are deleted
4. Provides a clean API for controllers


Controllers would access this through dependency injection:

```php
class PostsController {
    public function __construct(ContextRegistry $contextRegistry) {
        $this->contextRegistry = $contextRegistry;
    }
    
    public function indexAction(): Response {
        $storeId = $this->contextRegistry->getCurrentId('store');
        $userId = $this->contextRegistry->getCurrentId('user');
        
        // Use the IDs...
    }
}
```

GitHub Copilot: # Simple Context Registry for ID Tracking


```php
<?php

declare(strict_types=1);

namespace Core\Context;

/**
 * A simple registry for tracking current entity IDs across the application
 */
class ContextRegistry
{

```



## Integration Points

This simple registry can be integrated at key points in your application flow:

### 1. When User Logs In
```php
// In SessionAuthenticationService after login
$contextRegistry = $this->container->get(ContextRegistry::class);
$contextRegistry->setCurrentId('user', $user->getUserId());

// If user has a store, set that too
if ($store) {
    $contextRegistry->setCurrentId('store', $store->getStoreId());
}
```

### 2. When Viewing a Post
```php
// In PostController::viewAction
public function viewAction(): ResponseInterface
{
    $postId = (int)$this->route_params['id'];
    $post = $this->postRepository->findById($postId);
    
    // Add to context registry
    $contextRegistry = $this->container->get(ContextRegistry::class);
    $contextRegistry->setCurrentId('post', $postId);
    
    // Continue with view logic...
}
```

### 3. When User Logs Out
```php
// In SessionAuthenticationService logout method
$contextRegistry = $this->container->get(ContextRegistry::class);
$contextRegistry->removeContext('user');
$contextRegistry->removeContext('store');
// Clear any other contexts that depend on user authentication
```

## Usage in Controllers

Any controller can now access the current context IDs:

```php
public function someAction(): ResponseInterface
{
    $contextRegistry = $this->container->get(ContextRegistry::class);
    
    $userId = $contextRegistry->getCurrentId('user');
    $storeId = $contextRegistry->getCurrentId('store');
    $postId = $contextRegistry->getCurrentId('post');
    
    // Use IDs as needed...
}
```


## Use Cases Where It Shines

1. **Multi-step Workflows**
   ```php
   // Step 1: Select a product
   $contextRegistry->setCurrentId('product', $productId);
   
   // Step 2: Configure options (product ID still available)
   $productId = $contextRegistry->getCurrentId('product');
   ```

2. **Dashboards with Context**
   ```php
   // User switches to a specific store view
   $contextRegistry->setCurrentId('active_store', $storeId);
   
   // All dashboard widgets now show data for this store
   ```

3. **Related Entity Operations**
   ```php
   // Viewing a post automatically sets post context
   // All comment operations now have post context available
   $postId = $contextRegistry->getCurrentId('post');
   ```


A potential compromise is a registry that returns specialized context objects:

```php
// Get specialized context services
$storeContext = $contextRegistry->getContext('store');
$userContext = $contextRegistry->getContext('user');

// Use their specialized methods
$storeName = $storeContext->getName();
$userEmail = $userContext->getEmail();

// But still have the simple ID access when that's all you need
$storeId = $contextRegistry->getCurrentId('store');
```

## Practical Considerations

For a medium-sized application, specialized context services often provide more value:

1. **User Context** - Handles authentication, roles, permissions
2. **Store Context** - Handles store selection, ownership validation
3. **Product Context** - Handles product selection, availability, etc.








What I've been describing is primarily a **Service** - specifically, a specialized application service that manages state.

The Context Registry (or CurrentState Manager, or whatever name you prefer) would be implemented as a service class that:

1. Is registered in your dependency container
2. Gets injected into controllers or other services
3. Maintains state throughout the application lifecycle
4. Provides methods to get/set current entity IDs

It's not a middleware because:
- It doesn't intercept HTTP requests
- It doesn't modify requests or responses
- It doesn't sit in the request processing pipeline

Unlike middleware which executes once per request in a specific order, this service would be:
- Persistent across requests (when using session storage)
- Accessed on-demand when needed
- Used for both reading and writing context information

In your architecture, it would be similar to your existing `StoreContext` service, but more generalized to handle multiple entity types.




















