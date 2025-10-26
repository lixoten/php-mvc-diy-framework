<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * File field type
 */
class FileType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'file';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            // Add any file-specific default options here if needed
            'required' => false,
            'attributes' => [
                'accept' => '*/*',
            ],
        ]);
    }
}
