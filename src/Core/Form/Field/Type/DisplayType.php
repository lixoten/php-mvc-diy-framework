<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Range field type
 */
class DisplayType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'display';
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
