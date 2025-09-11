<?php

declare(strict_types=1);

namespace Core\Registry;

/**
 * Interface for field registries
 */
interface FieldRegistryInterface
{
    /**
     * Get a field definition by name
     */
    public function get(string $fieldName): ?array;
}
