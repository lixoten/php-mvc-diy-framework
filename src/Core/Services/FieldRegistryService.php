<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Interfaces\ConfigInterface;

/**
 * Service for retrieving field definitions with layered fallbacks.
 *
 * Fallback order:
 * 1. Page/view context (set via setPageName, e.g., 'local_posts')
 * 2. Entity/table context (set via setEntityName, e.g., 'posts')
 * 3. Base/global config ('base')
 *
 * Usage:
 * - setPageName('local_posts') for page-specific overrides
 * - setEntityName('posts') for entity/table-level fields
 * - getFieldWithFallbacks('title') to resolve a field definition
 * $service->setPageName('local_posts');
 * $service->setEntityName('posts');
 * $fieldDef = $service->getFieldWithFallbacks('title');
*/

class FieldRegistryService
{
    protected ConfigInterface $configService;
    protected ?string $pageName = null;
    protected ?string $entityName = null;

    public function __construct(ConfigInterface $configService)
    {
        $this->configService    = $configService;
    }

    public function setEntityName(string $entityName): void
    {
        $this->entityName = $entityName;
    }
    public function getEntityName(): string
    {
        return $this->entityName;
    }
    public function setPageName(string $pageName): void
    {
        $this->pageName = $pageName;
    }
    public function getLocalType(): string
    {
        return $this->pageName;
    }

    /**
     * Validate an array of field names against known schema.
     * Returns only valid fields, while logging and triggering warnings
     * for invalid ones (developer visibility).
     *
     * @param array $fieldNames
     * @return array
     */
    public function filterAndValidateFields(array $fieldNames): array
    {
        $invalidFields = [];
        $validFields   = [];
        foreach ($fieldNames as $name) {
            if ($this->getFieldWithFallbacks($name) !== null) {
                $validFields[] = $name;
            } else {
                $invalidFields[] = $name;
            }
        }

        if (!empty($invalidFields)) {
            if (($_ENV['APP_ENV'] ?? null) === 'development') {
                $message = 'Removed invalid fields';
                $message2 = "Page: {$this->pageName}, Entity: {$this->entityName} ";
                $string = implode(', ', $invalidFields);
                $message .= ": $string";
                $message .= "-- $message2";
                $this->logWarning($message . ' - ERR-DEV88');
            }
        }

        return $validFields;
    }


    /**
     * Resolve a field definition for the current page/view, with fallbacks:
     * 1. Page/view context (e.g., list_fields/local_posts.php)
     * 2. Entity/table (e.g., list_fields/posts.php)
     * 3. Base/global (e.g., list_fields/base.php)
     *
     * @param string $fieldName   The field name (e.g. 'title')
     * @return array|null         The field definition or null if not found
     */
    public function getFieldWithFallbacks(string $fieldName): ?array
    {
        // 1. Page-Context-specific config: config/list_fields/posts.php
        if (isset($this->pageName)) {
            $listName = $this->pageName;
            $field = $this->getField($fieldName, $listName);
            if ($field !== null) {
                $field['label'] = '*' . $field['label'];//fixme - t/he "*" is mine indicator
                return $field;
            }
        }

        // 2. Entity-specific config: config/list_fields/posts.php
        if (isset($this->entityName)) {
            $listName = $this->entityName; // e.g., 'posts'
            //$field = $this->configService->get('list_fields/' . $entityName . "." . $fieldName); // loads "list_fields/posts.php"
            $field = $this->getField($fieldName, $listName);
            if ($field !== null) {
                $field['label'] = '!' . $field['label'];
                return $field;
            }
        }


        // 3. Base config: config/list_fields_base.php
        $field = $this->getField($fieldName, 'base');
        //$field = $this->configService->get('list_fields/base' . "." . $fieldName); // loads "list_fields/posts.php"
        if ($field !== null) {
            $field['label'] = '-' . $field['label'];
            return $field;
        }

        // 3. Not found
        return null;
    }



    /**
     * Get a single field definition for an entity, with all fallbacks.
     *
     * @param string $fieldName   The field name (e.g. 'title')
     * @param string $listName  The entity/table name (e.g. 'posts')
     * @return array|null         The field definition or null if not found
     */
    public function getField(string $fieldName, string $listName): ?array
    {
        $field = $this->configService->get('list_fields/' . $listName . "." . $fieldName); // loads "list_fields/posts.php"
        if ($field !== null) {
            return $field;
        }

        // 3. Not found
        return null;
    }




    /**
     * Get multiple field definitions for an entity.
     *
     * @param string $entityType
     * @param array $fieldNames
     * @return array<string, array>  Associative array of fieldName => definition
     */
    public function getFields(string $entityType, array $fieldNames): array
    {
        $fields = [];
        foreach ($fieldNames as $name) {
            $def = $this->getField($entityType, $name);
            if ($def !== null) {
                $fields[$name] = $def;
            }
        }
        return $fields;
    }


    /**
     * Log a warning message in development mode
     */
    private function logWarning(string $message): void
    {
        if ($_ENV['APP_ENV'] === 'development') {
            trigger_error("Field Registry Service Warning: {$message}", E_USER_WARNING);
        }

        // Always log to system log
        error_log("Field Registry Service: {$message}");
    }
}
