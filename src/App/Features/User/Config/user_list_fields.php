<?php

declare(strict_types=1);

use App\Helpers\DebugRt;

return [
    'id' => [
        'label' => 'user.id',
        'form' => [
            'attributes' => [
                'type' => 'hidden',
            ],
        ],
        'list' => [
            'sortable' => true,
            'formatter' => null,
        ]
    ],
    'username' => [
        'label' => 'user.username',
        'list' => [
            'sortable' => true,
            // 'formatter' => fn($value) => htmlspecialchars($value ?? 'Unknown'),
            'formatter' => function ($value) {
                return htmlspecialchars($value ?? 'Unknown');
            },
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


];
