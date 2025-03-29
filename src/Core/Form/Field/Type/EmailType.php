<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Email field type
 */
class EmailType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'email';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            'attributes' => [
                'type' => 'email',
            ],
        ]);
    }
}
