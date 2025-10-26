<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Week field type
 */
class WeekType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'week';
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
