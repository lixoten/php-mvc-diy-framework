<?php

declare(strict_types=1);

return [
    'id' => [
        'label' => 'testys.id---testyField', //ok
        'list' => [
            'sortable' => true,
            'formatter' => null,
        ],
    ],
    'title' => [
        'label' => 'testys.title',
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
            'maxLength' => 30,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'title',
                'placeholder' => 'testys.title.placeholder', //.Enter a testy title'
            ]
        ]
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
                'placeholder' => 'testys.content.placeholder',//'Enter testy content',
                'rows' => '6'
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
];
