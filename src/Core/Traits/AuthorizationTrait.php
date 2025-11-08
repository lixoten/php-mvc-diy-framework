<?php

declare(strict_types=1);

namespace Core\Traits;

use Core\Auth\AuthenticationServiceInterface;
use Core\Exceptions\UnauthenticatedException;

trait AuthorizationTrait
{
    /**
     * Check if the current user is authorized to edit the given post/resource
     *
     * @param int $resourceOwnerId User ID of the resource owner
     * @param array $requiredRoles Roles that are also allowed to access the resource
     * @return bool True if authorized, false otherwise
     */
    protected function isUserAuthorized(int $resourceOwnerId, array $requiredRoles = ['admin']): bool
    {
        // Get the current user ID and check if they are the owner
        $currentUserId = $this->getCurrentUserId();

        if ($resourceOwnerId === $currentUserId) {
            // User is the owner, so they are authorized
            return true;
        }

        // Not the owner, check if they have any of the required roles
        /** @var AuthenticationServiceInterface $authService */
        $authService = $this->container->get(AuthenticationServiceInterface::class);
        $currentUser = $authService->getCurrentUser();

        if (!$currentUser) {
            return false;
        }

        // Check if user has any of the required roles
        $userRoles = $currentUser->getRoles();
        foreach ($requiredRoles as $role) {
            if (in_array($role, $userRoles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the current user ID or throw an exception if not authenticated
     *
     * @return int
     * @throws UnauthenticatedException
     */
    protected function getCurrentUserId(): int
    {
        /** @var AuthenticationServiceInterface $authService */
        $authService = $this->container->get(AuthenticationServiceInterface::class);

        if (!$authService->isAuthenticated()) {
            throw new UnauthenticatedException(
                'User must be logged in to perform this action',
                attemptedResource: $_SERVER['REQUEST_URI'] ?? 'unknown',
                authMethod: 'session',
                reasonCode: 'AUTH_REQUIRED'
            );
        }

        $currentUser = $authService->getCurrentUser();

        if (!$currentUser) {
            throw new UnauthenticatedException(
                'Unable to retrieve current user details',
                attemptedResource: $_SERVER['REQUEST_URI'] ?? 'unknown',
                authMethod: 'session',
                reasonCode: 'USER_RETRIEVAL_FAILED'
            );
        }

        return $currentUser->getId();
    }
}
