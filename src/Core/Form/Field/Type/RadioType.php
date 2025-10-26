<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Radio button field type
 */
class RadioType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'radio';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
        ]);
    }

    // /**
    //  * {@inheritdoc}
    //  */
    // public function getDefaultAttributes(): array
    // {
    //     return array_merge(parent::getDefaultAttributes(), [
    //         'type'      => 'checkbox',
    //         'checked'   => false,
    //         'value'     => '1',
    //     ]);
    // }
}
