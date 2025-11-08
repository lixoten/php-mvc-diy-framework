<?php
// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\Core\Services\DataTransformerService.php

declare(strict_types=1);

namespace Core\Services;

/**
 * Data Transformer Service
 *
 * Transforms data between different representations using configuration-driven
 * field definitions from FieldRegistryService.
 *
 * This service handles bidirectional data transformation:
 * - Storage → Display: Decodes JSON, converts types for human-readable display
 * - Input → Storage: Normalizes types for database/storage persistence
 *
 * Transformation strategy is determined by the 'data_transformer' key in field
 * configuration loaded through FieldRegistryService's layered fallback system
 * (base/entity/page).
 *
 * Common use cases:
 * - List views: Display JSON arrays as formatted badges
 * - Forms: Convert database values to input-ready format
 * - Detail pages: Format data for display
 * - Database inserts/updates: Normalize input values
 * - API responses: Transform for JSON serialization
 *
 * Supported transformations:
 * - 'json_array': JSON string ↔ PHP array (bidirectional normalization)
 * - 'boolean': Various truthy values ↔ bool (bidirectional normalization)
 * - 'date': Database format ↔ Human-readable format (future)
 * - 'currency': Cents ↔ Formatted money (future)
 *
 * @see FieldRegistryService For field definition resolution
 */
class DataTransformerService
{
    /**
     * Field registry service for loading merged field definitions
     *
     * @var FieldRegistryService
     */
    private FieldRegistryService $fieldRegistryService;

    /**
     * Constructor
     *
     * @param FieldRegistryService $fieldRegistryService Service for field definition lookup
     */
    public function __construct(FieldRegistryService $fieldRegistryService)
    {
        $this->fieldRegistryService = $fieldRegistryService;
    }


    /**
     * Transform data from storage format to display format
     *
     * Converts raw storage values (database, cache, session) into display-ready formats
     * for use in views, lists, forms, or any presentation layer.
     *
     * @param array<string, mixed> $data Raw data from storage layer
     * @param string $pageName Page context (e.g., 'user_edit', 'user_list', 'user_detail')
     * @param string $entityName Entity name (e.g., 'user', 'post')
     * @return array<string, mixed> Display-ready data with transformed values
     */
    public function toDisplay(array $data, string $pageName, string $entityName): array
    {
        return $this->transform($data, $pageName, $entityName, 'display');
    }


    /**
     * Transform data from input format to storage format
     *
     * Normalizes user input, form submissions, or API payloads into storage-compatible
     * formats for persistence in database, cache, or session.
     *
     * Note: Final JSON encoding is handled by the Repository layer (AbstractRepository).
     * This method only normalizes PHP types.
     *
     * @param array<string, mixed> $data Raw input data (form, API, user input)
     * @param string $pageName Page context (e.g., 'user_edit')
     * @param string $entityName Entity name (e.g., 'user')
     * @return array<string, mixed> Storage-ready data with normalized values
     */
    public function toStorage(array $data, string $pageName, string $entityName): array
    {
        return $this->transform($data, $pageName, $entityName, 'storage');
    }


    /**
     * Core transformation logic (DRY helper)
     *
     * @param array<string, mixed> $data Raw data
     * @param string $pageName Page context
     * @param string $entityName Entity name
     * @param string $direction Transformation direction ('display' or 'storage')
     * @return array<string, mixed> Transformed data
     */
    private function transform(
        array $data,
        string $pageName,
        string $entityName,
        string $direction
    ): array {
        $transformed = $data;

        foreach ($data as $fieldName => $value) {
            $fieldDef = $this->fieldRegistryService->getFieldWithFallbacks(
                $fieldName,
                $pageName,
                $entityName
            );

            if ($fieldDef === null) {
                continue;
            }

            $transformer = $fieldDef['data_transformer'] ?? null;

             // Apply transformation based on direction
            $transformed[$fieldName] = match ($transformer) {
                'json_array' => $direction === 'display'
                    ? $this->normalizeToArray($value)
                    : $this->normalizeToJsonString($value), // Convert array to JSON string for storage
                'boolean' => $direction === 'display'
                    ? $this->normalizeToBoolean($value)
                    : ($this->normalizeToBoolean($value) ? '1' : '0'), // Convert boolean to '1' or '0' string for storage
                default => $value
            };
        }

        return $transformed;
    }



    /**
     * Normalize value to array
     *
     * Handles multiple input formats and converts them to a PHP array:
     * - Already an array: Pass through unchanged
     * - JSON string: Decode to array
     * - Other types: Return empty array
     *
     * @param mixed $value Raw value from any source
     * @return array<int|string, mixed> Normalized PHP array
     */
    private function normalizeToArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Normalize value to JSON string
     *
     * Converts a PHP array to a JSON string for storage.
     * If the value is not an array, it returns an empty JSON array string.
     *
     * @param mixed $value Raw value (expected to be an array for storage)
     * @return string Normalized JSON string
     */
    private function normalizeToJsonString(mixed $value): string
    {
        if (is_array($value)) {
            return json_encode($value);
        }
        // If it's not an array, store an empty JSON array
        return '[]';
    }


    /**
     * Normalize value to boolean
     *
     * Converts various truthy/falsy representations to strict boolean:
     * - Already boolean: Pass through unchanged
     * - Truthy values: '1', 1, 'on', true, 'yes' → true
     * - All other values → false
     *
     * Common use cases:
     * - HTML checkbox inputs send 'on' when checked
     * - Database CHAR(1) 'Y'/'N' or '1'/'0' flags
     * - API boolean strings 'true'/'false'
     *
     * @param mixed $value Raw value from any source
     * @return bool Normalized boolean value
     */
    private function normalizeToBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array($value, ['1', 1, 'on', true, 'yes', 'Y'], true);
    }
}
