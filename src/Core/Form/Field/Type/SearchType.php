<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

/**
 * Search field type
 */
class SearchType extends AbstractFieldType
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'search';
    }
}
