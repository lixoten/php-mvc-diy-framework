<?php

return [
    'title' => [
        'label' => 'post.title-xxtable',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                return htmlspecialchars((string)$value ?? '');
            },
        ],
        'form' => [
            'type' => 'text',
            'required' => true,
            'minLength' => 2,
            'maxLength' => 10,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'title',
                'placeholder' => 'Enter a post xxtitlezzzz'
            ]
        ]
    ],
    'test1' => [
        'label' => 'post.title-xxtest1',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                return htmlspecialchars((string)$value ?? '');
            },
        ],
        'form' => [
            'type' => 'xxtest1',
            'required' => true,
            'minLength' => 2,
            'maxLength' => 10,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'title',
                'placeholder' => 'Enter a post xxtest1'
            ]
        ]
    ],
    'test2' => [
        'label' => 'post.title-xxtest2',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                return htmlspecialchars((string)$value ?? '');
            },
        ],
        'form' => [
            'type' => 'xxtest2',
            'required' => true,
            'minLength' => 2,
            'maxLength' => 10,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'title',
                'placeholder' => 'Enter a post xxtest2'
            ]
        ]
    ],
];
