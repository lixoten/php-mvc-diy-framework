<?php

declare(strict_types=1);

namespace Core\Form;

/**
 * Abstract base class for form types
 * Implements common functionality while allowing specific implementations in child classes
 */
abstract class AbstractFormType implements FormTypeInterface
{
    /**
     * Form options
     */
    protected array $options = [];

    /**
     * Get the form name
     * Child classes should override this to provide a unique name
     */
    abstract public function getName(): string;

    /**
     * Get form options
     */
    public function getOptions(): array
    {
        return array_merge($this->getDefaultOptions(), $this->options);
    }

    /**
     * Set form options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * Get default form options
     * Child classes can override to provide different defaults
     */
    protected function getDefaultOptions(): array
    {
        return [
            'method' => 'POST',
            'attr' => [
                'class' => 'form'
            ]
        ];
    }

    /**
     * Get default data for the form
     * Child classes can override to provide default values
     */
    public function getDefaultData(): array
    {
        return [];
    }

    /**
     * Build the form with fields
     * Must be implemented by child classes
     */
    abstract public function buildForm(FormBuilderInterface $builder, array $options = []): void;

    /**
     * Helper method to add multiple fields from a field registry
     * Makes form building more concise in child classes
     */
    protected function addFieldsFromRegistry(
        FormBuilderInterface $builder,
        array $fieldNames,
        FieldRegistryInterface $registry
    ): void {
        foreach ($fieldNames as $name) {
            // Handle special case for field names with underscores
            $registryName = str_contains($name, '_') ?
                $this->convertToMethodName($name) : $name;

            $fieldDef = $registry->get($registryName) ?? [];
            $builder->add($name, $fieldDef);
        }
    }

    /**
     * Convert underscore field name to camelCase for method name
     */
    protected function convertToMethodName(string $fieldName): string
    {
        return lcfirst(str_replace('_', '', ucwords($fieldName, '_')));
    }

    /**
     * Set sequential layout for fields
     */
    protected function setSequentialLayout(FormBuilderInterface $builder, array $fieldNames): void
    {
        $builder->setLayout([
            'sequential' => [
                'fields' => $fieldNames
            ]
        ]);
    }
}
