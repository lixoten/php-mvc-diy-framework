<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Text field type
 */
class TextType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            'minLength' => null,
            'maxLength' => null,
        ]);
    }
}
