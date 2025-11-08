<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entities\Image;

interface ImageRepositoryInterface
{
    /**
     * Find an image by ID with full entity mapping.
     *
     * @param int $imageId
     * @return Image|null
     */
    public function findById(int $imageId): ?Image;

    /**
     * Find an image by ID, selecting only specified columns (raw data).
     *
     * @param int $imageId
     * @param array<string> $fields
     * @return array<string, mixed>|null
     */
    public function findByIdWithFields(int $imageId, array $fields): ?array;

    /**
     * Find images based on criteria with full entity mapping.
     *
     * @param array<string, mixed> $criteria
     * @param array<string, string> $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array<Image>
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Find images by gallery ID.
     *
     * @param int $galleryId
     * @param array<string, string> $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array<Image>
     */
    public function findByGalleryId(
        int $galleryId,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Find images by gallery ID with specified fields (raw data).
     *
     * @param int $galleryId
     * @param array<string> $fields
     * @param array<string, string> $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array<array<string, mixed>>
     */
    public function findByGalleryIdWithFields(
        int $galleryId,
        array $fields,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Count images by gallery ID.
     *
     * @param int $galleryId
     * @return int
     */
    public function countByGalleryId(int $galleryId): int;

    /**
     * Get featured image for a gallery.
     *
     * @param int $galleryId
     * @return Image|null
     */
    public function getFeaturedImageByGalleryId(int $galleryId): ?Image;

    /**
     * Set featured image for a gallery (unsets all others in the same gallery).
     *
     * @param int $imageId
     * @param int $galleryId
     * @return bool
     */
    public function setFeaturedImage(int $imageId, int $galleryId): bool;

    /**
     * Update display order for an image.
     *
     * @param int $id
     * @param int $displayOrder
     * @return bool
     */
    public function updateDisplayOrder(int $id, int $displayOrder): bool;

    /**
     * Create a new image.
     *
     * @param Image $image
     * @return Image The created image with ID
     */
    public function create(object $image): object;

    /**
     * Update an existing image.
     *
     * @param Image $image
     * @return bool True if update was successful
     */
    public function update(object $image): bool;

    /**
     * Update selected fields for an entity by its primary ID.
     *
     * @param int $id The entity ID.
     * @param array<string, mixed> $fieldsToUpdate Associative array of fields to update.
     * @return bool True on success, false on failure.
     */
    public function updateFields(int $id, array $fieldsToUpdate): bool;

    /**
     * Delete an image (hard delete).
     *
     * @param int $imageId
     * @return bool True if deletion was successful
     */
    public function delete(int $imageId): bool;

    /**
     * Count total images matching criteria.
     *
     * @param array<string, mixed> $criteria Optional filtering criteria
     * @return int Total number of images matching criteria
     */
    public function countBy(array $criteria = []): int;

    /**
     * Convert an Image entity to an array with selected fields.
     *
     * @param Image $image
     * @param array<string> $fields
     * @return array<string, mixed>
     */
    public function toArray(Image $image, array $fields = []): array;
}