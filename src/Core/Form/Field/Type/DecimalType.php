<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Number field type
 */
class DecimalType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'decimal';
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
