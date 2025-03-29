<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

use Core\Form\Field\Field;
use Core\Form\Field\FieldInterface;

/**
 * Textarea field type
 */
class TextareaType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'textarea';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            'attributes' => [
                'rows' => 5,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function createField(string $name, array $options = []): FieldInterface
    {
        // Extract and merge options with defaults
        $options = array_merge($this->getDefaultOptions(), $options);

        // Extract field options
        $label = $options['label'] ?? null;
        $required = $options['required'] ?? false;
        $attributes = $options['attributes'] ?? [];

        // Create field
        $field = new Field($name, $label);
        $field->setType('textarea'); // This is critical!
        $field->setRequired($required);
        $field->setAttributes($attributes);
        $field->setOptions($options);

        return $field;
    }
}
