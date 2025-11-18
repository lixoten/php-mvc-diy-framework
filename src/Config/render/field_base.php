<?php

use App\Helpers\DebugRt;

// DebugRt::j('0', '', 'BOOM on Config File');
return [
    'id' => [
        'list' => [
            'label' => 'common.id.list.label',
            'sortable' => true,
            'formatter' => null,
        ],
    ],
    'store_id' => [
        'list' => [
            'label' => 'store_id',
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'label' => 'store_id',
            'type'          => 'number',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'store_id.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'user_id' => [
        'list' => [
            'label' => 'user_id',
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'label' => 'user_id',
            'type'          => 'number',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'user_id.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'title' => [
        'list' => [
            'label' => 'title',
            'sortable' => false,
        ],
        'form' => [
            'label' => 'title',
            'type'          => 'text',
            'required'      => true, // Required if not nullable
        ],
        'formatters' => [
            'text' => [
                'max_length' => 11,
                'truncate_suffix' => '...',
                'transform' => 'lowercase',
            ],
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'titleXxx' => [
        'list' => [
            'label' => 'acrTitle',
            'sortable' => true,
            'formatter' => function ($value) {
                return htmlspecialchars((string)$value ?? '');
            },
        ],
        'form' => [
            'label' => 'acrTitle',
            'attributes' => [
                'type' => 'text',
                'id' => 'title',
                'placeholder' => 'title.placeholder', //.Enter a tescccty title'
                'minlength' => 5,
                'maxlength' => 12,
                'data-char-counter' => 'title-counter',
            ],
            'show_char_counter' => true, // js-feature
        ]
    ],
    'name' => [
        'list' => [
            'label' => 'name',
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'label' => 'name',
            'type'          => 'textarea',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'name.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'namexxx' => [
        'list' => [
            'label' => 'acrName',
            'sortable' => true,
            'formatter' => function ($value) {
                return htmlspecialchars((string)$value ?? '');
            },
        ],
    ],

    'description' => [
        'list' => [
            'label' => 'description',
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'label' => 'description',
            'type'          => 'textarea',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'description.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'content' => [
        'list' => [
            'label' => 'content',
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'label' => 'content',
            'type'          => 'textarea',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'content.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'status' => [
        'list' => [
            'label' => 'status',
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'label' => 'status',
            'type'          => 'text',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'status.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    // 'statusxxx' => [
    //     'label' => 'acrStatus',
    //     'list' => [
    //         'sortable' => true,
    //         'formatter' => function ($value) {
    //             if ($value === null || $value === '') {
    //                 return '';
    //             }
    //             $statusClass = ($value == 'Published' || $value == 'Active' || $value === true || $value === 1) ? 'success' : 'warning';
    //             return '<span class="badge bg-' . $statusClass . '">' . htmlspecialchars((string)$value) . '</span>';
    //         },
    //     ],
    // ],
    'created_at' => [
        'list' => [
            'label' => 'created_at',
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
        'list' => [
            'label' => 'acrUpdated At',
            'sortable' => true,
            'formatter' => function ($value) {
                if ($value instanceof \DateTimeInterface) {
                    return $value->format('Y-m-d H:i:s');
                }
                return htmlspecialchars((string)$value);
            },
        ],
    ],
    'username' => [
        // 'label' => 'Username or Emailxxx',
        'form' => [
            'label' => 'Username.username222',
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
        'list' => [
            'label' => 'Username.username',
            'sortable' => true,
            'formatter' => null,
        ],
        'form' => [
            'label' => 'Username.username',
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
        'form' => [
            'label' => 'Password',
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
        'form' => [
            'label' => 'Security Verification',
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


    'generic_text' => [ // gen
        'list' => [
            'label'      => 'common.generic_text.list.label',
            'sortable'   => false,
        ],
        'form' => [
            'label'      => 'common.generic_text.form.label',
            'type'       => 'text',
            'attributes' => [
                'placeholder' => 'common.generic_text.form.placeholder',
                'required'    => true,
                'minlength'   => 5,
                'maxlength'   => 50,
                'pattern'     => '[a-z0-9]/',
                // 'style'       => 'background:yellow;',
                // 'data-char-counter'    => false,
                // 'data-live-validation' => false,
            ],
        ],
        'formatters' => [
            'text' => [
                // 'max_length' => 5,
                // 'truncate_suffix',                   // Defaults to ...
                // 'truncate_suffix' => '...Read More',
                // 'null_value' => 'Nothing here',      // Replaces null value with string
                // 'suffix'     => "Boo",               // Appends to end of text
                // 'transform'  => 'lowercase',
                // 'transform'  => 'uppercase',
                // 'transform'  => 'capitalize',
                // 'transform'  => 'title',
                // 'transform'  => 'trim',              // notes-: assuming we did not store clean data
                // 'transform'  => 'last2char_upper',
            ],
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
                'forbidden'         => ['fook', 'shit'], // allows to add on to existing
                'allowed'           => ['fee', 'foo'],   // allows to add on to existing
                // 'ignore_forbidden'  => true,  // Default is false
                // 'ignore_allowed'    => false, // Default is true
                //---
                'required_message'  => 'common.generic_text.validation.required',
                'invalid_message'   => 'common.generic_text.validation.invalid',
                'minlength_message' => 'common.generic_text.validation.minlength',
                'maxlength_message' => 'common.generic_text.validation.maxlength',
                'pattern_message'   => 'common.generic_text.validation.pattern',
                'allowed_message'   => 'common.generic_text.validation.allowed',
                'forbidden_message' => 'common.generic_text.validation.forbidden',
            ],
        ]
    ],



    'primary_email' => [
        'list' => [
            'label' => 'primary_email',
            'sortable' => false,
        ],
        'form' => [
            'label' => 'primary_email',
            'type'          => 'email',
            'attributes'    => [
                'placeholder' => 'primary_email.placeholder',
                'required'    => true,
                'minlength'   => 12,
                'maxlength'   => 255,
                // 'pattern'     => '/^user[a-z0-9._%+-]*@/',
            ],
        ],
        'formatters' => [
            'email' => [
                // 'mask' => true, // Or false, or omit for default
            ],
            'text' => [
                'transform' => 'uppercase',
            ],
        ],
        'validators' => [
            'email' => [
                'allowed'           => ['ok.com', 'gmail.com'],   // Allowed domains
                'forbidden'         => ['fook.com'],              // Not allowed domains
                // 'ignore_forbidden'  => true,  // Default is false
                // 'ignore_allowed'    => false, // Default is true
                //---
                // 'required_message'  => "Custom: Email is required.",
                // 'invalid_message'   => "Custom: Please enter a valid email address.",
                // 'minlength_message' => "Custom: Email must be at least ___ characters.",
                // 'maxlength_message' => "Custom: Email should not exceed ___ characters.",
                // 'pattern_message'   => "Custom: Email does not match the required pattern.",
                // 'forbidden_message' => 'Custom: This domain is not allowed.',
                // 'allowed_message'   => 'Custom: Please select a valid domain.',
            ],
        ]
    ],
    'telephone' => [
        'list' => [
            'label' => 'telephone',
            'sortable' => false,
        ],
        'form' => [
            'label' => 'telephone',
            //  'region' => 'US',
            'type'          => 'tel',
            'attributes'    => [
                'placeholder' => 'telephone.placeholder',
                // 'required'              => true,
                // 'list'                  => 'foo',
                // 'data-char-counter'     => true,     // js-feature
                // 'data-live-validation'  => true      // js-feature
                // 'data-mask'             => 'phone', // todo - mast does not validate.
                // 'data-country'          => 'pt',    // todo - revisit for validation -  'pattern, maxlength
                // 'style' => 'background: cyan;',
            ],
        ],
        'formatters' => [
            // 'tel' => []
            'tel' => [
                // 'format' => 'default', // no need. FYI National format if its detected
                // 'format' => 'dashes',  // Force dashes
                // 'format' => 'dots',    // Force dots
                // 'format' => 'spaces',  // Force spaces
                // 'region' => 'PT',      // Optional: provide a specific region context
            ]
        ],
        'validators' => [
            'tel' => [
                // 'required_mess age'  => "Custom: Phone  is required.",
                // 'invalid_message'   => "Custom: Please enter a valid international phone number
                //                         (e.g., +15551234567). Invalid Error.",
                // 'invalid_region_message' => 'Custom: Invalid_region',
                // 'invalid_parse_message'  => 'Custom: Please enter a valid international phone number
                //                             (e.g., +15551234567). Parse Error',
            ],
        ]
    ],
];
