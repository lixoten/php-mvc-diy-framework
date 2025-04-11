# // TODO User Impersonation Feature - Future Implementation Guide

## Overview
User impersonation allows administrators to temporarily view and interact with the application as if they were another user, without knowing that user's password.

## Implementation Plan for MVCLixo Framework

### 1. Extend AuthenticationServiceInterface

```php
interface AuthenticationServiceInterface 
{
    // Existing methods...
    
    /**
     * Begin impersonating another user
     * 
     * @param int $userId ID of the user to impersonate
     * @return bool True if impersonation started successfully
     * @throws AuthorizationException If current user doesn't have permission
     */
    public function impersonateUser(int $userId): bool;
    
    /**
     * Stop impersonation and return to original user
     * 
     * @return void
     */
    public function stopImpersonation(): void;
    
    /**
     * Check if current session is impersonating another user
     * 
     * @return bool
     */
    public function isImpersonating(): bool;
    
    /**
     * Get the ID of the original user (admin who started impersonation)
     * 
     * @return int|null UserID or null if not impersonating
     */
    public function getOriginalUserId(): ?int;
}
```

### 2. Session Storage Implementation

```php
// In SessionAuthenticationService:
private ?int $impersonatedUserId = null;
private ?User $originalUser = null;

public function impersonateUser(int $userId): bool
{
    // Security checks
    if (!$this->hasRole('admin')) {
        throw new AuthorizationException("Only administrators can impersonate users");
    }
    
    // Store original user before switching
    $this->originalUser = $this->getCurrentUser();
    $user = $this->userRepository->find($userId);
    
    if (!$user) {
        return false;
    }
    
    // Save impersonation state in session
    $this->impersonatedUserId = $userId;
    $this->session->set('impersonated_user_id', $userId);
    $this->session->set('original_user_id', $this->originalUser->getId());
    
    // Log the impersonation event for security audit
    $this->logger->info(sprintf(
        'User %s (ID: %d) started impersonating user %s (ID: %d)',
        $this->originalUser->getUsername(),
        $this->originalUser->getId(),
        $user->getUsername(),
        $user->getId()
    ));
    
    return true;
}
```

### 3. UI Components

**Admin User List:**
```php
<!-- In admin/users/index.php -->
<a href="/admin/users/impersonate/<?= $user->getId() ?>" 
   class="btn btn-sm btn-secondary"
   title="View site as this user">
    <i class="fas fa-user-secret"></i> Impersonate
</a>
```

**Impersonation Bar:**
```php
<!-- In layout.php (only shown when impersonating) -->
<?php if ($authService->isImpersonating()): ?>
<div class="impersonation-bar">
    You are viewing the site as <?= $authService->getCurrentUser()->getUsername() ?>
    <a href="/admin/users/stop-impersonation" class="btn btn-warning btn-sm">
        Return to Admin
    </a>
</div>
<?php endif; ?>
```

## Security Considerations

1. **Strict Access Control**: Limit to administrators only
2. **Comprehensive Audit Logging**: Log start/end of all impersonation sessions
3. **Visual Indicators**: Always show when in impersonation mode
4. **Prevent Privilege Escalation**: Don't allow impersonating users with higher privileges
5. **Session Isolation**: Consider separating impersonation sessions from normal sessions

## Common in Popular Frameworks

This feature exists in many frameworks:

- **Laravel**: `loginAs()` method in the Auth facade
- **Symfony**: `switch_user` firewall listener
- **Django**: Admin site has "Log in as user" functionality
- **WordPress**: Various "User Switching" plugins

## Benefits

1. **Support**: Troubleshoot user-specific issues
2. **Training**: Demonstrate features in user's actual environment
3. **Testing**: Verify permission systems are working correctly
4. **Validation**: Confirm bug reports in specific user contexts

This implementation approach maintains security while providing admins with powerful user support capabilities.