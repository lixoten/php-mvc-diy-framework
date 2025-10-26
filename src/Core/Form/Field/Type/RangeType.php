<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Range field type
 */
class RangeType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'range';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            // Add range-specific options if needed
        ]);
    }
}
