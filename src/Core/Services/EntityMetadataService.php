<?php

declare(strict_types=1);

namespace Core\Services;

use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;

/**
 * Service for retrieving metadata about entity classes using reflection.
 * This service helps in checking the existence of properties and public getters on entity objects.
 */
class EntityMetadataService
{
    public function __construct(
        protected LoggerInterface $logger
    ) {
    }

    /**
     * Checks if a given field name exists as a public property or a public getter method
     * on the specified entity class.
     *
     * A getter method is assumed if it follows the pattern 'getFieldName' or 'isFieldName' for booleans.
     * The field name 'id' is a special case often handled by base entities without an explicit 'getId'
     * if it's directly accessible.
     *
     * @param string $entityFqcn The fully qualified class name of the entity.
     * @param string $fieldName The name of the field to check (e.g., 'title', 'is_verified').
     * @return bool True if the field exists as a property or getter, false otherwise.
     */
    public function hasField(string $entityFqcn, string $fieldName): bool
    {
        if (!class_exists($entityFqcn)) {
            $this->logger->warning("Entity class '{$entityFqcn}' not found when checking for field '{$fieldName}'.", [
                'dev_code' => 'ERR-FIELD-META-01',
                'entity_fqcn' => $entityFqcn,
                'field_name' => $fieldName
            ]);
            return false;
        }

        try {
            $reflectionClass = new ReflectionClass($entityFqcn);

            // 1. Check for a public property
            if ($reflectionClass->hasProperty($fieldName)) {
                $property = $reflectionClass->getProperty($fieldName);
                if ($property->isPublic()) {
                    return true;
                }
            }

            // 2. Check for a public getter method (e.g., getTitle, isVerified, getId)
            $camelCaseFieldName = str_replace('_', '', ucwords($fieldName, '_')); // title_name -> TitleName
            $getterMethod = 'get' . $camelCaseFieldName;
            $isMethod = 'is' . $camelCaseFieldName;

            if ($reflectionClass->hasMethod($getterMethod)) {
                $method = $reflectionClass->getMethod($getterMethod);
                if ($method->isPublic()) {
                    return true;
                }
            }

            if ($reflectionClass->hasMethod($isMethod)) { // For boolean fields
                $method = $reflectionClass->getMethod($isMethod);
                if ($method->isPublic()) {
                    return true;
                }
            }

            return false;

        } catch (ReflectionException $e) {
            $this->logger->error("Reflection error for entity '{$entityFqcn}' while checking field '{$fieldName}': {$e->getMessage()}", [
                'dev_code' => 'ERR-FIELD-META-02',
                'entity_fqcn' => $entityFqcn,
                'field_name' => $fieldName,
                'exception' => $e
            ]);
            return false;
        }
    }
}
