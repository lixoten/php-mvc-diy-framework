<?php

declare(strict_types=1);

return [
    'render_options' => [
        'from' => 'testy_view-config',
        'title' => 'testy.view.title',
        'show_actions' => true,
        'layout_type' => 'fieldsets', // or 'sequential'
    ],
    'view_layout' => [
        [
            'title' => 'Basic Information',
            'fields' => [
                'id',
                'generic_text',
                'primary_email',
            ],
        ],
        [
            'title' => 'Metadata',
            'fields' => [
                'created_at',
                'updated_at',
            ],
        ],
    ],
];