<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Datetime field type
 */
class DatetimeType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'datetime';
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
