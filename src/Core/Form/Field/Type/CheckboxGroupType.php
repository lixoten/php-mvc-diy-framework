<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

use Core\Form\Field\FieldInterface;

/**
 * Checkbox group field type for handling multiple related checkboxes
 */
class CheckboxGroupType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'checkbox_group';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            'choices' => [],
            'inline' => false,
        ]);
    }
}
