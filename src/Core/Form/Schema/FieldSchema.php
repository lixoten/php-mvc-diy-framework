<?php

declare(strict_types=1);

namespace Core\Form\Schema;

class FieldSchema
{
    private array $schemas;

    public function __construct(array $schemas)
    {
        $this->schemas = $schemas;
    }

    public function get(string $fieldType): array
    {
        $global = $this->schemas['global'] ?? [];
        $typeSpecific = $this->schemas[$fieldType] ?? [];

        return array_merge($global, $typeSpecific);
    }
}
