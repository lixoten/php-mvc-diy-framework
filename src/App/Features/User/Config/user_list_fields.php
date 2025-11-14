<?php

/**
 * Generated File - Date: 20251111_222717
 * List-Field definitions for the User entity.
 *
 * This file defines how each field should be rendered in lists,
 * including labels, input types, attributes, formatters, and validators.
 */

declare(strict_types=1);

return [
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
];
