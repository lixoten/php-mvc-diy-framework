# MVCLixo Middleware System Analysis

After examining your codebase, here's a detailed breakdown of your middleware system:

## Core Middleware Architecture

Your framework implements a PSR-15 compatible middleware system with these key components:

### 1. Framework Infrastructure

- **`MiddlewareInterface`**: PSR-15 compatible interface requiring a `process()` method
- **`MiddlewarePipeline`**: Manages middleware execution in FIFO order
- **`MiddlewareFactory`**: Configures the middleware stack in correct order

The pipeline is initialized in `public_html/index.php` where the request passes through all middleware before generating a response.

### 2. Request Processing Flow

```
Request → TimingMiddleware → ErrorHandlerMiddleware → SessionMiddleware → 
CSRFMiddleware → RateLimitMiddleware → Auth Middleware → FrontController → 
Router → Controller → Response
```

## Implemented Middleware Components

### Core Application Middleware

1. **`TimingMiddleware`**
   - First in pipeline to accurately measure total execution time
   - Adds `X-Execution-Time` header to responses
   - Simple diagnostic tool for performance monitoring

2. **`ErrorHandlerMiddleware`**
   - Catches exceptions from downstream middleware/handlers
   - Delegates to application's `ErrorHandler`
   - Ensures unified error handling throughout application

3. **`SessionMiddleware`**
   - Starts PHP session via `SessionManager`
   - Adds session to request attributes for controller access
   - Makes session data available throughout request lifecycle

4. **`CSRFMiddleware`**
   - Validates CSRF tokens on state-changing requests (POST, PUT, DELETE)
   - Excludes certain paths (e.g., `/api`) from CSRF checks
   - Adds CSRF token manager to request attributes

5. **`RateLimitMiddleware`**
   - Prevents abuse of sensitive endpoints (login, registration)
   - Maps URLs to action types using path mappings
   - Uses `BruteForceProtectionService` to track/limit attempts
   - Records attempts and updates success status after request processing

### Route-Specific Middleware

6. **`RoutePatternMiddleware`**
   - Conditionally applies other middleware based on URL patterns
   - Supports wildcard matching for path patterns

### Authentication Middleware Group

All authentication middleware extend `AuthMiddleware`:

7. **`RequireAuthMiddleware`**
   - Ensures users are authenticated for protected routes
   - Redirects to login page if not authenticated
   - Stores intended URL in session for post-login redirect

8. **`RequireRoleMiddleware`**
   - Verifies users have required roles (e.g., 'admin')
   - Supports checking for multiple roles with any-match logic
   - Redirects to unauthorized page if role check fails

9. **`GuestOnlyMiddleware`**
   - Protects login/registration from already authenticated users
   - Redirects authenticated users away from login/register pages

## Configuration

The middleware pipeline is configured in `MiddlewareFactory::createPipeline()` and dependency injection is set up in `dependencies.php`.

### Route Protection Examples

```php
// Guest-only pages
$pipeline->pipe(new RoutePatternMiddleware('/login', $container->get(GuestOnlyMiddleware::class)));
$pipeline->pipe(new RoutePatternMiddleware('/registration', $container->get(GuestOnlyMiddleware::class)));

// Authentication required
$pipeline->pipe(new RoutePatternMiddleware('/account/*', $container->get(RequireAuthMiddleware::class)));

// Role-based protection
$pipeline->pipe(new RoutePatternMiddleware('/admin/*', $container->get(RequireRoleMiddleware::class)));
```

This system provides a flexible, maintainable approach to request processing with good separation of concerns.