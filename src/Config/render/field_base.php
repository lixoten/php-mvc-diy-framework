<?php

use App\Helpers\DebugRt;

// DebugRt::j('0', '', 'BOOM on Config File');
return [
    'id' => [
        'label' => 'base.id',
        'list' => [
            'sortable' => true,
            'formatter' => null,
        ],
    ],
    'store_id' => [
        'label' => 'testy.store_id',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'number',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.store_id.placeholder',
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
        'label' => 'testy.user_id',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'number',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.user_id.placeholder',
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
        'label' => 'testy.title',
        'list' => [
            'sortable' => false,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => true, // Required if not nullable
        ],
        'formatters' => [
            'text' => [
                'max_length' => 10,
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
        'label' => 'base.name',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'textarea',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'base.name.placeholder',
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
        'label' => 'base.acrName',
        'list' => [
            'sortable' => true,
            'formatter' => function ($value) {
                return htmlspecialchars((string)$value ?? '');
            },
        ],
    ],

    'description' => [
        'label' => 'base.description',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'textarea',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'base.description.placeholder',
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
        'label' => 'base.content',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'textarea',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'base.content.placeholder',
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
        'label' => 'base.status',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.status.placeholder',
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
    //     'label' => 'base.acrStatus',
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



    'generic_text' => [
        'label' => 'testy.generic_text',
        'list' => [
            'sortable' => false,
        ],
        'form' => [
            'type'          => 'text',
            'attributes'    => [
                'placeholder' => 'testy.generic_text.placeholder',
                // 'required'  => true,     // Used in validation
                // 'minlength' => 5,        // Used in validation
                // 'maxlength' => 15,       // Used in validation
                // 'pattern'   => '/\d/',   // Used in validation
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
            'text' => [
                'forbidden'         => ['fook', 'shit'], // allows to add on to existing
                'allowed'           => ['fee', 'foo'],   // allows to add on to existing
                // 'ignore_forbidden'  => true,  // Default is false
                // 'ignore_allowed'    => false, // Default is true
                //---
                'required_message'  => "Custom: This field is required.",
                // 'invalid_message'   => "Custom: Please enter a valid text.",
                // 'minlength_message' => "Custom: Text must be at least ___ characters.",
                // 'maxlength_message' => "Custom: Text must not exceed ___ characters.",
                // 'pattern_message'   => "Custom: Text does not match the required pattern.",
                // 'allowed_message'   => "Custom: Please select a valid word.",
                // 'forbidden_message' => "Custom: This word is not allowed.",
            ],
        ]
    ],
    'primary_email' => [
        'label' => 'testy.primary_email',
        'list' => [
            'sortable' => false,
        ],
        'form' => [
            'type'          => 'email',
            'attributes'    => [
                'placeholder' => 'testy.primary_email.placeholder',
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
        'label' => 'testy.telephone',
        'list' => [
            'sortable' => false,
        ],
        'form' => [
            //  'region' => 'US',
            'type'          => 'tel',
            'attributes'    => [
                'placeholder' => 'testy.telephone.placeholder',
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
