<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Time field type
 */
class TimeType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'time';
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
}
