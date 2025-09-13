<?php

return [
    'title' => [
        'label' => 'post.title-table',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                return htmlspecialchars((string)$value ?? '');
            },
        ],
        'form' => [
            'type' => 'text',
            'required' => true,
            'minlength' => 2,
            'maxlength' => 10,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'title',
                'placeholder' => 'Enter a post titlezzzz'
            ]
        ]
    ],
    'test1' => [
        'label' => 'post.title-test1',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                return htmlspecialchars((string)$value ?? '');
            },
        ],
        'form' => [
            'type' => 'test1',
            'required' => true,
            'minlength' => 2,
            'maxlength' => 10,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'title',
                'placeholder' => 'Enter a post test1'
            ]
        ]
    ],
    'test2' => [
        'label' => 'post.title-test2',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                return htmlspecialchars((string)$value ?? '');
            },
        ],
        'form' => [
            'type' => 'test2',
            'required' => true,
            'minlength' => 2,
            'maxlength' => 10,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'title',
                'placeholder' => 'Enter a post test2'
            ]
        ]
    ],
];
