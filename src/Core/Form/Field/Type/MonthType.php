<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Month field type
 */
class MonthType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'month';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            // Add any specific default options here if needed
        ]);
    }
}
