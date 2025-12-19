<?php

declare(strict_types=1);

namespace App\Features\Image;

use App\Enums\ImageStatus;

/**
 * Generated File - Date: 20251218_145442
 * Entity class for Image.
 *
 * @property-read array<string, mixed> $fields
 */
class Image
{
    /**
     * @var int
     */
    private int $id = 0;

    /**
     * @var int
     */
    private ?int $store_id = null;

    /**
     * @var int
     */
    private int $user_id = 0;

    /**
     * @var ImageStatus
     */
    private ImageStatus $status = ImageStatus::PENDING;

    /**
     * @var string
     */
    private string $title = '';

    /**
     * @var string
     */
    private string $slug = '';

    /**
     * @var string
     */
    private string $description = '';

    /**
     * @var string
     */
    private ?string $filename = null;

    /**
     * @var string
     */
    private ?string $original_filename = null;

    /**
     * @var string
     */
    private ?string $mime_type = null;

    /**
     * @var int
     */
    private ?int $file_size_bytes = null;

    /**
     * @var int
     */
    private ?int $width = null;

    /**
     * @var int
     */
    private ?int $height = null;

    /**
     * @var string
     */
    private ?string $focal_point = null;

    /**
     * @var bool
     */
    private bool $is_optimized = false;

    /**
     * @var string
     */
    private ?string $checksum = null;

    /**
     * @var string
     */
    private ?string $alt_text = null;

    /**
     * @var string
     */
    private ?string $license = null;

    /**
     * @var string
     */
    private string $created_at = '';

    /**
     * @var string
     */
    private string $updated_at = '';

    /**
     * @var string
     */
    private ?string $deleted_at = null;
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return ?int
     */
    public function getStoreId(): ?int
    {
        return $this->store_id;
    }

    /**
     * @param ?int $store_id
     * @return self
     */
    public function setStoreId(?int $store_id): self
    {
        $this->store_id = $store_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     * @return self
     */
    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return ImageStatus
     */
    public function getStatus(): ImageStatus
    {
        return $this->status;
    }

    /**
     * @param ImageStatus $status
     * @return self
     */
    public function setStatus(ImageStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return self
     */
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @param ?string $filename
     * @return self
     */
    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getOriginalFilename(): ?string
    {
        return $this->original_filename;
    }

    /**
     * @param ?string $original_filename
     * @return self
     */
    public function setOriginalFilename(?string $original_filename): self
    {
        $this->original_filename = $original_filename;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getMimeType(): ?string
    {
        return $this->mime_type;
    }

    /**
     * @param ?string $mime_type
     * @return self
     */
    public function setMimeType(?string $mime_type): self
    {
        $this->mime_type = $mime_type;
        return $this;
    }

    /**
     * @return ?int
     */
    public function getFileSizeBytes(): ?int
    {
        return $this->file_size_bytes;
    }

    /**
     * @param ?int $file_size_bytes
     * @return self
     */
    public function setFileSizeBytes(?int $file_size_bytes): self
    {
        $this->file_size_bytes = $file_size_bytes;
        return $this;
    }

    /**
     * @return ?int
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @param ?int $width
     * @return self
     */
    public function setWidth(?int $width): self
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return ?int
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @param ?int $height
     * @return self
     */
    public function setHeight(?int $height): self
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getFocalPoint(): ?string
    {
        return $this->focal_point;
    }

    /**
     * @param ?string $focal_point
     * @return self
     */
    public function setFocalPoint(?string $focal_point): self
    {
        $this->focal_point = $focal_point;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsOptimized(): bool
    {
        return $this->is_optimized;
    }

    /**
     * @param bool $is_optimized
     * @return self
     */
    public function setIsOptimized(bool $is_optimized): self
    {
        $this->is_optimized = $is_optimized;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getChecksum(): ?string
    {
        return $this->checksum;
    }

    /**
     * @param ?string $checksum
     * @return self
     */
    public function setChecksum(?string $checksum): self
    {
        $this->checksum = $checksum;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getAltText(): ?string
    {
        return $this->alt_text;
    }

    /**
     * @param ?string $alt_text
     * @return self
     */
    public function setAltText(?string $alt_text): self
    {
        $this->alt_text = $alt_text;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getLicense(): ?string
    {
        return $this->license;
    }

    /**
     * @param ?string $license
     * @return self
     */
    public function setLicense(?string $license): self
    {
        $this->license = $license;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    /**
     * @param string $created_at
     * @return self
     */
    public function setCreatedAt(string $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }

    /**
     * @param string $updated_at
     * @return self
     */
    public function setUpdatedAt(string $updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getDeletedAt(): ?string
    {
        return $this->deleted_at;
    }

    /**
     * @param ?string $deleted_at
     * @return self
     */
    public function setDeletedAt(?string $deleted_at): self
    {
        $this->deleted_at = $deleted_at;
        return $this;
    }

    /**
     * Convert the Image entity to an array for persistence.
     * This method is used by the ImageService when calling array-based
     * repository methods like insertFields() and updateFields().
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'status' => $this->status?->value, // ✅ Get the scalar value from the Enum
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'filename' => $this->filename,
            'original_filename' => $this->original_filename,
            'mime_type' => $this->mime_type,
            'file_size_bytes' => $this->file_size_bytes,
            'width' => $this->width,
            'height' => $this->height,
            'focal_point' => $this->focal_point, // Should already be JSON string or null
            'is_optimized' => $this->is_optimized, // Should be bool, will be cast by DB
            'checksum' => $this->checksum,
            'alt_text' => $this->alt_text,
            'license' => $this->license,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
