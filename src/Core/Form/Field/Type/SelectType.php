<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Select (dropdown) field type
 */
class SelectType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'select';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            // 'default_choice' => 'Please Select one',
            // 'choices' => [],
        ]); 
    }

    // /**
    //  * {@inheritdoc}
    //  */
    // public function getDefaultAttributes(): array
    // {
    //     return array_merge(parent::getDefaultAttributes(), [
    //         'type'      => 'select',
    //         'required'  => false,
    //         'class'     => 'form-select',
    //     ]);
    // }
}
