<?php

declare(strict_types=1);

namespace App\Features\Gallery;

/**
 * Generated File - Date: 20251109_204056
 * Entity class for Gallery.
 *
 * @property-read array<string, mixed> $fields
 */
class Gallery
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
    private ?int $user_id = null;

    /**
     * @var string
     */
    private string $status = '';

    /**
     * @var string
     */
    private string $name = '';

    /**
     * @var string
     */
    private string $slug = '';

    /**
     * @var string
     */
    private ?string $description = null;

    /**
     * @var int
     */
    private ?int $image_count = null;

    /**
     * @var int
     */
    private ?int $cover_image_id = null;
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
     * @return ?int
     */
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    /**
     * @param ?int $user_id
     * @return self
     */
    public function setUserId(?int $user_id): self
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return self
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
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
     * @return ?string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param ?string $description
     * @return self
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return ?int
     */
    public function getImageCount(): ?int
    {
        return $this->image_count;
    }

    /**
     * @param ?int $image_count
     * @return self
     */
    public function setImageCount(?int $image_count): self
    {
        $this->image_count = $image_count;
        return $this;
    }

    /**
     * @return ?int
     */
    public function getCoverImageId(): ?int
    {
        return $this->cover_image_id;
    }

    /**
     * @param ?int $cover_image_id
     * @return self
     */
    public function setCoverImageId(?int $cover_image_id): self
    {
        $this->cover_image_id = $cover_image_id;
        return $this;
    }
}
