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
];
