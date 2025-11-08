<?php

declare(strict_types=1);

namespace App\Entities;

use App\Attributes\Field;

class Image
{
    #[Field(
        type: 'int',
        nullable: true,
        label: 'Image ID',
        primary: true,
        name: 'id',
        enum: null,
    )]
    private ?int $imageId = null;

    #[Field(
        type: 'int',
        nullable: false,
        label: 'Store ID',
        name: 'store_id',
        enum: null
    )]
    private int $imageStoreId;

    #[Field(
        type: 'int',
        nullable: false,
        label: 'User ID',
        name: 'user_id',
        enum: null
    )]
    private int $imageUserId;

    #[Field(
        type: 'int',
        nullable: true,
        label: 'Gallery ID',
        name: 'gallery_id',
        enum: null
    )]
    private ?int $galleryId = null;

    #[Field(
        type: 'string',
        nullable: false,
        label: 'Status',
        name: 'status',
        enum: ['A', 'P', 'I']
    )]
    private string $imageStatus;

    #[Field(
        type: 'string',
        nullable: true,
        label: 'Title',
        name: 'title',
        enum: null
    )]
    private ?string $title = null;

    #[Field(
        type: 'string',
        nullable: true,
        label: 'Description',
        name: 'description',
        enum: null
    )]
    private ?string $description = null;

    #[Field(
        type: 'string',
        nullable: false,
        label: 'Filename',
        name: 'filename',
        enum: null
    )]
    private string $filename;

    #[Field(
        type: 'string',
        nullable: false,
        label: 'File Path',
        name: 'filepath',
        enum: null
    )]
    private string $filepath;

    #[Field(
        type: 'int',
        nullable: false,
        label: 'File Size (bytes)',
        name: 'filesize',
        enum: null
    )]
    private int $filesize;

    #[Field(
        type: 'string',
        nullable: false,
        label: 'MIME Type',
        name: 'mime_type',
        enum: null
    )]
    private string $mimeType;

    #[Field(
        type: 'int',
        nullable: true,
        label: 'Width (px)',
        name: 'width',
        enum: null
    )]
    private ?int $width = null;

    #[Field(
        type: 'int',
        nullable: true,
        label: 'Height (px)',
        name: 'height',
        enum: null
    )]
    private ?int $height = null;

    #[Field(
        type: 'string',
        nullable: true,
        label: 'Alt Text',
        name: 'alt_text',
        enum: null
    )]
    private ?string $altText = null;

    #[Field(
        type: 'string',
        nullable: true,
        label: 'Caption',
        name: 'caption',
        enum: null
    )]
    private ?string $caption = null;

    #[Field(
        type: 'int',
        nullable: false,
        label: 'Display Order',
        name: 'display_order',
        enum: null
    )]
    private int $displayOrder = 0;

    #[Field(
        type: 'bool',
        nullable: false,
        label: 'Is Featured',
        name: 'is_featured',
        enum: null
    )]
    private bool $isFeatured = false;

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

    // Getters and Setters

    public function getImageId(): ?int
    {
        return $this->imageId;
    }

    public function getRecordId(): ?int
    {
        return $this->imageId;
    }

    public function setImageId(?int $imageId): self
    {
        $this->imageId = $imageId;
        return $this;
    }

    public function getImageStoreId(): int
    {
        return $this->imageStoreId;
    }

    public function setImageStoreId(int $imageStoreId): self
    {
        $this->imageStoreId = $imageStoreId;
        return $this;
    }

    public function getImageUserId(): int
    {
        return $this->imageUserId;
    }

    public function setImageUserId(int $imageUserId): self
    {
        $this->imageUserId = $imageUserId;
        return $this;
    }

    public function getGalleryId(): ?int
    {
        return $this->galleryId;
    }

    public function setGalleryId(?int $galleryId): self
    {
        $this->galleryId = $galleryId;
        return $this;
    }

    public function getImageStatus(): string
    {
        return $this->imageStatus;
    }

    public function setImageStatus(string $imageStatus): self
    {
        $this->imageStatus = $imageStatus;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
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

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    public function getFilepath(): string
    {
        return $this->filepath;
    }

    public function setFilepath(string $filepath): self
    {
        $this->filepath = $filepath;
        return $this;
    }

    public function getFilesize(): int
    {
        return $this->filesize;
    }

    public function setFilesize(int $filesize): self
    {
        $this->filesize = $filesize;
        return $this;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): self
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;
        return $this;
    }

    public function getAltText(): ?string
    {
        return $this->altText;
    }

    public function setAltText(?string $altText): self
    {
        $this->altText = $altText;
        return $this;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(?string $caption): self
    {
        $this->caption = $caption;
        return $this;
    }

    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): self
    {
        $this->displayOrder = $displayOrder;
        return $this;
    }

    public function getIsFeatured(): bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(int|bool $isFeatured): self
    {
        $this->isFeatured = (bool)$isFeatured;
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
        return $this->imageStatus === 'A';
    }

    public function isPending(): bool
    {
        return $this->imageStatus === 'P';
    }

    public function isInactive(): bool
    {
        return $this->imageStatus === 'I';
    }

    /**
     * Get the full public URL for the image.
     *
     * @return string
     */
    public function getPublicUrl(): string
    {
        return '/' . ltrim($this->filepath, '/');
    }

    /**
     * Get the aspect ratio of the image.
     *
     * @return float|null
     */
    public function getAspectRatio(): ?float
    {
        if ($this->width === null || $this->height === null || $this->height === 0) {
            return null;
        }

        return $this->width / $this->height;
    }

    /**
     * Check if the image is landscape orientation.
     *
     * @return bool
     */
    public function isLandscape(): bool
    {
        if ($this->width === null || $this->height === null) {
            return false;
        }

        return $this->width > $this->height;
    }

    /**
     * Check if the image is portrait orientation.
     *
     * @return bool
     */
    public function isPortrait(): bool
    {
        if ($this->width === null || $this->height === null) {
            return false;
        }

        return $this->height > $this->width;
    }

    /**
     * Check if the image is square.
     *
     * @return bool
     */
    public function isSquare(): bool
    {
        if ($this->width === null || $this->height === null) {
            return false;
        }

        return $this->width === $this->height;
    }

    /**
     * Get human-readable file size.
     *
     * @return string
     */
    public function getHumanReadableFilesize(): string
    {
        $bytes = $this->filesize;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
