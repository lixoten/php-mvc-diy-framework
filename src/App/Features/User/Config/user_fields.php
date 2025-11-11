<?php

/**
 * Generated File - Date: 20251109_203449
 * Field definitions for the Testy entity.
 *
 * This file defines how each field should be rendered in forms and lists,
 * including labels, input types, attributes, formatters, and validators.
 */

declare(strict_types=1);

    // private int $id = 0;
    // private string $username = '';
    // private string $email = '';
    // private string $password_hash = '';
    // private array $roles = [];
    // private UserStatus $status = UserStatus::PENDING;
    // private ?string $activation_token = null;
    // private ?string $reset_token = null;
    // private ?string $reset_token_expiry = null;
    // private bool $is_green = false;
    // private bool $is_blue = false;
    // private bool $is_red = false;
    // private string $generic_code = '';

return [
    'generic_text' => [
        'label' => 'user.generic_text',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'user.generic_text.placeholder',
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
        'label' => 'testy.image_count',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'number',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'user.image_count.placeholder',
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
        'label' => 'user.cover_image_id',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'number',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'user.cover_image_id.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'is_green' => [
        'label' => 'user.is_green',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'checkbox',
            'required'      => true,
            'attributes'    => [
                'placeholder' => 'user.is_green.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'is_blue' => [
        'label' => 'user.is_blue',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'checkbox',
            'required'      => true,
            'attributes'    => [
                'placeholder' => 'user.is_blue.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'is_red' => [
        'label' => 'user.is_red',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'checkbox',
            'required'      => true,
            'attributes'    => [
                'placeholder' => 'user.is_red.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'email' => [
        'label' => 'user.email',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'email',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'user.email.placeholder',
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
