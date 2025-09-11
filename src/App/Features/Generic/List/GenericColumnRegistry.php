<?php

declare(strict_types=1);

// namespace App\Features\Admin\Generic\List;
namespace App\Features\Generic\List;

use Core\Interfaces\ConfigInterface;
use Core\List\AbstractFieldRegistry;
use InvalidArgumentException;

// Dynamic-me 3
/**
 * Registry for generics list column definitions
 */
class GenericColumnRegistry extends AbstractFieldRegistry
{
    private ConfigInterface $config; // ADD
    private array $columnConfig = []; // ADD Cache for loaded config

    // ADD Constructor to inject ConfigInterface
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
        // Load the entire column config at construction or lazy load in getEntityTypeConfig
        $this->columnConfig = $this->config->get('list_columns'); // Assumes 'list_columns' is the key for the file
        if (!is_array($this->columnConfig)) {
             throw new \RuntimeException("List column configuration ('list_columns') is missing or invalid.");
        }
    }

    /**
     * Get column definition by name for a specific entity type.
     * Checks common columns first, then entity-specific config.
     *
     * @param string $columnName The name of the column (e.g., 'title', 'id').
     * @param string $entityType The entity type key (e.g., 'posts', 'users').
     * @return array|null The column definition array or null if not found.
     */
    public function getForEntity(string $columnName, string $entityType): ?array
    {
        // // 1. Check common columns defined in AbstractColumnRegistry
        // $commonColumn = $this->getCommonColumn($columnName);
        // if ($commonColumn) {
        //     return $commonColumn;
        // }
        // 2. Check entity-specific configuration


        // 1. First check entity-specific configuration
        $entityConfig = $this->getEntityTypeConfig($entityType);
        $columnDef = $entityConfig['columns'][$columnName] ?? null;

        if ($columnDef) {
            // Apply defaults
            $defaults = $this->columnConfig['default'] ?? [];
            $columnDef = array_merge($defaults, $columnDef);

            // Resolve formatter string if needed
            if (isset($columnDef['formatter']) && is_string($columnDef['formatter'])) {
                $formatterName = $columnDef['formatter'];
                $formatters = $this->columnConfig['formatters'] ?? [];
                if (isset($formatters[$formatterName]) && is_callable($formatters[$formatterName])) {
                    $columnDef['formatter'] = $formatters[$formatterName];
                } else {
                    // Handle case where formatter string doesn't match a defined callable
                    // Maybe default to null or throw an error
                    unset($columnDef['formatter']); // Remove invalid formatter string
                     trigger_error("Undefined list column formatter referenced: " . $formatterName, E_USER_WARNING);
                }
            }
            return $columnDef;
        }

        // 2. Fall back to common columns defined in AbstractColumnRegistry
        $commonColumn = $this->getCommonColumn($columnName);
        if ($commonColumn) {
            return $commonColumn;
        }

        // 3. Column not found
        trigger_error("Column definition not found for entity '$entityType', column '$columnName'", E_USER_WARNING);
        return null;
    }

    /**
     * Get the list of columns to display for an entity type.
     *
     * @param string $entityType
     * @return array<string>
     */
    public function getDisplayColumnsForEntity(string $entityType): array
    {
         $entityConfig = $this->getEntityTypeConfig($entityType);
         return $entityConfig['display'] ?? [];
    }


    /**
     * Helper to get the config section for a specific entity type.
     */
    private function getEntityTypeConfig(string $entityType): array
    {
        if (!isset($this->columnConfig['entities'][$entityType])) {
             throw new InvalidArgumentException("Configuration not found for entity type: " . $entityType);
        }
        return $this->columnConfig['entities'][$entityType];
    }


    // REMOVE ALL HARDCODED GETTERS (getId, getTitle, getUsername, getStatus, getCreatedAt)
    // public function getId(): array { ... }
    // public function getTitle(): array { ... }
    // public function getUsername(): array { ... }
    // public function getStatus(): array { ... }
    // public function getCreatedAt(): array { ... }

    // Override get() to prevent direct calls without entityType
    /**
     * @throws \BadMethodCallException Always throws because entity type is required. Use getForEntity().
     */
    public function get(string $columnName): ?array
    {
        throw new \BadMethodCallException('Direct call to get() is not supported. Use getForEntity(string $columnName, string $entityType) instead.');
    }




    // /**
    //  * Get the ID column definition
    //  */
    // public function getId(): array
    // {
    //     return [
    //         'label' => 'ID',
    //         'sortable' => true,
    //         'formatter' => null,
    //     ];
    // }

    // /**
    //  * Get the title column definition
    //  */
    // public function getTitle(): array
    // {
    //     return [
    //         'label' => 'Title',
    //         'sortable' => true,
    //         'formatter' => function ($value) {
    //             return htmlspecialchars($value ?? '');
    //         },
    //     ];
    // }

    // /**
    //  * Get the username column definition
    //  */
    // public function getUsername(): array
    // {
    //     return [
    //         'label' => 'Author',
    //         'sortable' => true,
    //         'formatter' => function ($value) {
    //             return htmlspecialchars($value ?? 'Unknown');
    //         },
    //     ];
    // }

    // /**
    //  * Get the status column definition
    //  */
    // public function getStatus(): array
    // {
    //     return [
    //         'label' => 'Status',
    //         'sortable' => true,
    //         'formatter' => function ($value) {
    //             $statusClass = ($value == 'Published') ? 'success' : 'warning';
    //             return '<span class="badge bg-' . $statusClass . '">' . htmlspecialchars($value) . '</span>';
    //         },
    //     ];
    // }

    // /**
    //  * Get the created_at column definition
    //  */
    // public function getCreatedAt(): array
    // {
    //     return [
    //         'label' => 'Created At',
    //         'sortable' => true,
    //         'formatter' => function ($value) {
    //             return htmlspecialchars($value ?? '');
    //         },
    //     ];
    // }
}
