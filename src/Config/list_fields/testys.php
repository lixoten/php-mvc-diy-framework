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
            'type' => 'text',
            'required' => true,
            'minLength' => 2,
            'maxLength' => 10,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'title',
                'placeholder' => 'Enter a testy title'
            ]
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
            'type' => 'text',
            'required' => true,
            'minLength' => 10,
            'maxLength' => 20,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'favorite_word',
                'placeholder' => 'testys.favorite_word.placeholder', //.Enter a testy title'
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
            'type' => 'textarea',
            'required' => true,
            'minLength' => 10,
            'maxLength' => 2000,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'content',
                'placeholder' => 'testys.placeholder',//'Enter testy content',
                'rows' => '6'
            ]
        ]
    ],
];
