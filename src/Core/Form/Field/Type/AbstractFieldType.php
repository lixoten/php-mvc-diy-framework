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
        $resolvedOptions    = array_merge($this->getDefaultOptions(), $options);

        $customAttributes   = $options['attributes'] ?? [];

        $defaultAttributes  = $this->getDefaultAttributes();

        $resolvedAttributes = array_merge($defaultAttributes, $customAttributes);

        // Set type if not explicitly provided
        if (!isset($resolvedAttributes['type'])) {
            $resolvedAttributes['type'] = $this->getName();
        }

        // Create and return field
        $newField = new Field($name, $resolvedOptions, $resolvedAttributes);
        return $newField;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return [
            'label' => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultAttributes(): array
    {
        return [
            'id'            => null,
            'name'          => null,
            'class'         => null,
            'style'         => null,
            'readonly'      => false,
            'required'      => false,
            'autocomplete'  => false,
            'autofocus'     => false,
            'disabled'      => false,
            'tabindex'      => null,
            // 'form' => null,
        ];
    }

}
