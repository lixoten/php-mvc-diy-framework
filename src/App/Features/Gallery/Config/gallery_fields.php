<?php

/**
 * Generated File - Date: 20251109_204104
 * Field definitions for the Gallery entity.
 *
 * This file defines how each field should be rendered in forms and lists,
 * including labels, input types, attributes, formatters, and validators.
 */

declare(strict_types=1);

return [
    'store_id' => [
        'label' => 'gallery.store_id',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'number',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'gallery.store_id.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'user_id' => [
        'label' => 'gallery.user_id',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'number',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'gallery.user_id.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'status' => [
        'label' => 'gallery.status',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'gallery.status.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'name' => [
        'label' => 'gallery.name',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'textarea',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'gallery.name.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'slug' => [
        'label' => 'gallery.slug',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'textarea',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'gallery.slug.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'description' => [
        'label' => 'gallery.description',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'textarea',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'gallery.description.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'image_count' => [
        'label' => 'gallery.image_count',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'number',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'gallery.image_count.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'cover_image_id' => [
        'label' => 'gallery.cover_image_id',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'number',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'gallery.cover_image_id.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
];
