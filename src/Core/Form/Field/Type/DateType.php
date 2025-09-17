<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Date field type
 */
class DateType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'date';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            // Add any date-specific default options here if needed
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultAttributes(): array
    {
        return array_merge(parent::getDefaultAttributes(), [
            'type'      => 'date',
            'min'       => null,
            'max'       => null,
            'required'  => false,
            'class'     => 'form-control',
        ]);
    }
}
