<?php

declare(strict_types=1);

// namespace App\Features\Admin\Generic\Form;
namespace App\Features\Generic\Form;

use Core\Form\AbstractFieldRegistry;
use Core\Form\FieldRegistryInterface; // Interface is implemented by AbstractFieldRegistry
use Core\Interfaces\ConfigInterface;
use InvalidArgumentException;

// Dynamic-me 4
/**
 * Registry for generic form field definitions loaded from configuration.
 */
class GenericFieldRegistry extends AbstractFieldRegistry
{
    private ConfigInterface $config;
    private array $fieldConfig = []; // Cache for loaded config

    /**
     * Constructor
     *
     * @param ConfigInterface $config The configuration service.
     * @param FieldRegistryInterface|null $baseRegistry Optional base registry for inheritance.
     */
    public function __construct(
        ConfigInterface $config,
        ?FieldRegistryInterface $baseRegistry = null // Allow injecting a base registry if needed
    ) {
        parent::__construct($baseRegistry); // Pass base registry to parent
        $this->config = $config;
        // Load the entire field config at construction
        $this->fieldConfig = $this->config->get('form_fields'); // Assumes 'form_fields' is the key
        if (!is_array($this->fieldConfig)) {
            throw new \RuntimeException("Form field configuration ('form_fields') is missing or invalid.");
        }
    }

    /**
     * Get field definition by name for a specific entity type.
     * Checks common fields first, then entity-specific config, then base registry.
     *
     * @param string $fieldName The name of the field (e.g., 'title', 'content').
     * @param string $entityType The entity type key (e.g., 'posts', 'users').
     * @return array|null The field definition array or null if not found.
     */
    public function getForEntity(string $fieldName, string $entityType): ?array
    {
        // 1. Check common fields defined in AbstractFieldRegistry::getCommonField
        $commonField = $this->getCommonField($fieldName);

        // 2. Check entity-specific configuration
        $entityConfig = $this->getEntityTypeConfig($entityType);
        $entitySpecificDef = $entityConfig['definitions'][$fieldName] ?? null;

        // 3. Check base registry (if provided)
        $baseDefinition = $this->baseRegistry ? $this->baseRegistry->get($fieldName) : null;

        // --- Merging Logic ---
        // Start with the base definition (if any)
        $finalDef = $baseDefinition ?? [];

        // Merge common field definition (common overrides base)
        if ($commonField) {
            $finalDef = array_replace_recursive($finalDef, $commonField);
        }

        // Merge entity-specific definition (entity overrides common and base)
        if ($entitySpecificDef) {
            // Special handling for attributes: merge them instead of replacing
            if (isset($finalDef['attributes']) && isset($entitySpecificDef['attributes'])) {
                $entitySpecificDef['attributes'] = array_merge(
                    $finalDef['attributes'],
                    $entitySpecificDef['attributes']
                );
            }
            $finalDef = array_replace_recursive($finalDef, $entitySpecificDef);
        }

        // Apply top-level defaults if a definition was found
        if (!empty($finalDef)) {
            $defaults = $this->fieldConfig['default'] ?? [];
            // Merge defaults carefully, ensuring existing values take precedence
            $finalDef = array_merge($defaults, $finalDef);

            // Re-apply specific attributes after defaults if they existed
            if (isset($entitySpecificDef['attributes'])) {
                $finalDef['attributes'] = $entitySpecificDef['attributes'];
            } elseif (isset($commonField['attributes'])) {
                $finalDef['attributes'] = $commonField['attributes'];
            } elseif (isset($baseDefinition['attributes'])) {
                $finalDef['attributes'] = $baseDefinition['attributes'];
            }
        }


        // Return null if no definition was found anywhere
        return !empty($finalDef) ? $finalDef : null;
    }

    /**
     * Get the list of field names to display for an entity type's form.
     *
     * @param string $entityType The entity type key (e.g., 'posts', 'users').
     * @return array<string> An array of field names.
     */
    public function getFieldsForEntity(string $entityType): array
    {
        $entityConfig = $this->getEntityTypeConfig($entityType);
        return $entityConfig['fields'] ?? [];
    }

    /**
     * Helper to get the config section for a specific entity type.
     *
     * @param string $entityType The entity type key.
     * @return array The configuration array for the entity type.
     * @throws InvalidArgumentException If configuration for the entity type is not found.
     */
    private function getEntityTypeConfig(string $entityType): array
    {
        if (!isset($this->fieldConfig['entities'][$entityType])) {
            throw new InvalidArgumentException("Form field configuration not found for entity type: " . $entityType);
        }
        return $this->fieldConfig['entities'][$entityType];
    }

    /**
     * Override get() to prevent direct calls without entityType.
     * Use getForEntity() instead when using GenericFieldRegistry.
     *
     * @param string $fieldName
     * @return array|null
     * @throws \BadMethodCallException Always throws because entity type is required for config lookup.
     */
    public function get(string $fieldName): ?array
    {
        // While the parent get() provides common/base lookup, this registry's
        // primary purpose is entity-specific lookup via config.
        // If you need ONLY common/base fields, inject AbstractFieldRegistry directly.
        // If you need entity-specific fields, use getForEntity().
        // Allowing get() here could lead to confusion about which definition is returned.
        throw new \BadMethodCallException('Direct call to get() is not supported on GenericFieldRegistry. Use getForEntity(string $fieldName, string $entityType) instead.');

        // Alternatively, you could allow it but it would only return common/base fields:
        // return parent::get($fieldName);
    }
}