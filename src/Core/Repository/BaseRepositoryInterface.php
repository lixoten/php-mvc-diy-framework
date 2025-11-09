<?php

declare(strict_types=1);

namespace Core\Repository;

// Dynamic-me 2
/**
 * Defines a common interface for repositories used in generic CRUD operations.
 */
interface BaseRepositoryInterface
{
    public function findFoo(): string;

    /**
     * Find testy by user ID, selecting only specified columns. xxxBase
     *
     * @param int $userId
     * @param array<int, string> $fields
     * @param array<string, string> $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array<array<string, mixed>>
     */
    public function findByUserIdWithFields(
        int $userId,
        array $fields,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;



    /**
     * Find records by criteria with specified fields. xxxBase
     *
     * @param array<string, mixed> $criteria
     * @param array<string> $fields
     * @param array<string, string> $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array<array<string, mixed>>
     */
    public function findByCriteriaWithFields(
        array $criteria,
        array $fields,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;


    /**
     * Count entities by user ID. xxxBase
     *
     * @param int $userId
     * @return int
     */
    public function countByUserId(int $userId): int;

    // Store ////////////////////////////////////////

    /**
     * Find entities by store ID with specified fields (raw data). xxxBase
     *
     * @param int $storeId
     * @param array<int, string> $fields
     * @param array<string, string> $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array<array<string, mixed>>
     */
    public function findByStoreIdWithFields(
        int $storeId,
        array $fields,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;


    /**
     * Count entities by store ID. xxxBase
     *
     * @param int $storeId
     * @return int
     */
    public function countByStoreId(int $storeId): int;


        ////////////////////////////////
    ////user list /////////////////////////////////////
    /**
     * Find all entities, selecting only specified columns. xxxBase
     *
     * @param array<string> $fields The fields to select.
     * @param array<string, string> $orderBy Optional sorting criteria.
     * @param int|null $limit Maximum number of results.
     * @param int|null $offset Result offset for pagination.
     * @return array<array<string, mixed>> An array of associative arrays representing the records.
     */
    public function findAllWithFields(
        array $fields,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Count total record. xxxBase
     *
     * @param array $criteria Optional filtering criteria
     * @return int Total number of records matching criteria
     */
    public function countAll(array $criteria = []): int;


    /**
     * Count records by criteria. xxxBase
     *
     * @param array $criteria Optional filtering criteria [field => value].
     * @return int Total number of entities matching criteria.
     */
    public function countBy(array $criteria = []): int;


    //////////////////////////////////////



    /**
     * Delete a record by its primary ID, (hard delete). xxxBase
     *
     * Child repositories can override this for soft deletes or cascading deletes.
     *
     * @param int $id The ID of the entity to delete.
     * @return bool True on success, false on failure.
     */
    public function delete(int $id): bool;





        //??????????????????????????????????????????/

    /**
     * Find entities by user ID with full entity mapping. xxxFook
     *
     * Returns an array of fully hydrated entity objects filtered by user_id.
     * This method delegates to findBy() with user_id criteria, so child repositories
     * must implement findBy() and mapToEntity() for proper entity hydration.
     *
     * Used for multi-tenant queries where entities belong to specific users.
     * For raw database columns without entity mapping, use findByUserIdWithFields() instead.
     *
     * @param int $userId The user ID to filter by
     * @param array<string, string> $orderBy Optional sorting criteria (field => 'ASC'|'DESC')
     * @param int|null $limit Maximum number of entities to return
     * @param int|null $offset Number of entities to skip for pagination
     * @return array<object> Array of fully hydrated entity objects matching the user_id
     */
    public function findByUserId(
        int $userId,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): array;


















    /**
     * Update selected fields for an entity by its primary ID. xxxBase
     *
     * @param int $id The entity ID.
     * @param array<string, mixed> $fieldsToUpdate Associative array of fields to update.
     * @return bool True on success, false on failure.
     */
    public function updateFields(int $id, array $fieldsToUpdate): bool;


    /**
     * Insert a new record into the database. xxxBase
     *
     * @param array<string, mixed> $data The data to insert.
     * @return int The ID of the newly inserted record.
     */
    public function insertFields(array $data): int;


    /**
     * Find an entity by its primary ID with full entity mapping.
     *
     * This method returns a fully hydrated entity object with all properties populated.
     * Use this when you need the complete entity with business logic methods available.
     *
     * For raw database columns without entity hydration, use findByIdWithFields() instead.
     *
     * @param int $id The entity primary key ID
     * @return object|null The fully hydrated entity object or null if not found
     */
    public function findById(int $id): ?object; // Type Hint, this is actually in Testy


#
#
#    // /**
#    //  * Find an entity by its primary ID.
#    //  *
#    //  * @param int $id The entity ID.
#    //  * @return object|null The entity object or null if not found.
#    //  */
#    // public function findById(int $id): ?object;
#
#
#    public function findByStoreId(
#        int $storeId,
#        array $orderBy = [],
#        ?int $limit = null,
#        ?int $offset = null
#    ): array;
#






#
#    /**
#     * Find all entities matching criteria.
#     *
#     * @param array $criteria Optional filtering criteria [field => value].
#     * @param array $orderBy Optional sorting criteria [field => 'ASC'|'DESC'].
#     * @param int|null $limit Maximum number of results.
#     * @param int|null $offset Result offset for pagination.
#     * @return array An array of entity objects.
#     */
#    public function findBy(
#        array $criteria = [],
#        array $orderBy = [],
#        ?int $limit = null,
#        ?int $offset = null
#    ): array;
#

#
#        /**
#     * Convert an entity to an array with selected fields.
#     *
#     * @param object $entity The entity object to convert.
#     * @param array $fields  The fields to include in the array.
#     * @return array
#     */
#    // public function toArray(object $entity, array $fields = []): array;

    /**
     * Fetches a record by ID, returning only the specified fields. xxxBase
     *
     * @param int $id
     * @param array<string> $fields
     * @return array<string, mixed>|null
     */
    public function findByIdWithFields(int $id, array $fields): ?array;
}
