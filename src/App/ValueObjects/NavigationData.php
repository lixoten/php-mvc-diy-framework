<?php

declare(strict_types=1);

namespace App\ValueObjects;

class NavigationData
{
    public function __construct(
        private array $publicItems = [],
        private array $accountItems = [],
        private array $coreItems = [],
        private array $storeItems = [],
        private array $adminItems = [],
        private array $guestItems = [],
        private array $subNavItems = [],
        private string $subNavClass = '',
        private bool $showSubNav = false,
        private ?array $storeInfo = null,
        private array $debugInfo = []
    ) {}

    // Getters
    public function getPublicItems(): array { return $this->publicItems; }
    public function getAccountItems(): array { return $this->accountItems; }
    public function getCoreItems(): array { return $this->coreItems; }
    public function getStoreItems(): array { return $this->storeItems; }
    public function getAdminItems(): array { return $this->adminItems; }
    public function getGuestItems(): array { return $this->guestItems; }
    public function getSubNavItems(): array { return $this->subNavItems; }
    public function getSubNavClass(): string { return $this->subNavClass; }
    public function shouldShowSubNav(): bool { return $this->showSubNav; }
    public function getStoreInfo(): ?array { return $this->storeInfo; }
    public function getDebugInfo(): array { return $this->debugInfo; }
}
