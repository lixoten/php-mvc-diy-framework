<?php

declare(strict_types=1);

namespace Core\Form;

/**
 * Interface for field registries
 */
interface FieldRegistryInterface
{
    /**
     * Get a field definition by name
     *
     * @param string $fieldName
     * @return array|null
     */
    public function get(string $fieldName): ?array;
}
