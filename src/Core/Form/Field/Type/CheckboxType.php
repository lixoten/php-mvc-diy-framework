<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Checkbox field type
 */
class CheckboxType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'checkbox';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            'checked' => false,
            'value' => '1',
            'required' => false,
            'attributes' => [
                'type' => 'checkbox',
            ],
        ]);
    }
}
