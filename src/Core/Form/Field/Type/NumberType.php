<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Number field type
 */
class NumberType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'number';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            // Add any number-specific default options here if needed
        ]);
    }
}
