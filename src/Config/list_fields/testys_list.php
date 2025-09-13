<?php

use App\Helpers\DebugRt;

// DebugRt::j('0', '', 'BOOM on Config File');
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
            'minlength' => 2,
            'maxlength' => 10,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'title',
                'placeholder' => 'Enter a testy title'
            ]
        ]
    ],
];
