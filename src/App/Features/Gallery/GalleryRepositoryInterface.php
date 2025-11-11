<?php

declare(strict_types=1);

namespace App\Features\Gallery;

use Core\Repository\BaseRepositoryInterface;

// use App\Entities\Gallery;

/**
 * Generated File - Date: 2025-10-30 20:01
 * interface for Gallery.
 */
interface GalleryRepositoryInterface extends BaseRepositoryInterface
{
#    /**
#     * Find a Gallery by ID, selecting only specified columns (raw data).
#     *
#     * @param int $id
#     * @param array<string> $fields
#     * @return array<string, mixed>|null
#     */
#    public function findByIdWithFields(int $id, array $fields): ?array;
#
#    /**
#     * Find Gallery records based on criteria with full entity mapping.
#     *
#     * @param array<string, mixed> $criteria
#     * @param array<string, string> $orderBy
#     * @param int|null $limit
#     * @param int|null $offset
#     * @return array<Gallery>
#     */
#    public function findBy(
#        array $criteria = [],
#        array $orderBy = [],
#        ?int $limit = null,
#        ?int $offset = null
#    ): array;
#
#    /**
#     * Create a new Gallery.
#     *
#     * @param Gallery $gallery
#     * @return Gallery The created Gallery with ID
#     */
#    public function create(Gallery $gallery): Gallery;
#
#    /**
#     * Update an existing Gallery.
#     *
#     * @param Gallery $gallery
#     * @return bool True if update was successful
#     */
#    public function update(Gallery $gallery): bool;
#
#    /**
#     * Update selected fields for a Gallery by its primary ID.
#     *
#     * @param int $id The record ID.
#     * @param array<string, mixed> $fieldsToUpdate Associative array of fields to update.
#     * @return bool True on success, false on failure.
#     */
#    public function updateFields(int $id, array $fieldsToUpdate): bool;
#
#    /**
#     * Delete a Gallery (hard delete).
#     *
#     * @param int $id
#     * @return bool True if deletion was successful
#     */
#    public function delete(int $id): bool;
#
#    /**
#     * Count total Gallery records matching criteria.
#     *
#     * @param array<string, mixed> $criteria Optional filtering criteria
#     * @return int Total number of records matching criteria
#     */
#    public function countBy(array $criteria = []): int;
#
#    /**
#     * Convert a Gallery entity to an array with selected fields.
#     *
#     * @param Gallery $gallery
#     * @param array<string> $fields
#     * @return array<string, mixed>
#     */
#    public function toArray(Gallery $gallery, array $fields = []): array;
#
}
