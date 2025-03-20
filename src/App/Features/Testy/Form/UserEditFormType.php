<?php

declare(strict_types=1);

namespace App\Features\Testy\Form;

use App\Core\Form\FormTypeInterface;
use App\Core\Form\FieldRegistryInterface;
use Core\Form\FieldRegistryInterface as FormFieldRegistryInterface;
use FieldRegistryInterface as GlobalFieldRegistryInterface;
use FormTypeInterface as GlobalFormTypeInterface;

/**
 * User edit form type
 */
class UserEditFormType implements GlobalFormTypeInterface
{
    private GlobalFieldRegistryInterface $fieldRegistry;
    private array $config;

    /**
     * Constructor
     *
     * @param FieldRegistryInterface $fieldRegistry
     * @param array $config
     */
    public function __construct(FormFieldRegistryInterface $fieldRegistry, array $config = [])
    {
        $this->fieldRegistry = $fieldRegistry;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields(): array
    {
        // Initialize result array
        $fields = [];

        // Default field list if nothing specified in config
        $fieldList = ['name', 'email', 'address', 'phone'];

        // If fields are specified in config, process each one with its overrides
        if (!empty($this->config['fields'])) {
            foreach ($this->config['fields'] as $fieldName => $overrides) {
                $fields[$fieldName] = $this->getField($fieldName, $overrides);
            }
        } else {
            // Fallback to default fields without overrides
            foreach ($fieldList as $fieldName) {
                $fields[$fieldName] = $this->getField($fieldName);
            }
        }

        return $fields;
    }

    /**
     * Get individual field with overrides applied
     *
     * @param string $fieldName
     * @param array $overrides
     * @return array
     */
    private function getField(string $fieldName, array $overrides = []): array
    {
        $field = $this->fieldRegistry->get($fieldName);

        if (empty($field)) {
            throw new \InvalidArgumentException("Field '$fieldName' not found in registry");
        }

        if (!empty($overrides)) {
            // Deep merge the overrides
            foreach ($overrides as $key => $value) {
                if (is_array($value) && isset($field[$key]) && is_array($field[$key])) {
                    $field[$key] = array_merge($field[$key], $value);
                } else {
                    $field[$key] = $value;
                }
            }
        }

        return $field;
    }
}
