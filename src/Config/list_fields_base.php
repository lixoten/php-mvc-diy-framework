<?php

return [
    'id' => [
        'label' => 'acrID',
        'list' => [
            'sortable' => true,
            'formatter' => null,
        ],
    ],
    'user_id' => [
        'label' => 'acrUserId',
        'list' => [
            'sortable' => true,
            'formatter' => null,
        ],
    ],
    'title' => [
        'label' => 'acrTitle',
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
                'placeholder' => 'Enter a post titlezzzz'
            ]
        ]
    ],
    'name' => [
        'label' => 'acrName',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                return htmlspecialchars((string)$value ?? '');
            },
        ],
    ],
    'status' => [
        'label' => 'acrStatus',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                if ($value === null || $value === '') {
                    return '';
                }
                $statusClass = ($value == 'Published' || $value == 'Active' || $value === true || $value === 1) ? 'success' : 'warning';
                return '<span class="badge bg-' . $statusClass . '">' . htmlspecialchars((string)$value) . '</span>';
            },
        ],
    ],
    'created_at' => [
        'label' => 'posts.created_at---BaseField',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                if ($value instanceof \DateTimeInterface) {
                    return $value->format('Y-m-d H:i:s');
                }
                if (is_string($value) && strtotime($value) !== false) {
                    $dateTime = new \DateTime($value);
                    return $dateTime->format('Y-m-d H:i:s');
                }
                return htmlspecialchars((string)$value);
            },
        ],
    ],
    'updated_at' => [
        'label' => 'acrUpdated At',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                if ($value instanceof \DateTimeInterface) {
                    return $value->format('Y-m-d H:i:s');
                }
                return htmlspecialchars((string)$value);
            },
        ],
    ],
    'testname1' => [
        'type' => 'text',
        'label' => 'Testname1',
        'required' => true,
        'minLength' => 3,
        'maxLength' => 50,
        'attributes' => [
            'class' => 'form-control',
            'id' => 'testname1',
            'placeholder' => 'Testname1 boo',
            'autofocus' => false
        ],
        'validators' => []
    ],
    'username' => [
        'label' => 'Username.username222xxxxx',
        // 'label' => 'Username or Emailxxx',
        'form' => [
            'type' => 'text',
            'attributes' => [
                'placeholder' => 'Enter your username or emailxxx',
            ],
            'minLength' => null,  // Remove minLength restriction
            'maxLength' => null,  // Remove maxLength restriction
            // Remove registration-specific validators
            'validators' => []
        ]
    ],
    'usernamexxx' => [
        'label' => 'Username.username',
        'list' => [
            'sortable' => true,
            'formatter' => null,
        ],
        'form' => [
            'type' => 'text',
            'required' => true,
            'minLength' => 3,
            'maxLength' => 50,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'username',
                'placeholder' => 'Choose a unique username',
                'autofocus' => true
            ],
            'validators' => [
                'unique_username' => [
                    'message' => 'This username is already taken.'
                ]
            ],
        ]
    ],
    'password' => [
        'label' => 'Password',
        'form' => [
            'type' => 'password',
            'required' => true,
            'minLength' => 4,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'password',
                'placeholder' => 'Choose a strong password'
            ],
            'validators' => [
                'regex' => [
                    'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{4,}$/',
                    'message' => 'Password must include at least one uppercase letter, one lowercase letter, one number, and one special character...'
                ]
            ]
        ]
    ],
    'captcha' => [
        'label' => 'xxxSecurity Verification',
        'form' => [
            'type' => 'captcha',
            'required' => true,
            'help_text' => 'Please complete the security check',
            'attributes' => [
                'class' => 'g-recaptcha'
            ],
            'options' => [
                'theme' => 'light',
                'size' => 'normal'
            ],
            'validators' => [
                'captcha' => [
                    'message' => 'xxxFailed security verification. Please try again.'
                ]
            ]
        ]
    ],
];
];
