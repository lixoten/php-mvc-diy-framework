<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Email field type
 */
class ColorType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'color';
    }
}
