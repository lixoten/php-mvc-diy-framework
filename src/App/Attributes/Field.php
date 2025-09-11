<?php

declare(strict_types=1);

namespace App\Attributes;

/**
 * Attribute for entity field metadata.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Field
{
    public function __construct(
        public string $type,
        public bool $nullable = false,
        public ?string $label = null,
        public ?string $name = null,
        public bool $primary = false,
        public ?array $enum = null,
    ) {}
}



        // // Reflect the class and property
        // $reflection = new \ReflectionClass(\App\Entities\Post::class);
        // $property = $reflection->getProperty('createdAt');

        // // Get the first Field attribute instance (if any)
        // // $attributes = $property->getAttributes(Field::class);
        // $attributes = $property->getAttributes(\App\Attributes\Field::class);

        // // $fieldMeta = $attributes[0]->newInstance() ?? null;
        // $fieldMeta = isset($attributes[0]) ? $attributes[0]->newInstance() : null;

        // // Now $fieldMeta is an instance of Field, or null if not present
        // if ($fieldMeta) {
        //     echo "Field name: " . $property->getName() . PHP_EOL;
        //     echo "Type: " . $fieldMeta->type . PHP_EOL;
        //     echo "Nullable: " . ($fieldMeta->nullable ? 'yes' : 'no') . PHP_EOL;
        //     echo "Label: " . $fieldMeta->label . PHP_EOL;
        //     echo "Name: " . $fieldMeta->name . PHP_EOL;
        //     echo "Primary: " . ($fieldMeta->primary ? 'yes' : 'no') . PHP_EOL;
        //     // ...and so on for other metadata
        // } else {
        //     echo "No Field attribute found for property 'postId'";
        // }
        // //DebugRt::j('1', '', '111');

        