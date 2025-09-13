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
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultAttributes(): array
    {
        return array_merge(parent::getDefaultAttributes(), [
            'type'          => 'textarea',
            'rows'          => 5,
            'maxlength '    => null,
            'minlength'     => null,
            'placeholder'   => null,
            'wrap'          => null,
            'spellcheck'    => false,
        ]);
    }


    /**
     * {@inheritdoc}
     */
    public function createField(string $name, array $options = [], $attributes = []): FieldInterface
    {
        // Extract and merge options with defaults
        $options = array_merge($this->getDefaultOptions(), $options);
        // Extract field options
        $label = $options['label'] ?? null;

        // Merge with default attributes
        $attributes = array_merge($this->getDefaultAttributes(), $attributes);



        $required = $attributes['required'] ?? false;

        // Create field
        $field = new Field($name, $label);
        $field->setType('textarea'); // This is critical!
        $field->setRequired($required);
        $field->setAttributes($attributes);
        $field->setOptions($options);

        return $field;
    }
}
