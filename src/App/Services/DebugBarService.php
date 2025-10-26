<?php

declare(strict_types=1);

namespace App\Services;

use Core\Auth\AuthenticationServiceInterface;
use Core\Context\CurrentContext;
// use App\Services\AuthService;




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

            $role = 'user';
            if ($this->authService->hasRole('admin')) {
                $role = 'admin';
            } elseif ($this->authService->hasRole('store_owner')) {
                $role = 'store_owner';
            }

            $debugInfo['role'] = $role;
            $debugInfo['user_id'] = $currentUser->getUserId();
            $debugInfo['username'] = $currentUser->getUsername();

            if ($role === 'store_owner') {
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