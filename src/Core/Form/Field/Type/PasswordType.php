<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Password field type
 */
class PasswordType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'password';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            'attributes' => [
                'type' => 'password',
                'autocomplete' => 'current-password',
            ],
        ]);
    }
}
