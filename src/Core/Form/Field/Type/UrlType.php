<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Url field type
 */
class UrlType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'url';
    }
}
