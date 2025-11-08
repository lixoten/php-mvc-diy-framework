<?php

declare(strict_types=1);

namespace App\Services;

use App\Features\Store\Store;
use App\Features\Store\StoreRepositoryInterface;
use Core\Auth\AuthenticationServiceInterface;
use Core\Session\SessionManagerInterface;

/**
 * Service for managing and accessing store context
 */
class StoreContext
{
    /**
     * Session keys for store context
     */
    private const SESSION_STORE_ID = 'active_store_id';
    private const SESSION_STORE_SLUG = 'active_store_slug';
    private const SESSION_STORE_NAME = 'active_store_name';

    /**
     * @var Store|null The current store
     */
    private ?Store $currentStore = null;

    /**
     * Constructor
     */
    public function __construct(
        private SessionManagerInterface $session,
        private StoreRepositoryInterface $storeRepository,
        private AuthenticationServiceInterface $authService
    ) {
    }

    /**
     * Get the current store ID
     */
    public function getCurrentStoreId(): ?int
    {
        $store = $this->getCurrentStore();
        return $store ? $store->getId() : null;
    }

    /**
     * Get the current store entity
     */
    public function getCurrentStore(): ?Store
    {
        // Return cached instance if available
        if ($this->currentStore !== null) {
            return $this->currentStore;
        }

        // First check session for active store ID
        $storeId = $this->session->get(self::SESSION_STORE_ID);

        if ($storeId) {
            $this->currentStore = $this->storeRepository->findById((int)$storeId);

            // Verify store ownership
            if ($this->currentStore && $this->validateStoreOwnership($this->currentStore)) {
                return $this->currentStore;
            }
        }

        // No valid store in session, try to find the user's primary store
        if ($this->authService->isAuthenticated()) {
            $userId = $this->authService->getCurrentUser()->getUserId();
            $this->currentStore = $this->storeRepository->findByUserId($userId);

            if ($this->currentStore) {
                // Set as active in session for future requests
                $this->setStoreInSession($this->currentStore);
                return $this->currentStore;
            }
        }

        return null;
    }

    /**
     * Check if current user has an active store
     */
    public function hasStore(): bool
    {
        return $this->getCurrentStore() !== null;
    }

    /**
     * Switch the active store
     */
    public function switchStore(int $storeId): bool
    {
        $store = $this->storeRepository->findById($storeId);

        if (!$store || !$this->validateStoreOwnership($store)) {
            return false;
        }

        // Update session and current store
        $this->setStoreInSession($store);
        $this->currentStore = $store;

        return true;
    }

    /**
     * Get current store name
     */
    public function getCurrentStoreName(): ?string
    {
        $store = $this->getCurrentStore();
        return $store ? $store->getName() : null;
    }

    /**
     * Get current store slug
     */
    public function getCurrentStoreSlug(): ?string
    {
        $store = $this->getCurrentStore();
        return $store ? $store->getSlug() : null;
    }

    /**
     * Set store data in session
     */
    private function setStoreInSession(Store $store): void
    {
        $this->session->set(self::SESSION_STORE_ID, $store->getId());
        $this->session->set(self::SESSION_STORE_SLUG, $store->getSlug());
        $this->session->set(self::SESSION_STORE_NAME, $store->getName());
    }

    /**
     * Validate that the current user owns the given store
     */
    private function validateStoreOwnership(Store $store): bool
    {
        if (!$this->authService->isAuthenticated()) {
            return false;
        }

        $user = $this->authService->getCurrentUser();

        // Check if user is admin (admins can access any store)
        if ($this->authService->hasRole('admin')) {
            return true;
        }

        //$storeUserId = $store->getId();
        //$userUserId = $user->getId();

        // Check if user is the store owner
        return $store->getId() === $user->getId();
    }
}
