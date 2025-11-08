<?php

use App\Enums\UserStatus;

return [
    'id' => [
        'label' => 'user.id---ent', //ok
        'list' => [
            'sortable' => true,
            'formatter' => null,
        ],
    ],
    'title' => [
        'label' => 'user.title---ent',
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
                'placeholder' => 'user.title.placeholder', //.Enter a user title'
                'minlength' => 5,
                'maxlength' => 12,
                'data-char-counter' => 'title-counter',
            ],
            'show_char_counter' => true, // js-feature
        ]
    ],
    'username' => [
        'label' => 'user.author---ent',
        'list' => [
            'sortable' => true,
            // 'formatter' => fn($value) => htmlspecialchars($value ?? 'Unknown'),
            'formatter' => function ($value) {
                return htmlspecialchars($value ?? 'Unknown');
            },
        ],
        'form' => [
            'attributes' => [
                'type' => 'text',
                'placeholder' => 'user.username.placeholder', //.Enter a username'
                'minlength' => 5,
                'maxlength' => 12,
                // 'data-char-counter' => 'title-counter',
            ],
            // 'show_char_counter' => true, // js-feature
        ]
    ],
    'status' => [
        'label' => 'user.status---ent',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                $statusClass = ($value == 'Published') ? 'success' : 'warning';
                return '<span class="badge bg-' . $statusClass . '">' . htmlspecialchars($value) . '</span>';
            },
        ],
        'form' => [
            'type' => 'select',
            // 'placeholder' => '-- Please select --', // Optional: for UI hint, not a real <option>
            // 'default_choice' => '-- Please select --', // Optional: for UI hint, not a real <option>
            'choices' => UserStatus::toSelectArray(),

            // 'choices' => [
            //     'a'  => 'Allen',
            //     'b'  => 'Bob',
            //     'c'  => 'Charlie',
            //     'na' => 'None',
            // ],
            'attributes' => [
                'class' => 'form-select',
                // 'aria-label' => 'Gender',
                'required' => false,
            ],
            'validators' => [
                'in_array' => [
                    'values' => ['a', 'b', 'c', 'na'],
                    'message' => 'Please select a valid gender.',
                ],
            ],
        ],
    ],

    'roles' => [
        'label' => 'user.roles',
        'data_transformer' => 'json_array', // or 'array'

        'list' => [
            'sortable' => false,
            'formatter' => function ($value) {
                // Decode JSON and display as badges
                $roles = is_string($value) ? json_decode($value, true) : $value;
                if (!is_array($roles)) {
                    return '';
                }

                $badges = array_map(function ($role) {
                    return '<span class="badge bg-primary me-1">' . htmlspecialchars($role) . '</span>';
                }, $roles);

                return implode('', $badges);
            },
        ],
        'form' => [
            'type' => 'checkbox_group',

            'choices' => [
                'user' => 'user.roles.user',
                'admin' => 'user.roles.admin',
                'store_owner' => 'user.roles.store_owner',
                'guest' => 'user.roles.guest',
            ],
            'attributes' => [
                'class' => 'form-check-input',
            ],
            'validators' => [
                'checkbox_group' => [
                    'min_choices' => 1,
                    'message' => 'Please select at least one role.',
                ],
            ]
        ],
    ],


    'is_green' => [
        'label' => 'user.is_green',
        'data_transformer' => 'boolean',
        'form' => [
            'type' => 'checkbox',
            'attributes' => [
            ],
        ],
    ],
    'is_blue' => [
        'label' => 'user.is_blue',
        'data_transformer' => 'boolean',
        'form' => [
            'type' => 'checkbox',
            'attributes' => [
            ],
        ]
    ],
    'is_red' => [
        'label' => 'user.is_red',
        'data_transformer' => 'boolean',
        'form' => [
            'type' => 'checkbox',
            'attributes' => [
            ],
        ]
    ],
    'generic_code' => [
        'label' => 'Generic Code',
        'form' => [
            'type' => 'select',
            // 'placeholder' => '-- Please select --', // Optional: for UI hint, not a real <option>
            // 'default_choice' => '-- Please select --', // Optional: for UI hint, not a real <option>
            'choices' => [
                'a'  => 'Allen',
                'b'  => 'Bob',
                'c'  => 'Charlie',
                'na' => 'None',
            ],
            'attributes' => [
                'class' => 'form-select',
                // 'aria-label' => 'Gender',
                'required' => false,
            ],
            'validators' => [
                'in_array' => [
                    'values' => ['a', 'b', 'c', 'na'],
                    'message' => 'Please select a valid gender.',
                ],
            ],
        ],
    ],

    'created_atxx' => [
        'label' => 'user.created_at---ent',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                return htmlspecialchars($value ?? '');
            },
            // 'formatter' => fn($value) => htmlspecialchars($value ?? ''),
        ],
    ],
];
