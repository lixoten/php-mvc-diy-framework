<?php

declare(strict_types=1);

namespace App\Attributes\Field;

use App\Attributes\Field;

/**
 * Entity representing a blog post.
 *
 * Uses PHP attributes for field metadata.
 */
class Post
{
    #[Field(
        type: 'int',
        nullable: true,
        label: 'Post ID',
        primary: true,
        name: 'id',
        autoIncrement: true,
        unsigned: true
    )]
    private ?int $postId = null;

    #[Field(
        type: 'int',
        nullable: true,
        label: 'Store ID',
        name: 'store_id',
        index: true,
        unsigned: true,
        foreign: ['table' => 'stores', 'column' => 'store_id', 'onDelete' => 'CASCADE']
    )]
    private ?int $postStoreId = null;

    #[Field(
        type: 'int',
        nullable: false,
        label: 'User ID',
        name: 'user_id',
        index: true,
        unsigned: true,
        foreign: ['table' => 'users', 'column' => 'user_id', 'onDelete' => 'CASCADE']
    )]
    private int $postUserId;

    #[Field(
        type: 'string',
        nullable: false,
        label: 'Status',
        name: 'status',
        length: 1,
        enum: ['P', 'D', 'A'],
        index: true,
        default: 'P'
    )]
    private string $postStatus;

    #[Field(
        type: 'string',
        nullable: false,
        label: 'Slug',
        name: 'slug',
        length: 100,
        unique: true
    )]
    private string $slug;

    #[Field(
        type: 'string',
        nullable: false,
        label: 'Title',
        name: 'title',
        length: 255
    )]
    private string $title;

    #[Field(
        type: 'string',
        nullable: false,
        label: 'Content',
        name: 'content'
    )]
    private string $content;

    #[Field(
        type: 'string',
        nullable: true,
        label: 'Created At',
        name: 'created_at'
    )]
    private ?string $createdAt = null;

    #[Field(
        type: 'string',
        nullable: true,
        label: 'Updated At',
        name: 'updated_at'
    )]
    private ?string $updatedAt = null;

    #[Field(
        type: 'string',
        nullable: true,
        label: 'Username',
        name: 'username'
    )]
    private ?string $username = null;




    // Getters and setters

    public function getPostId(): ?int
    {
        return $this->postId;
    }

    public function setPostId(?int $postId): self
    {
        $this->postId = $postId;
        return $this;
    }

    public function getPostStoreId(): ?int
    {
        return $this->postStoreId;
    }

    public function setPostStoreId(?int $postStoreId): self
    {
        $this->postStoreId = $postStoreId;
        return $this;
    }

    public function getPostUserId(): int
    {
        return $this->postUserId;
    }

    public function setPostUserId(int $postUserId): self
    {
        $this->postUserId = $postUserId;
        return $this;
    }

    public function getPostStatus(): string
    {
        return $this->postStatus;
    }

    public function setPostStatus(string $postStatus): self
    {
        $this->postStatus = $postStatus;
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
}
