<?php

declare(strict_types=1);

namespace App\Entities;

use App\Attributes\Field;

class Testy
{
    #[Field(
        type: 'int',
        nullable: true,
        label: 'Testy ID',
        primary: true,
        name: 'testy_id',
        enum: null,
    )]
    private ?int $testyId = null;

    #[Field(
        type: 'int',
        nullable: true,
        label: 'Store ID',
        name: 'testy_store_id',
        enum: null
    )]
    private ?int $testyStoreId = null;

    #[Field(
        type: 'int',
        nullable: false,
        label: 'User ID',
        name: 'testy_user_id',
        enum: null
    )]
    private int $testyUserId;

    #[Field(
        type: 'string',
        nullable: false,
        label: 'Status',
        name: 'testy_status',
        enum: ['P', 'D', 'A']
    )]
    private string $testyStatus;

    #[Field(
        type: 'string',
        nullable: false,
        label: 'Slug',
        name: 'slug',
        enum: null
    )]
    private string $slug;

    #[Field(
        type: 'string',
        nullable: false,
        label: 'Title',
        name: 'title',
        enum: null
    )]
    private string $title;

    #[Field(
        type: 'string',
        nullable: false,
        label: 'Content',
        name: 'content',
        enum: null
    )]
    private string $content;

    #[Field(
        type: 'string',
        nullable: false,
        label: 'Favorite Word',
        name: 'favorite_word',
        enum: null
    )]
    private string $favorite_word;

    #[Field(
        type: 'string',
        nullable: true,
        label: 'Created At',
        name: 'created_at',
        enum: null
    )]
    private ?string $createdAt = null;

    #[Field(
        type: 'string',
        nullable: true,
        label: 'Updated At',
        name: 'updated_at',
        enum: null
    )]
    private ?string $updatedAt = null;

    #[Field(
        type: 'string',
        nullable: true,
        label: 'Username',
        name: 'username',
        enum: null
    )]
    private ?string $username = null;

    // Getters and setters~
    public function getTestyId(): ?int
    {
        return $this->testyId;
    }

    public function getRecordId(): ?int
    {
        return $this->testyId;
    }

    public function setTestyId(?int $testyId): self
    {
        $this->testyId = $testyId;
        return $this;
    }

    public function getTestyStoreId(): int
    {
        return $this->testyStoreId;
    }

    public function setTestyStoreId(?int $testyStoreId): self
    {
        $this->testyStoreId = $testyStoreId;
        return $this;
    }

    public function getTestyUserId(): int
    {
        return $this->testyUserId;
    }

    public function setTestyUserId(int $testyUserId): self
    {
        $this->testyUserId = $testyUserId;
        return $this;
    }

    public function getTestyStatus(): string
    {
        return $this->testyStatus;
    }

    public function setTestyStatus(string $testyStatus): self
    {
        $this->testyStatus = $testyStatus;
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }
    public function getFavoriteWord(): string
    {
        return $this->favorite_word;
    }

    public function setFavoriteWord(string $favorite_word): self
    {
        $this->favorite_word = $favorite_word;
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

    public function isPublished(): bool
    {
        return $this->testyStatus === 'P';
    }
}
