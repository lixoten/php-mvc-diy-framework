<?php

declare(strict_types=1);

namespace App\Entities;

class Store
{
    private ?int $storeId = null;
    private int $storeUserId;
    private string $storeStatus = 'I';  // I=Inactive, A=Active, S=Suspended
    private string $slug;
    private string $name;
    private ?string $description = null;
    private string $theme = 'default';
    private ?string $createdAt = null;
    private ?string $updatedAt = null;
    private ?string $username = null;  // Joined from users table

    /**
     * Get store ID
     */
    public function getStoreId(): ?int
    {
        return $this->storeId;
    }


    public function getRecordId(): ?int
    {
        return $this->storeId;
    }

    /**
     * Set store ID
     */
    public function setStoreId(?int $storeId): self
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * Get store user ID (store owner)
     */
    public function getUserId(): int
    {
        return $this->storeUserId;
    }

    /**
     * Set user ID (store owner)
     */
    public function setUserId(int $storeUserId): self
    {
        $this->storeUserId = $storeUserId;
        return $this;
    }


    /**
     * Get store status
     */
    public function getStatus(): string
    {
        return $this->storeStatus;
    }

    /**
     * Set store status
     */
    public function setStoreStatus(string $storeStatus): self
    {
        $this->storeStatus = $storeStatus;
        return $this;
    }


    /**
     * Get store slug
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * Set store slug
     */
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * Get store name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set store name
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get store description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set store description
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get store theme
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * Set store theme
     */
    public function setTheme(string $theme): self
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * Get created timestamp
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * Set created timestamp
     */
    public function setCreatedAt(?string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get updated timestamp
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * Set updated timestamp
     */
    public function setUpdatedAt(?string $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get username of store owner
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Set username of store owner
     */
    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Check if store is active
     */
    public function isActive(): bool
    {
        return $this->storeStatus === 'A';
    }

    /**
     * Check if store is inactive
     */
    public function isInactive(): bool
    {
        return $this->storeStatus === 'I';
    }

    /**
     * Check if store is suspended
     */
    public function isSuspended(): bool
    {
        return $this->storeStatus === 'S';
    }

    /**
     * Activate the store
     */
    public function activate(): self
    {
        $this->storeStatus = 'A';
        return $this;
    }

    /**
     * Deactivate the store
     */
    public function deactivate(): self
    {
        $this->storeStatus = 'I';
        return $this;
    }

    /**
     * Suspend the store
     */
    public function suspend(): self
    {
        $this->storeStatus = 'S';
        return $this;
    }

    /**
     * Get store URL
     */
    public function getUrl(): string
    {
        return '/' . $this->slug;
    }

    /**
     * Generate slug from store name
     */
    public function generateSlug(): string
    {
        $slug = strtolower($this->name);

        // Replace non-alphanumeric characters with hyphens
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        // Remove leading and trailing hyphens
        $slug = trim($slug, '-');

        // Ensure slug is not empty
        if (empty($slug)) {
            $slug = 'store-' . time();
        }

        $this->slug = $slug;
        return $slug;
    }
}
