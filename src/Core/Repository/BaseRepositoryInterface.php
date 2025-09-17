<?php

declare(strict_types=1);

namespace Core\Repository;

// Dynamic-me 2
/**
 * Defines a common interface for repositories used in generic CRUD operations.
 */
interface BaseRepositoryInterface
{
    /**
     * Update selected fields for an entity by its primary ID.
     *
     * @param int $id The entity ID.
     * @param array<string, mixed> $fieldsToUpdate Associative array of fields to update.
     * @return bool True on success, false on failure.
     */
    public function updateFields(int $id, array $fieldsToUpdate): bool;

    /**
     * Find an entity by its primary ID.
     *
     * @param int $id The entity ID.
     * @return object|null The entity object or null if not found.
     */
    public function findById(int $id): ?object;

    /**
     * Find all entities matching criteria.
     *
     * @param array $criteria Optional filtering criteria [field => value].
     * @param array $orderBy Optional sorting criteria [field => 'ASC'|'DESC'].
     * @param int|null $limit Maximum number of results.
     * @param int|null $offset Result offset for pagination.
     * @return array An array of entity objects.
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Create a new entity in the database.
     *
     * @param object $entity The entity object to create.
     * @return object
     */
    public function create(object $entity): object;

    /**
     * Update an existing entity in the database.
     *
     */
    public function update(object $entity): bool;

    /**
     * Delete an entity by its primary ID.
     *
     * @param int $id The ID of the entity to delete.
     * @return bool True on success, false on failure.
     */
    public function delete(int $id): bool;

    /**
     * Count total entities matching criteria.
     *
     * @param array $criteria Optional filtering criteria [field => value].
     * @return int Total number of entities matching criteria.
     */
    public function countBy(array $criteria = []): int;

        /**
     * Convert an entity to an array with selected fields.
     *
     * @param object $entity The entity object to convert.
     * @param array $fields  The fields to include in the array.
     * @return array
     */
    // public function toArray(object $entity, array $fields = []): array;
}
