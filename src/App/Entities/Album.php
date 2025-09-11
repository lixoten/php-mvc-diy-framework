<?php

declare(strict_types=1);

namespace App\Entities;

class Album
{
    private ?int $albumId = null;
    private ?int $albumStoreId = null;
    private int $albumUserId;
    private string $albumStatus;
    private string $slug;
    private string $name;
    private ?string $description = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;
    private ?string $username = null; // For joining with users table

    // Getters and setters
    public function getAlbumId(): ?int
    {
        return $this->albumId;
    }

    public function getRecordId(): ?int
    {
        return $this->albumId;
    }

    public function setAlbumId(?int $albumId): self
    {
        $this->albumId = $albumId;
        return $this;
    }

    public function getAlbumStoreId(): ?int
    {
        return $this->albumStoreId;
    }

    public function setAlbumStoreId(?int $albumStoreId): self
    {
        $this->albumStoreId = $albumStoreId;
        return $this;
    }

    public function getAlbumUserId(): int
    {
        return $this->albumUserId;
    }

    public function setAlbumUserId(int $albumUserId): self
    {
        $this->albumUserId = $albumUserId;
        return $this;
    }

    public function getAlbumStatus(): string
    {
        return $this->albumStatus;
    }

    public function setAlbumStatus(string $albumStatus): self
    {
        $this->albumStatus = $albumStatus;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?string $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->albumStatus === 'A';
    }

    public function isPending(): bool
    {
        return $this->albumStatus === 'P';
    }

    public function isInactive(): bool
    {
        return $this->albumStatus === 'I';
    }
}
