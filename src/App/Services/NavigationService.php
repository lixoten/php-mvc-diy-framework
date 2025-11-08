<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Url;
use App\Features\Store\StoreRepositoryInterface;
use App\Helpers\DebugRt;
use App\Helpers\MenuBuilder;

use App\ValueObjects\NavigationData;
use Core\Auth\AuthenticationServiceInterface;
use Core\Context\CurrentContext;

class NavigationService
{
    public function __construct(
        private AuthenticationServiceInterface $authService,
        private StoreRepositoryInterface $storeRepository,
        private CurrentContext $context
    ) {
    }

    public function buildNavigation(string $currentPath): NavigationData
    {
        // Determine sections
        $sections = $this->determineSections($currentPath);

        // Build menu items as data arrays (not HTML)
        $publicItems = $this->buildPublicItems();
        $accountItems = $this->buildAccountItems();
        $coreItems = $this->buildCoreItems();
        $storeItems = $this->buildStoreItems();
        $adminItems = $this->buildAdminItems();
        $guestItems = $this->buildGuestItems();

        // Build sub-navigation
        [$subNavItems, $subNavClass, $showSubNav] = $this->buildSubNavigation($sections);

        // Get store info if applicable
        $storeInfo = $this->getStoreInfo();

        // Debug information
        $debugInfo = $this->buildDebugInfo();

        return new NavigationData(
            publicItems: $publicItems,
            accountItems: $accountItems,
            coreItems: $coreItems,
            storeItems: $storeItems,
            adminItems: $adminItems,
            guestItems: $guestItems,
            subNavItems: $subNavItems,
            subNavClass: $subNavClass,
            showSubNav: $showSubNav,
            storeInfo: $storeInfo,
            debugInfo: $debugInfo
        );
    }

    private function determineSections(string $currentPath): array
    {
        return [
            'inAdminSection' => strpos($currentPath, '/admin/') === 0,
            'inStoreSection' => strpos($currentPath, '/store/') === 0,
            'inAccountSection' => strpos($currentPath, '/account/') === 0
        ];
    }

    private function buildPublicItems(): array
    {
        $rrr = $this->context->getRouteType();
        return [
            Url::CORE_HOME->toLinkData(),
            Url::CORE_HOME_TEST->toLinkData(),
            Url::CORE_ABOUT->toLinkData(),
            Url::CORE_CONTACT->toLinkData(),
            // Url::CORE_TESTY->toLinkData(),
            Url::CORE_TESTY->toLinkData(routeType: $rrr),
        ];
        ## If you have set 'section' => 'PUBLIC' in your Url enum routeData:
        // return array_map(
            // fn($url) => $url->toLinkData(),
            // array_filter(Url::cases(), fn($url) => $url->section() === 'PUBLIC')
        // );
    }

    private function buildCoreItems(): array
    {
        if (!$this->authService->isAuthenticated()) {
            return [];
        }


        return [
            [
                'label' => 'Core',
                'items' => [
                    Url::CORE_POST->toLinkData(),
                    Url::CORE_TESTY->toLinkData(),
                ],
            ]
        ];
    }

    private function buildAccountItems(): array
    {
        if (!$this->authService->isAuthenticated()) {
            return [];
        }
        // fixme shit

        $rrr = $this->context->getRouteType(); // routeType: $rrr

        return [
            [
                'label' => 'User',
                'items' => [
                    Url::ACCOUNT_DASHBOARD->toLinkData(),
                    Url::ACCOUNT_PROFILE->toLinkData(),
                    Url::ACCOUNT_MYNOTES->toLinkData(),
                    // Url::ACCOUNT_POST->toLinkData(),
                    Url::CORE_POST->toLinkData(routeType: 'account'),
                    Url::CORE_ALBUMS->toLinkData(),
                    // Url::ACCOUNT_TESTY->toLinkData(),
                    Url::CORE_TESTY->toLinkData(routeType: 'account'),
                ],



            ]
        ];
        // return [
            // Url::ACCOUNT_DASHBOARD->toLinkData(),
            // Url::ACCOUNT_PROFILE->toLinkData(),
            // Url::ACCOUNT_MYNOTES->toLinkData(),
            // Url::ACCOUNT_POST->toLinkData(),
            // Url::ACCOUNT_ALBUMS->toLinkData(),
        // ];
        // return array_map(
            // fn($url) => $url->toLinkData(),
            // array_filter(Url::cases(), fn($url) => $url->section() === 'ACCOUNT')
        // );
    }

    private function buildStoreItems(): array
    {
        if (!$this->authService->hasRole('store_owner')) {
            return [];
        }

        $rrr = $this->context->getRouteType(); // routeType: $rrr

        return [
            [
                'label' => 'Store',
                'items' => [
                    Url::STORE_DASHBOARD->toLinkData(),
                    Url::STORE_PROFILE->toLinkData(),
                    Url::STORE_SETTINGS->toLinkData(),
                    // Url::STORE_POST->toLinkData(),
                    Url::CORE_POST->toLinkData(routeType: $rrr),
                    Url::CORE_ALBUMS->toLinkData(),
                    // Url::STORE_TESTY->toLinkData(),
                    Url::CORE_TESTY->toLinkData(routeType: $rrr),
                ]
            ]
        ];
        // return array_map(
            // fn($url) => $url->toLinkData(),
            // array_filter(Url::cases(), fn($url) => $url->section() === 'STORE')
        // );
    }

    private function buildAdminItems(): array
    {
        if (!$this->authService->hasRole('admin')) {
            return [];
        }

        return [
            [
                'label' => 'Admin',
                'items' => [
                    Url::ADMIN_DASHBOARD->toLinkData(),
                    Url::ADMIN_USERS->toLinkData(),
                    // Url::ADMIN_POSTS->toLinkData(),
                    // Url::ADMIN_ALBUMS->toLinkData(),
                ],
            ]
        ];
        // return array_map(
            // fn($url) => $url->toLinkData(),
            // array_filter(Url::cases(), fn($url) => $url->section() === 'ADMIN')
        // );
    }

    private function buildGuestItems(): array
    {
        if ($this->authService->isAuthenticated()) {
            return [];
        }

        return [
            Url::LOGIN->toLinkData(),
            Url::REGISTRATION->toLinkData(),
        ];
    }

    private function buildSubNavigation(array $sections): array
    {
        if (!$this->authService->isAuthenticated()) {
            return [[], '', false];
        }

        $rrr = $this->context->getRouteType(); // routeType: $rrr


        if ($sections['inStoreSection'] && $this->authService->hasRole('store_owner')) {
            return [
                [
                    Url::STORE_DASHBOARD->toLinkData(),
                    Url::STORE_PROFILE->toLinkData(),
                    Url::STORE_SETTINGS->toLinkData(),
                    // Url::STORE_POST->toLinkData(),
                    Url::CORE_POST->toLinkData(routeType: $rrr),
                    Url::CORE_ALBUMS->toLinkData(),
                    // Url::STORE_TESTY->toLinkData(),
                    Url::CORE_TESTY->toLinkData(routeType: $rrr),
                ],
                'store-nav',
                false // FIK - this turn on and off the submenu......at the oment it is hard coded
            ];
        }

        if ($sections['inAdminSection'] && $this->authService->hasRole('admin')) {
            return [
                [
                    Url::ADMIN_DASHBOARD->toLinkData(),
                    Url::ADMIN_USERS->toLinkData(),
                    // Url::ADMIN_POSTS->toLinkData(),
                    // Url::ADMIN_ALBUMS->toLinkData(),
                ],
                'admin-nav',
                false // FIK - this turn on and off the submenu......at the oment it is hard coded
            ];
        }

        // Default account navigation
        return [
            [
                Url::ACCOUNT_DASHBOARD->toLinkData(),
                Url::ACCOUNT_PROFILE->toLinkData(),
                Url::ACCOUNT_MYNOTES->toLinkData(),
                // Url::ACCOUNT_POST->toLinkData(),
                Url::CORE_POST->toLinkData(routeType: $rrr),
                Url::CORE_ALBUMS->toLinkData(),
                // Url::ACCOUNT_TESTY->toLinkData(),
                Url::CORE_TESTY->toLinkData(routeType: $rrr),
            ],
            'account-nav',
            false // FIK - this turn on and off the submenu......at the oment it is hard coded
        ];
    }

    private function getStoreInfo(): ?array
    {
        if (!$this->authService->isAuthenticated() || !$this->authService->hasRole('store_owner')) {
            return null;
        }

        $currentUser = $this->authService->getCurrentUser();
        $store = $this->storeRepository->findByUserId($currentUser->getId());

        if (!$store) {
            return null;
        }

        //DebugRt::j('0', '', '$store->getId(), // dangerDanger');
        return [
            'id' => $store[0]->getId(), // dangerDanger // fixme
            'name' => $store[0]->getName(),
            'slug' => $store[0]->getSlug(),
            'url' => '/' . $store[0]->getSlug(),
        ];
    }

    private function buildDebugInfo(): array
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
            $debugInfo['user_id'] = $currentUser->getId();
            $debugInfo['username'] = $currentUser->getUsername();

            // Store info for store owners
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
