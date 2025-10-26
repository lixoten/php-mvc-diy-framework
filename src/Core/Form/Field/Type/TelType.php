<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Telephone field type
 */
class TelType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'tel';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            // Add tel-specific options if needed
            //'inputmode' => 'tel',
        ]);
    }

    // /**
    //  * {@inheritdoc}
    //  */
    // public function getDefaultAttributes(): array
    // {
    //     return array_merge(parent::getDefaultAttributes(), [
    //         // Add tel-specific options if needed
    //         'inputmode' => 'tel',
    //     ]);
    // }
}
