<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

// use Core\Form\Field\FieldInterface; // Not strictly needed in this file, but can be left for consistency

/**
 * Radio button group field type for handling multiple related radio buttons
 */
class RadioGroupType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'radio_group';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            'choices' => [],   // Options for the radio group (value => label translation key)
            'inline'  => false, // Whether to render radios inline
        ]);
    }
}
