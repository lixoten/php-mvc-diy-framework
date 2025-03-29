<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

use Core\Form\Field\FieldInterface;

/**
 * Registry for field types
 */
class FieldTypeRegistry
{
    /**
     * @var FieldTypeInterface[]
     */
    private array $types = [];

    /**
     * Constructor
     *
     * @param FieldTypeInterface[] $defaultTypes Array of default field types
     */
    public function __construct(array $defaultTypes = [])
    {
        foreach ($defaultTypes as $type) {
            $this->register($type);
        }
    }

    /**
     * Register a field type
     *
     * @param FieldTypeInterface $type
     * @return self
     */
    public function register(FieldTypeInterface $type): self
    {
        $this->types[$type->getName()] = $type;
        return $this;
    }

    /**
     * Get a field type by name
     *
     * @param string $name
     * @return FieldTypeInterface
     * @throws \InvalidArgumentException If type not found
     */
    public function get(string $name): FieldTypeInterface
    {
        if (!isset($this->types[$name])) {
            throw new \InvalidArgumentException("Field type '$name' not found");
        }

        return $this->types[$name];
    }

    /**
     * Check if a field type exists
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->types[$name]);
    }

    /**
     * Create a field using a specific field type
     *
     * @param string $name Field name
     * @param string $type Field type name
     * @param array $options Field options
     * @return FieldInterface
     */
    public function createField(string $name, string $type, array $options = []): FieldInterface
    {
        return $this->get($type)->buildField($name, $options);
    }
}
