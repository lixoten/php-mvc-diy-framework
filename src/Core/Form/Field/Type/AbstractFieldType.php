<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

use Core\Form\Field\Field;
use Core\Form\Field\FieldInterface;

/**
 * Abstract base field type
 */
abstract class AbstractFieldType implements FieldTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildField(string $name, array $options = []): FieldInterface
    {
        // Merge default options with provided options
        $resolvedOptions = array_merge($this->getDefaultOptions(), $options);

        // Set type if not explicitly provided
        if (!isset($resolvedOptions['type'])) {
            $resolvedOptions['type'] = $this->getName();
        }

        // Create and return field
        return new Field($name, $resolvedOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return [
            'required' => false,
            'label' => null,
            'attributes' => [],
        ];
    }
}
