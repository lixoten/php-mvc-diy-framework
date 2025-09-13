<?php

declare(strict_types=1);

return [
    'id' => [
        'label' => 'posts.id---postField', //ok
        'list' => [
            'sortable' => true,
            'formatter' => null,
        ],
    ],
    'title' => [
        'label' => 'posts.title',
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
            'minlength' => 10,
            'maxlength' => 30,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'title',
                'placeholder' => 'posts.title.placeholder', //.Enter a post title'
            ]
        ]
    ],
    'content' => [
        'label' => 'posts.content-local',
        'form' => [
            'type' => 'textarea',
            'required' => true,
            'minlength' => 10,
            'maxlength' => 2000,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'content',
                'placeholder' => 'posts.content.placeholder',//'Enter post content',
                'rows' => '6'
            ]
        ]
    ],
];
