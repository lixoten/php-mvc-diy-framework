<?php

/**
 * Generated File - Date: 20251111_222216
 * Field definitions for the User entity.
 *
 * This file defines how each field should be rendered in forms and lists,
 * including labels, input types, attributes, formatters, and validators.
 */

declare(strict_types=1);

return [
    'username' => [
        'label' => 'user.username',
        'list' => [
            'sortable' => true,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'user.username.placeholder',
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
            'required'      => true, // Required if not nullable
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
    'password_hash' => [
        'label' => 'user.password_hash',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'password',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'user.password_hash.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'roles' => [
        'label' => 'user.roles',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'user.roles.placeholder',
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
        'label' => 'user.status',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'user.status.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'activation_token' => [
        'label' => 'user.activation_token',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'user.activation_token.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'reset_token' => [
        'label' => 'user.reset_token',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'user.reset_token.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'reset_token_expiry' => [
        'label' => 'user.reset_token_expiry',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'user.reset_token_expiry.placeholder',
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
            'required'      => true, // Required if not nullable
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
            'required'      => true, // Required if not nullable
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
            'required'      => true, // Required if not nullable
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
    'generic_code' => [
        'label' => 'user.generic_code',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'user.generic_code.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'created_at' => [
        'label' => 'user.created_at',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'datetime-local',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'user.created_at.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'updated_at' => [
        'label' => 'user.updated_at',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'datetime-local',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'user.updated_at.placeholder',
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
