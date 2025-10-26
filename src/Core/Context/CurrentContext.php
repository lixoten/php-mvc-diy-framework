<?php

declare(strict_types=1);

namespace Core\Context;

use App\Entities\Store;
use App\Entities\User;
use App\Enums\Url;

/**
 * Holds contextual information for the current request/operation.
 * AKA Scrap / ScrapObj
 *
 * This class acts as a central repository for data relevant to the
 * current request lifecycle, such as the logged-in user, route parameters,
 * and potentially other application-specific context. It is typically
 * populated by middleware early in the request process.
 */
class CurrentContext
{
    private ?User $currentUser = null;
    private ?Store $storeObj = null;
    private array $currentUserRoles = [];

    private ?int $storeId = null;
    private ?string $storeName = null;

    private ?string $pageKey = null; // e.g.,  , 'users' from route
    private ?string $pageConfigKey = null;
    private ?int $entityId = null; // e.g., the ID from /edit/{id}
    private array $routeParams = [];
    private ?string $actionName = null; // e.g., 'index', 'edit'
    private ?string $controller = null;
    private ?string $namespace = null;
    private ?string $routeId = null;

    private ?string $routeType = null; // 'admin', 'account', or 'store'
    private ?string $routeTypePath = null; // 'admin', 'account', or 'store'
    private ?string $boo = null;


    // Add other relevant context properties as needed
    // private ?int $userStoreId = null;
    // private ?int $viewedStoreId = null;

    // --- User ---
    public function setCurrentUser(?User $user): void
    {
        $this->currentUser = $user;
        $this->setCurrentUserRoles($user?->getRoles()); // Assuming User entity has a getRole() method
    }

    public function getCurrentUser(): ?User
    {
        return $this->currentUser;
    }

    public function getUserId(): ?int
    {
        return $this->currentUser?->getUserId(); // Assumes getRecordId() method on User entity
    }

    public function isLoggedIn(): bool
    {
        return $this->currentUser !== null;
    }


    /**
     * Set the current user's roles.
     *
     * @param array $roles An array of user roles (e.g., ['admin', 'editor'])
     */
    public function setCurrentUserRoles(array $roles): void
    {
        $this->currentUserRoles = $roles;
    }


    /**
     * Get the current user's roles.
     *
     * @return array An array of user roles
     */
    public function getCurrentUserRoles(): array
    {
        return $this->currentUserRoles;
    }

    /**
     * Check if the current user has a specific role.
     *
     * @param string $role The role to check for
     * @return bool True if the user has the role, false otherwise
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->currentUserRoles);
    }

    /**
     * Check if the current user is an administrator.
     *
     * @return bool True if the user has the 'admin' role, false otherwise
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    // --- Store ---
    /**
     * Set the current store ID.
     *
     * @param int|null $id The store ID
     */
    public function setStoreObj(?Store $obj): void // <-- Allow null
    {
        $this->storeObj = $obj;
    }
    public function getStoreObj(): ?Store // <-- Return nullable
    {
        return $this->storeObj;
    }



    /**
     * Set the current store ID.
     *
     * @param int|null $id The store ID
     */
    public function setStoreId(?int $id): void // <-- Allow null
    {
        $this->storeId = $id;
    }

    /**
     * Get the current store ID.
     *
     * @return int|null The store ID or null if not set
     */
    public function getStoreId(): ?int // <-- Return nullable
    {
        return $this->storeId;
    }

    /**
     * Set the current store name.
     *
     * @param string|null $name The store name
     */
    public function setStoreName(?string $name): void // <-- Allow null
    {
        $this->storeName = $name;
    }

    /**
     * Get the current store name.
     *
     * @return string|null The store name or null if not set
     */
    public function getStoreName(): ?string // <-- Return nullable
    {
        return $this->storeName;
    }




    /**
     * Set the current store name.
     *
     * @param string|null $name The store name
     */
    public function setBoo(?string $boo): void // <-- Allow null
    {
        $this->boo = $boo;
    }

    /**
     * Get the current store name.
     *
     * @return string|null The store name or null if not set
     */
    public function getBoo(): ?string // <-- Return nullable
    {
        return $this->boo;
    }

        /**
     * Get the current store name.
     *
     * @return string|null The store name or null if not set
     */
    public function printIt(): array // <-- Return nullable
    {
        $arr = [];
        $arr['userObj'] = 'User object';
        $arr['storeObj'] = 'Store object';
        $arr['currentUserRoles'] = $this->currentUserRoles;
        $arr['storeId'] = $this->storeId;
        $arr['storeName'] = $this->storeName;
        $arr['pageKey'] = $this->pageKey;
        $arr['entityId'] = $this->entityId;
        $arr['actionName'] = $this->actionName;
        $arr['controller'] = $this->controller;
        $arr['namespace'] = $this->namespace;
        $arr['routeType'] = $this->routeType;
        $arr['routeTypePath'] = $this->routeTypePath;
        $arr['routeId'] = $this->routeId;
        $arr['boo'] = $this->boo;
        $arr['routeParams'] = $this->routeParams;

        $arr['total'] = count((array) $arr);
        $arr['this'] = count((array) $this);
        if ($arr['total'] === $arr['this']) {
            $arr['messages'] = 'All is good';
        } else {
            $arr['messages'] = 'Danger Danger this method is out of date. A new something';
        }
        return $arr;
    }




    // --- Routing / Entity ---
    public function setEntityId(?int $id): void
    {
        $this->entityId = $id;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }





    ## These are related to url, route
    public function setRouteParams(array $routeParams): void
    {
        $this->routeParams = $routeParams;
    }

    public function getRouteParams(): array
    {
        return $this->routeParams;
    }


    public function setActionName(?string $action): void
    {
        $this->actionName = $action;
    }

    public function getActionName(): ?string
    {
        return $this->actionName;
    }


    public function setControllerName(?string $controller): void
    {
        $this->controller = $controller;
    }

    public function getControllerName(): ?string
    {
        return $this->controller;
    }


    public function setNamespaceName(?string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function getNamespaceName(): ?string
    {
        return $this->namespace;
    }


    public function setRouteId(?string $routeId): void
    {
        $this->routeId = $routeId;
    }

    public function getRouteId(): ?string
    {
        return $this->routeId;
    }


    public function setPageKey(?string $pageKey): void
    {
        $this->pageKey = $pageKey;
    }

    public function getPageKey(): ?string
    {
        return $this->pageKey;
    }


    public function setPageConfigKey(?string $pageConfigKey): void
    {
        $this->pageConfigKey = $pageConfigKey;
    }

    public function getPageConfigKey(): ?string
    {
        return $this->pageConfigKey;
    }


    // Add getters/setters for other properties if added








    /**
     * Set the current route type.
     *
     * @param string|null $type The route type ('admin', 'account', 'store')
     */
    public function setRouteType(?string $type): void
    {
        $this->routeType = $type;
        if ($this->isAdminRoute()) {
            $this->routeTypePath = Url::BASE_ADMIN->url();
        } elseif ($this->isAccountRoute()) {
            $this->routeTypePath = Url::BASE_ACCOUNT->url();
        } elseif ($this->isStoreRoute()) {
            $this->routeTypePath = Url::BASE_STORE->url();
        }
    }

    /**
     * Get the current route type.
     *
     * @return string|null The route type or null if not set
     */
    public function getRouteType(): ?string
    {
        return $this->routeType;
    }

    /**
     * Get the current route type path.
     * This will be /admin/ or /account/ or /account/store/r
     *
     * @return string|null The route type path or null if not set
     */
    public function getRouteTypePath(): ?string
    {
        return $this->routeTypePath;
    }



    /**
     * Check if the current route is an admin route.
     *
     * @return bool True if route type is 'admin'
     */
    public function isAdminRoute(): bool
    {
        return $this->routeType === 'admin';
    }

    /**
     * Check if the current route is an account route.
     *
     * @return bool True if route type is 'account'
     */
    public function isAccountRoute(): bool
    {
        return $this->routeType === 'account';
    }

    /**
     * Check if the current route is a store route.
     *
     * @return bool True if route type is 'store'
     */
    public function isStoreRoute(): bool
    {
        return $this->routeType === 'store';
    }
}
