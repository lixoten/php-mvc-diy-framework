<?php

declare(strict_types=1);

namespace App\Services;

use Core\Auth\AuthenticationServiceInterface;
use Core\Context\CurrentContext;

class DebugBarService
{
    public function __construct(
        private CurrentContext $context,
        private AuthenticationServiceInterface $authService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getDebugInfo(): array
    {
        $debugInfo = [
            'namespace' => $this->context->getNamespaceName(),
            'controller' => $this->context->getControllerName(),
            'action' => $this->context->getActionName(),
            'route_id' => $this->context->getRouteId(),
        ];

        if ($this->authService->isAuthenticated()) {
            $currentUser = $this->authService->getCurrentUser();
            // $currentUser = $this->authService->getCurrentUser();

            $role = '';
            if ($this->authService->hasRole('admin')) {
                $role .= ' admin';
            }

            if ($this->authService->hasRole('store_owner')) {
                $role .= ' store_owner';
            }

            $debugInfo['role'] = $role;
            $debugInfo['user_id'] = $currentUser->getId();
            // $debugInfo['store_id'] = $currentUser->getS;
            $debugInfo['username'] = $currentUser->getUsername();

            //$roles = $this->context->getCurrentUserRoles();
            // if ($role === 'store_owner') {
            if ($this->authService->hasRole('store_owner')) {
                $debugInfo['active_store_id'] = $_SESSION['active_store_id'] ?? null;
                $debugInfo['active_store_name'] = $_SESSION['active_store_name'] ?? 'Unknown';
            }
        } else {
            $debugInfo['role'] = 'guest';
            $debugInfo['user_id'] = null;
            $debugInfo['username'] = 'none';
        }

        return $debugInfo;
    }
}
