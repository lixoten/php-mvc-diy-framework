<?php

return [
    'id' => [
        'label' => 'testy.id---ent', //ok
        'list' => [
            'sortable' => true,
            'formatter' => null,
        ],
    ],
    'title' => [
        'label' => 'testy.title---ent',
        'list' => [
            'sortable' => true,
            'formatter' => fn($value) => htmlspecialchars($value ?? ''),
            // 'formatter' => function ($value) {
                // return htmlspecialchars($value ?? '');
            // },
        ],
        'form' => [
            'attributes' => [
                'type' => 'text',
                'id' => 'title',
                'placeholder' => 'testy.title.placeholder', //.Enter a testy title'
                'minlength' => 5,
                'maxlength' => 12,
                'data-char-counter' => 'title-counter',
            ],
            'show_char_counter' => true, // js-feature
        ]
    ],
    'generic_text' => [
        'label' => 'testy.generic_text---ent',
        'list' => [
            'sortable' => true,
            'formatter' => fn($value) => htmlspecialchars($value ?? ''),
            // 'formatter' => function ($value) {
                // return htmlspecialchars($value ?? '');
            // },
        ],
        'form' => [
            'attributes' => [
                'type' => 'text',
                'id' => 'generic_text',
                'placeholder' => 'testy.generic_text.placeholder', //.Enter a testy title'
                'required' => true,
                'minlength' => 10,
                'maxlength' => 20,
            ]
        ]
    ],
    'username' => [
        'label' => 'testy.author---ent',
        'list' => [
            'sortable' => true,
            // 'formatter' => fn($value) => htmlspecialchars($value ?? 'Unknown'),
            'formatter' => function ($value) {
                return htmlspecialchars($value ?? 'Unknown');
            },
        ],
    ],
    'status' => [
        'label' => 'testy.status---ent',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                $statusClass = ($value == 'Published') ? 'success' : 'warning';
                return '<span class="badge bg-' . $statusClass . '">' . htmlspecialchars($value) . '</span>';
            },
        ],
    ],
    'created_atxx' => [
        'label' => 'testy.created_at---ent',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                return htmlspecialchars($value ?? '');
            },
            // 'formatter' => fn($value) => htmlspecialchars($value ?? ''),
        ],
    ],
    'content' => [
        'label' => 'testy.content-ent',
        'form' => [
            'attributes' => [
                'type' => 'textarea',
                'id' => 'content',
                'placeholder' => 'testy.content.placeholder',//'Enter testy content',
                'required' => true,
                'minlength' => 10,
                'maxlength' => 2000,
                'rows' => '6'
            ]
        ],
        'list' => [
            'sortable' => true,
            'formatter' => fn($value) => htmlspecialchars($value ?? ''),
            // 'formatter' => function ($value) {
                // return htmlspecialchars($value ?? '');
            // },
        ],
    ],
];
