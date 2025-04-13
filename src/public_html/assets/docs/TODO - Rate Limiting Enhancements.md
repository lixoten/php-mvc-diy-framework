# TODO: Rate Limiting Future Enhancements

## 1. Role-Based Limits & Exemptions

### Why Implement
Role-based rate limiting provides more granular control over system access, ensuring that:
- Administrative users can perform bulk operations without hitting limits
- Trusted users get higher thresholds than new or guest users
- Critical administrative functions remain accessible during high traffic

### Implementation Plan

#### In RateLimitMiddleware:
```php
public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
{
    // Get current user and role
    $user = $request->getAttribute('user');
    $role = $user?->getRole() ?? 'guest';
    
    // Check for exempt paths or roles
    if (
        in_array($path, $this->config['exempt_paths'] ?? []) ||
        in_array($role, $this->config['exempt_roles'] ?? [])
    ) {
        return $handler->handle($request);
    }
    
    // Continue with standard rate limiting...
}
```

#### In RateLimitService:
```php
public function checkRateLimit(string $identifier, string $actionType, string $ipAddress, ?string $role = 'guest'): void
{
    // Base limit from configuration
    $baseLimit = $this->config[$actionType]['limit'] ?? 0;
    
    // Apply role multiplier if configured
    $multiplier = $this->config['role_multipliers'][$role] ?? 1;
    $limit = $baseLimit * $multiplier;
    
    // Continue with limit checking...
}
```

#### Configuration Updates:
```php
'rate_limits' => [
    // Existing limits...
],
'exempt_paths' => [
    '/admin/dashboard',
    '/admin/bulk-operations/*'
],
'exempt_roles' => ['admin', 'system'],
'role_multipliers' => [
    'admin' => 5,      // 5x standard limits
    'moderator' => 3,  // 3x standard limits
    'premium' => 2     // 2x standard limits
]
```

## 2. Progressive Backoff

### Why Implement
Progressive backoff improves user experience while maintaining protection by:
- Allowing initial legitimate requests to succeed
- Gradually slowing down potential abusive behavior
- Providing natural feedback to users before hard blocking
- Reducing false positives from legitimate burst activity

### Implementation Plan

#### In RateLimitService:
```php
public function checkRateLimit(string $identifier, string $actionType, string $ipAddress): void
{
    // Get attempt counts
    $userAttempts = $this->repository->countRecentAttempts($identifier, $actionType, $since);
    
    // Calculate base limit
    $baseLimit = $this->config[$actionType]['limit'];
    
    // Progressive threshold checking
    if ($userAttempts >= $baseLimit * 2) {
        // Hard block - too many attempts
        throw new AuthenticationException('Too many attempts. Please try again later.');
    } elseif ($userAttempts >= $baseLimit) {
        // Soft limit - introduce delay
        $delaySeconds = min(30, ($userAttempts - $baseLimit + 1) * 5);
        sleep($delaySeconds);
        // Optional: inform user about throttling
        // Could add a header: X-RateLimit-Delay: $delaySeconds
    }
    
    // Continue normal processing...
}
```

#### Configuration Updates:
```php
'rate_limits' => [
    'login' => [
        'limit' => 5,           // Base limit
        'window' => 300,        // Window in seconds
        'hard_limit' => 10,     // Complete block threshold
        'backoff_factor' => 5   // Seconds to add per attempt over limit
    ],
    // Other action types...
],
```

## Implementation Priority

1. Role-based limits (Higher business value with simpler implementation)
2. Progressive backoff (More complex but improves user experience)

## Estimated Effort

- Role-Based Limits: 2-3 days (including testing)
- Progressive Backoff: 3-4 days (including testing)