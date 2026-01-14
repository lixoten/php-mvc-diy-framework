<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Interfaces\ConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for retrieving field definitions with layered fallbacks.
 *
 * Fallback order:
 * 1. Page/view context (set via setPageKey, e.g., 'local_posts')
 * 2. Entity/table context (set via setEntityName, e.g., 'posts')
 * 3. Base/global config ('base')
 *
 * Usage:
 * - setPageKey('local_posts') for page-specific overrides
 * - setEntityName('posts') for entity/table-level fields
 * - getFieldWithFallbacks('title') to resolve a field definition
 * $service->setPageKey('local_posts');
 * $service->setEntityName('posts');
 * $fieldDef = $service->getFieldWithFallbacks('title');
*/

class FieldRegistryService
{
    protected ConfigInterface $configService;
    protected ?string $pageKey = null;
    protected ?string $entityName = null;

    public function __construct(
        ConfigInterface $configService,
        protected LoggerInterface $logger,
        protected FieldDefinitionSchemaValidatorService $fieldDefinitionSchemaValidatorService
    ) {
        $this->configService    = $configService;
    }

    // public function setEntityName(string $entityName): void
    // {
    //     $this->entityName = $entityName;
    // }
    // public function getEntityName(): string
    // {
    //     return $this->entityName;
    // }
    // public function setPageName(string $pageKey): void
    // {
    //     $this->pageKey = $pageKey;
    // }
    // public function getLocalType(): string
    // {
    //     return $this->pageKey;
    // }

    /**
     * Validate an array of field names against known schema.
     * Returns only valid fields, while logging and triggering warnings
     * for invalid ones (developer visibility).
     *
     * @param array $fieldNames
     * @return array
     */
    public function filterAndValidateFields(array $fieldNames, string $pageKey, string $entityName): array
    {
        $invalidFields = [];
        $validFields   = [];
        foreach ($fieldNames as $name) {
            if ($this->getFieldWithFallbacks($name, $pageKey, $entityName) !== null) {
                $validFields[] = $name;
            } else {
                $invalidFields[] = $name;
            }
        }

        if (!empty($invalidFields)) {
            if (($_ENV['APP_ENV'] ?? null) === 'development') {
                $message = 'Removed invalid fields';
                $message2 = "Page: {$pageKey}, Entity: {$entityName} ";
                $string = implode(', ', $invalidFields);
                $message .= ": $string";
                $message .= " Look in config file \"(feature)_view_(edit/view/create).php\".";
                $message .= "-- $message2";
            }
            $this->logger->warning(
                $message,
                [
                    'dev_code' => 'ERR-DEV88'
                ]
            );
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
    public function getFieldWithFallbacks(string $fieldName, string $pageKey, string $entityName): ?array
    {
        // 1. Page-Context-specific config: src/App/Features/{Entity}/Config/{pageKey}_fields.php
        // This assumes pageKey for a feature is like 'testy_list' or 'testy_edit'
        // and the config file is field_testy.php
        //$featureEntityName = str_replace(['_list', '_edit'], '', $pageKey); // Extract 'testy' from 'testy_list'
        $key = str_replace('_', '_fields_', $pageKey);

        // findloc - Read testy_fields_edit.php /..._fields_list.root.php
        $field = $this->configService->getFromFeature($entityName, $key . ".$fieldName");
        if ($field !== null) {
            return $field;
        }

        // $key = str_replace('_', '_fields_', $pageKey);
        // 2. Entity-specific config: config: src/App/Features/{Entity}/Config/{entityName}_fields.php
        // findloc - Read testy_fields_root.php
        $field = $this->configService->getFromFeature($entityName, $entityName . '_fields_root' . ".$fieldName");
        if ($field !== null) {
            return $field;
        }

        // 3. Base config: config/render/fields_base.php
        // findloc - Read base_fields.php
        $field = $this->configService->get('render/base_fields' . '.' . $fieldName);
        if ($field !== null) {
            return $field;
        }

        // 4. Not found
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
        $field = $this->configService->get('list_fields/' . $listName . "." . $fieldName);
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


    // /**
    //  * Log a warning message in development mode
    //  */
    // private function logWarning(string $message): void
    // {
    //     if ($_ENV['APP_ENV'] === 'development') {
    //         trigger_error("Field Registry Service Warning: {$message}", E_USER_WARNING);
    //     }

    //     // Always log to system log
    //     error_log("Field Registry Service: {$message}");
    // }
}
