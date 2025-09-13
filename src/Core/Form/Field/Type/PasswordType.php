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
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultAttributes(): array
    {
        return array_merge(parent::getDefaultAttributes(), [
            'type'      => 'password',
            'autocomplete' => 'current-password',
            'minlength' => null,
            'maxlength' => null,
        ]);
    }
}
