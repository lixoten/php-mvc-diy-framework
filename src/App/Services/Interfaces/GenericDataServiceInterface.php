<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

// Dynamic-me 3
/**
 * Interface for a service that fetches and formats list data for various entity types.
 */
interface GenericDataServiceInterface
{
    /**
     * Fetches a paginated list of data for a specific entity type,
     * optionally filtered by store ID, and maps the results to an array.
     *
     * @param string $entityType The key identifying the entity type (e.g., 'posts', 'users').
     * @param array<string> $fields An array of field names to include in the output items.
     * @param array // FIXME missing text
     * @param int $page The current page number (1-based).
     * @param int $limit The number of items per page.
     * @param array<string, string> $orderBy An associative array for ordering (e.g., ['created_at' => 'DESC']).
     * @return array{
     *     items: array<int, array<string, mixed>>,
     *     totalRecords: int,
     *     totalPages: int
     * } An array containing the formatted items, total record count, and total page count.
     */
    public function fetchListData(
        string $entityType,
        array $fields,
        array $criteria,
        int $page,
        int $limit,
        array $orderBy = []
    ): array;

    /**
     * Fetches a single entity by its ID.
     *
     * @param string $entityType The key identifying the entity type (e.g., 'posts', 'users').
     * @param int $entityId The ID of the entity to fetch.
     * @return object|null The entity object or null if not found.
     */
    public function fetchEntityById(string $entityType, int $entityId): ?object;


    /**
     * Fetch selected fields for an entity by ID (generic).
     *
     * @param string $entityType
     * @param int $entityId
     * @param array<string> $fields
     * @return array<string, mixed>|null
     * @throws RuntimeException if repository lacks required methods
     */
    public function fetchEntityFieldsById(string $entityType, int $entityId, array $fields): ?array;

    /**
     * Generic update of selected fields for an entity by id.
     *
     * @param string $entityType
     * @param int $entityId
     * @param array<string, mixed> $fieldsToUpdate
     * @return bool
     */
    public function updateEntityFields(string $entityType, int $entityId, array $fieldsToUpdate): bool;


    /**
     * Create a new entity instance of the specified type
     *
     * @param string $entityType The entity type to create
     * @return object A new entity instance
     * @throws RuntimeException If the entity type is not supported
     */
    public function createNewEntity(string $entityType): object;
}
