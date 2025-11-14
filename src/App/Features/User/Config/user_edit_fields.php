<?php

/**
 * Generated File - Date: 20251111_224321
 * Edit-Field definitions for the User entity.
 *
 * This file defines how each field should be rendered in forms,
 * including labels, input types, attributes, formatters, and validators.
 */

declare(strict_types=1);

return [
    'email' => [
        'label' => 'user.email',
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
