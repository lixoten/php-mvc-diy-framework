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
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultAttributes(): array
    {
        return array_merge(parent::getDefaultAttributes(), [
            'type'      => 'text',
            'minlength' => null,
            'maxlength' => null,
            'style' => 'background: yellow;',
        ]);
    }
}
