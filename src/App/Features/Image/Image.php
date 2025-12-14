<?php

declare(strict_types=1);

namespace App\Features\Image;

use App\Enums\ImageStatus;

/**
 * Generated File - Date: 20251114_193709 ffff
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
    private string $slug = '';

    /**
     * @var string
     */
    private string $title = '';


    /**
     * @var string
     */
    private string $created_at = '';

    /**
     * @var string
     */
    private string $updated_at = '';
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

}
