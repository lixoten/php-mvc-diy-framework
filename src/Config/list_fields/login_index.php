<?php

declare(strict_types=1);

return [
    'id' => [
        'label' => 'Testname2',
        'form' => [
            'type' => 'text',
            'required' => true,
            'minLength' => 3,
            'maxLength' => 50,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'testname2',
                'placeholder' => 'testname2 boo',
                'autofocus' => false
            ],
            'validators' => []
        ],
    ],
    'password' => [
        'label' => 'password---local',
        'form' => [
            'attributes' => [
                'placeholder' => 'Enter your password22'
            ],
            'maxLength' => null,  // Remove maxLength restriction
            // Remove password complexity requirements for login
            'validators' => []
        ],
    ],
    'remember' => [
        'label' => 'Remember me123',
        'form' => [
            'type' => 'checkbox',
            'required' => false,
            'value' => false, // set default to false (unchecked)
            'attributes' => [
                'class' => 'form-check-input',
                'id' => 'remember'
            ]
        ],
    ],
    'usernamexxxx' => [
        'label' => 'posts.author',
        'list' => [
            'sortable' => true,
            // 'formatter' => fn($value) => htmlspecialchars($value ?? 'Unknown'),
            'formatter' => function ($value) {
                return htmlspecialchars($value ?? 'Unknown');
            },
        ],
    ],
];
