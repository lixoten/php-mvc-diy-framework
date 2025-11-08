<?php

use App\Helpers\DebugRt;

// DebugRt::j('0', '', 'BOOM on Config File');
return [
    'id' => [
        'label' => 'base.acrID',
        'list' => [
            'sortable' => true,
            'formatter' => null,
        ],
    ],
    'user_id' => [
        'label' => 'base.acrUserId',
        'list' => [
            'sortable' => true,
            'formatter' => null,
        ],
    ],
    'title' => [
        'label' => 'base.acrTitle',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                return htmlspecialchars((string)$value ?? '');
            },
        ],
        'form' => [
            'attributes' => [
                'type' => 'text',
                'id' => 'title',
                'placeholder' => 'testy.title.placeholder', //.Enter a testy title'
                'minlength' => 5,
                'maxlength' => 12,
                'data-char-counter' => 'title-counter',
            ],
            'show_char_counter' => true, // js-feature
        ]
    ],
    'name' => [
        'label' => 'base.acrName',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                return htmlspecialchars((string)$value ?? '');
            },
        ],
    ],
    'status' => [
        'label' => 'base.acrStatus',
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
        'label' => 'base.created_at',
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
        'label' => 'base.acrUpdated At',
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
        'label' => 'base.Testname1',
        'required' => true,
        'minlength' => 3,
        'maxlength' => 50,
        'attributes' => [
            'class' => 'form-control',
            'id' => 'testname1',
            'placeholder' => 'Testname1 boo',
            'autofocus' => false
        ],
        'validators' => []
    ],
    'username' => [
        'label' => 'base.Username.username222',
        // 'label' => 'Username or Emailxxx',
        'form' => [
            'type' => 'text',
            'attributes' => [
                'placeholder' => 'Enter your username or emailxxx',
            ],
            'minlength' => null,  // Remove minlength restriction
            'maxlength' => null,  // Remove maxlength restriction
            // Remove registration-specific validators
            'validators' => []
        ]
    ],
    'usernamexxx' => [
        'label' => 'base.Username.username',
        'list' => [
            'sortable' => true,
            'formatter' => null,
        ],
        'form' => [
            'type' => 'text',
            'required' => true,
            'minlength' => 3,
            'maxlength' => 50,
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
        'label' => 'base.Password',
        'form' => [
            'type' => 'password',
            'required' => true,
            'minlength' => 4,
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
        'label' => 'base.Security Verification',
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