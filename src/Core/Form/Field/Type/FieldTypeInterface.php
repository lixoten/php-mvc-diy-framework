<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

use Core\Form\Field\FieldInterface;

/**
 * Interface for field types
 */
interface FieldTypeInterface
{
    /**
     * Build a field instance
     *
     * @param string $name Field name
     * @param array $options Field options
     * @return FieldInterface
     */
    public function buildField(string $name, array $options = []): FieldInterface;

    /**
     * Get field type name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get default options for this field type
     *
     * @return array
     */
    public function getDefaultOptions(): array;
}
