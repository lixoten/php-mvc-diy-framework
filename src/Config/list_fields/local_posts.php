<?php

return [
    'id' => [
        'label' => 'posts.id---postField', //okkkkkkkkkkkkkkkkkkkk
        'list' => [
            'sortable' => true,
            'formatter' => null,
        ],
    ],
    'titlexx' => [
        'label' => 'posts.title---localTitle-l&f',
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
                'placeholder' => 'Enter a post title'
            ]
        ]
    ],
    'username' => [
        'label' => 'posts.author',
        'list' => [
            'sortable' => true,
            // 'formatter' => fn($value) => htmlspecialchars($value ?? 'Unknown'),
            'formatter' => function ($value) {
                return htmlspecialchars($value ?? 'Unknown');
            },
        ],
    ],
    'status' => [
        'label' => 'posts.status',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                $statusClass = ($value == 'Published') ? 'success' : 'warning';
                return '<span class="badge bg-' . $statusClass . '">' . htmlspecialchars($value) . '</span>';
            },
        ],
    ],
    'created_atxx' => [
        'label' => 'posts.created_at---postField',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                return htmlspecialchars($value ?? '');
            },
            // 'formatter' => fn($value) => htmlspecialchars($value ?? ''),
        ],
    ],
    'content' => [
        'label' => 'posts.content-local',
        'form' => [
            'type' => 'textarea',
            'required' => true,
            'minLength' => 10,
            'maxLength' => 2000,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'content',
                'placeholder' => 'posts.placeholder',//'Enter post content',
                'rows' => '6'
            ]
        ]
    ],
];
