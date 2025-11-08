<?php

declare(strict_types=1);

namespace App\Features\Testy;

// use App\Entities\Testy;

/**
 * Generated File - Date: 2025-10-30 20:01
 * interface for Testy.
 */
interface TestyRepositoryInterface
{
    /**
     * Find a Testy by ID with full entity mapping.
     *
     * @param int $id
     * @return Testy|null
     */
    public function findById(int $id): ?Testy;

    /**
     * Find a Testy by ID, selecting only specified columns (raw data).
     *
     * @param int $id
     * @param array<string> $fields
     * @return array<string, mixed>|null
     */
    public function findByIdWithFields(int $id, array $fields): ?array;

    /**
     * Find Testy records based on criteria with full entity mapping.
     *
     * @param array<string, mixed> $criteria
     * @param array<string, string> $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array<Testy>
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Create a new Testy.
     *
     * @param Testy $testy
     * @return Testy The created Testy with ID
     */
    public function create(Testy $testy): Testy;

    /**
     * Update an existing Testy.
     *
     * @param Testy $testy
     * @return bool True if update was successful
     */
    public function update(Testy $testy): bool;

    /**
     * Update selected fields for a Testy by its primary ID.
     *
     * @param int $id The record ID.
     * @param array<string, mixed> $fieldsToUpdate Associative array of fields to update.
     * @return bool True on success, false on failure.
     */
    public function updateFields(int $id, array $fieldsToUpdate): bool;

    /**
     * Delete a Testy (hard delete).
     *
     * @param int $id
     * @return bool True if deletion was successful
     */
    public function delete(int $id): bool;

    /**
     * Count total Testy records matching criteria.
     *
     * @param array<string, mixed> $criteria Optional filtering criteria
     * @return int Total number of records matching criteria
     */
    public function countBy(array $criteria = []): int;

    /**
     * Convert a Testy entity to an array with selected fields.
     *
     * @param Testy $testy
     * @param array<string> $fields
     * @return array<string, mixed>
     */
    public function toArray(Testy $testy, array $fields = []): array;

}
