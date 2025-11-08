<?php

declare(strict_types=1);

use App\Helpers\DebugRt;

return [
    'id' => [
        'label' => 'testy.id---local',
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
];
