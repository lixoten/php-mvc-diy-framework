<?php

return [
    'id' => [
        'label' => 'testys.id---testyField', //okkkkkkkkkkkkkkkkkkkk
        'list' => [
            'sortable' => true,
            'formatter' => null,
        ],
    ],
    'title' => [
        'label' => 'testys.title---localTitle-l&f',
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
                'placeholder' => 'testys.title.placeholder', //.Enter a testy title'
                'minlength' => 5,
                'maxlength' => 12,
                'data-char-counter' => 'title-counter',
            ],
            'show_char_counter' => true, // js-feature
        ]
    ],
    'favorite_word' => [
        'label' => 'testys.favorite_word',
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
                'id' => 'favorite_word',
                'placeholder' => 'testys.favorite_word.placeholder', //.Enter a testy title'
                'required' => true,
                'minlength' => 10,
                'maxlength' => 20,
            ]
        ]
    ],
    'username' => [
        'label' => 'testys.author',
        'list' => [
            'sortable' => true,
            // 'formatter' => fn($value) => htmlspecialchars($value ?? 'Unknown'),
            'formatter' => function ($value) {
                return htmlspecialchars($value ?? 'Unknown');
            },
        ],
    ],
    'status' => [
        'label' => 'testys.status',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                $statusClass = ($value == 'Published') ? 'success' : 'warning';
                return '<span class="badge bg-' . $statusClass . '">' . htmlspecialchars($value) . '</span>';
            },
        ],
    ],
    'created_atxx' => [
        'label' => 'testys.created_at---testyField',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                return htmlspecialchars($value ?? '');
            },
            // 'formatter' => fn($value) => htmlspecialchars($value ?? ''),
        ],
    ],
    'content' => [
        'label' => 'testys.content-local',
        'form' => [
            'attributes' => [
                'type' => 'textarea',
                'id' => 'content',
                'placeholder' => 'testys.content.placeholder',//'Enter testy content',
                'required' => true,
                'minlength' => 10,
                'maxlength' => 2000,
                'rows' => '6'
            ]
        ]
    ],
];
