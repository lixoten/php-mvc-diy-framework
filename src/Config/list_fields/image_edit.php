<?php

declare(strict_types=1);

use App\Helpers\DebugRt;

// DebugRt::j('0', '', 'BOOM on Config File');
return [
    'id' => [
        'label' => '',
        'form' => [
            'attributes' => [
                'type' => 'hidden',
            ],
        ]
    ],
    'user_id' => [
        'label' => '',
        'form' => [
            'attributes' => [
                'type' => 'hidden',
            ],
        ]
    ],
    'title222' => [ // Example - do not use
        'label' => 'testy.title',
        'formatter' => [                // Example: multiple Formatters in array
            'truncate5',                // Example: Built-in Formatter in array
            function ($value) {
                // Custom formatter to convert to uppercase // Example: Custom Formatter in Config
                return strtoupper((string)($value ?? ''));
            },
            'foo'                       // Eample: Built-in Formatter in array that happens to be a class
        ],
        'validators' => [
            'callback' => [
                'callback' => fn($value) => !empty($value) && strtoupper(substr($value, 0, 1)) === 'U',
                'message' => 'The title must start with the letter "U".',
            ],
        ],
        'form' => [
            'type'          => 'text',
            'attributes'    => [
                // 'value' => "TTT",
                // // 'fake' => 'one',
                // 'class' => 'fooCls',
                // // 'style' => 'border: 2px dotted green;',
                // 'id' => 'titleID',
                // 'name' => 'titleNAME',
                // 'autocomplete' => "off",
                // 'autocomplete' => "off",
                // 'inputmode' => "off",
                // // 'placeholder' => 'testy.title.placeholder', //.Enter a testy title'
                // 'required' => true,
                // 'readonly' => false,
                // 'minlength' => "aaaa",
                'minlength' => 5,
                'maxlength' => 30,
                // 'style'     => 'background: red;',
                'data-char-counter'     => true, // js-feature
                'data-live-validation'  => true  // js-feature
            ],
        ]
    ],
    'profile_picture' => [
        'label' => 'Picture of me',
        'form' => [
            'type' => 'file',
            'attributes' => [
                'accept' => 'image/*',
                // 'disabled' => true,
            ],
            'upload' => [
                'max_size' => 2097152,  // 2 MB
                'mime_types' => ['image/jpeg', 'image/png', 'image/gif'],
                'subdir' => 'pictures',
            ],
        ],
        // 'formatters' => [
        //     // ['name' => 'phone', 'options' => ['region' => 'US']],  // Include region in per-formatter options
        //     // ['name' => 'phone', 'options' => ['region' => ''$userRegion, 'user_region' => $userRegion]],
        //     // ['name' => 'phone', 'options' => ['region' => 'PT']],
        //     ['name' => 'image',
        //         'options' => [
        //             'base_url' => '/uploads/',  // Adjust to your server's web root or
                                               // upload directory, e.g., '/uploads/' if files are in /uploads/pictures/
        //             'class' => 'img-fluid',
        //             'alt' => 'Picture of me',  // Optional: for accessibility
        //         ],
        //     ],
        // ],
        'formatters' => [
            'image' => [
                'base_url' => '/uploads/',  // Adjust to your server's web root or upload directory, e.g.,
                                                                       // '/uploads/' if files are in /uploads/pictures/
                'class' => 'img-fluid',
                'alt' => 'Picture of me',  // Optional: for accessibility
            ],
        ],



    ],
    // 'profile_picture_upload' => [
    //     'label' => 'Upload New Picture',
    //     'form' => [
    //         'type' => 'file',
    //         'attributes' => [
    //             'accept' => 'image/*',
    //         ],
    //         'upload' => [
    //             'max_size' => 2097152,  // 2 MB
    //             'mime_types' => ['image/jpeg', 'image/png', 'image/gif'],
    //             'subdir' => 'pictures',
    //         ],
    //     ],
    // ],
    // 'profile_picture' => [
    //     'label' => 'Profile Picture',
    //     'form' => [
    //         'type' => 'file',
    //         'required' => false,
    //         'attributes' => [
    //             'accept' => 'image/*',
    //         ],
    //         'upload' => [
    //             'max_size' => 2097152,  // 2 MB
    //             'mime_types' => ['image/jpeg', 'image/png', 'image/gif'],
    //             'subdir' => 'pictures',
    //         ],
    //     ],
    // ],
    // 'title' => [ // Example - do not use
    //     'label' => 'testy.title',
    //     'form' => [
    //         'type'          => 'display',
    //         'attributes'    => [
    //         ],
    //     ]
    // ],
    'titlexxx' => [ // Example - do not use
        'label' => 'testy.title',
        'form' => [
            'type'          => 'text',
            'attributes'    => [
                'readonly' => true,
            ],
        ],
        'formatters' => [                // Example: multiple Formatters in array
                function ($value) {
                    // Custom formatter to convert to uppercase // Example: Custom Formatter in Config
                    return strtolower((string)($value ?? ''));
                },
            // 'text' => [
            //     // 'transform' => 'uppercase', // 'capitalize', 'uppercase', 'lowercase'
            //     // function ($value) {
            //     //     // Custom formatter to convert to uppercase // Example: Custom Formatter in Config
            //     //     return strtoupper((string)($value ?? ''));
            //     // },
            // ],

        ],
    ],
    'title' => [ // Example - do not use
        'label' => 'testy.title',
        'form' => [
            'type'          => 'text',
            'attributes'    => [
                'placeholder' => 'testy.title.placeholder', //.Enter a testy title'
                'required'  => true,
                'minlength' => 4,
                'maxlength' => 30,

                'pattern'   => '/\d/',

                'data-char-counter'     => true, // js-feature
                'data-live-validation'  => true,  // js-feature
                // 'autocomplete' => "off", // default is on, so only needed to turn off
                // 'style'     => 'background: red;',
            ],
        ],
        'formatters' => [                // Example: multiple Formatters in array
                function ($value) {
                    // Custom formatter to convert to uppercase // Example: Custom Formatter in Config
                    return strtolower((string)($value ?? ''));
                },
            // 'text' => [
            //     // 'transform' => 'uppercase', // 'capitalize', 'uppercase', 'lowercase'
            //     // function ($value) {
            //     //     // Custom formatter to convert to uppercase // Example: Custom Formatter in Config
            //     //     return strtoupper((string)($value ?? ''));
            //     // },
            // ],

        ],
        // 'sanitize' => function ($value, $config, $data) {
                                                      //fixme maybe???? inside the actually validators as inside "text"
        //         // Example: trim, remove HTML tags, and normalize to null if empty
        //         if (is_string($value)) {
        //             $value = trim($value);
        //             $value = strip_tags($value);
        //         }
        //         if ($value === '') {
        //             $value = null;
        //         }
        //         return $value;
        // },
        'validators' => [
            // 'callback' => [
            //     'callback' => fn($value) => !empty($value) && strtoupper(substr($value, 0, 1)) !== 'U',
            //     'message' => 'The title must start with the letter "U".',
            // ],
            // 'sanitize' => function ($value, $config, $data) {
            //     // Example: trim, remove HTML tags, and normalize to null if empty
            //     if (is_string($value)) {
            //         $value = trim($value);
            //         $value = strip_tags($value);
            //     }
            //     if ($value === '') {
            //         $value = null;
            //     }
            //     return $value;
            // },
            'text' => [
                // 'minlength' => 5,
                // 'maxlength' => 15,
                // 'pattern'   => '/\d/',
                // 'pattern'   => '/[1-5]/',
                'ignore_allowed'    => true,
                'ignore_forbidden'  => true,
                'allowed'           => ['cccc'],
                'forbidden'         => ['fook'],
                 //---
                'required_message'  => "Custom: This field is required.",
                'minlength_message' => "Custom: Text must be at least ___ characters.",
                'maxlength_message' => "Custom: Text must not exceed ___ characters.",
                'pattern_message'   => "Custom: Text does not match the required pattern.",
                'allowed_message'   => "Custom: Please select a valid word.",
                'forbidden_message' => "Custom: This word is not allowed.",
            //     'invalid_message'           => "Custom: invalid...",
            ],
        ]
    ],
    'secret_code_hash' => [
        'label' => 'Secret Code',
        'form' => [
            'type' => 'password',
            'attributes' => [
                'required' => true,
                'minlength' => 5,
                'maxlength' => 6,
                // 'pattern' => '/\d/',
                // 'data-char-counter'     => true, // js-feature
                // 'data-live-validation'  => true  // js-feature
            ],
        ],
        'validators' => [
            'password' => [
        //         'minlength' => 5,
        //         'maxlength' => 6,
        //         'pattern' => '/\d/',
        //         'forbidden_words'   => ['abcdef'],
        //         'require_digit'     => true,
        //         'require_uppercase' => true,
        //         'require_lowercase' => true,
        //         'require_special'   => true,
        //         //---
                'required_message'          => "Custom: This field is required.",
        //         'minlength_message'         => "Custom: Password must be at least ___ characters.",
        //         'maxlength_message'         => "Custom: This value should not exceed ___ characters.",
        //         'pattern_message'           => "Custom: pattern...",
        //         'forbidden_words_message'   => "Custom: forbidden...",
        //         'require_digit_message'     => "Custom: digit...",
        //         'require_uppercase_message' => "Custom: uppercase...",
        //         'require_lowercase_message' => "Custom: lowercase...",
        //         'require_special_message'   => "Custom: special...",
        //         'invalid_message'           => "Custom: invalid...",
            ],
        ],
    ],
    'primary_email' => [
        'label' => 'Primary Email',
        'form' => [
            'type' => 'email',
            'attributes' => [
                'placeholder' => 'Enter your primary email',
                'required' => true,
                'minlength' => 5,
                'maxlength' => 255,
                'pattern' => '/^user[a-z0-9._%+-]*@/',

                // 'list' => 'email-domains', // Future - Datalists does not work reliably in chrome. Future

                // 'data-char-counter'     => true, // js-feature
                // 'data-live-validation'  => true,  // js-feature
            ],
        ],
        'formatters' => [
            'email' => [
            //     'mask' => true,
            ],
        ],
        'validators' => [
            'email' => [
                // 'minlength'         => 12,
                // 'maxlength'         => 255,
                // 'pattern'           => '/^user[a-z0-9._%+-]*@/',

                // 'ignore_allowed'    => false, // Default is true
                // 'ignore_forbidden'  => true,  // Default is false
                'allowed'           => ['ok.com'],
                'forbidden'         => ['ccc.com'],
                //---
                // 'required_message'  => "Custom: Email is required.",
                'invalid_message'   => "Custom: Please enter a valid email address.",
                'minlength_message' => "Custom: Email must be at least ___ characters.",
                'maxlength_message' => "Custom: Email should not exceed ___ characters.",
                'pattern_message'   => "Custom: Email does not match the required pattern.",
                'forbidden_message' => 'Custom: This domain is not allowed.',
                'allowed_message'   => 'Custom: Please select a valid domain.',
            ],
        //     'unique_email' => [
        //         'message' => 'This email address is already registered.'
        //     ]
        ]
    ],
    'online_address' => [
        'label' => 'Online Address',
        'form' => [
            'type' => 'url',
            'attributes' => [
                'required' => true,
                'placeholder' => 'Enter your website or profile URL',
                'minlength' => 10,
                'maxlength' => 255,
                // 'pattern' => '/profile/i', // Case-insensitive match for "profile"
                // 'data-char-counter'     => true, // js-feature
                // 'data-live-validation'  => true,  // js-feature
                // 'autocomplete' => 'url',
            ],
        ],
        'validators' => [
            'url' => [
                // 'minlength' => 10,
                // 'maxlength' => 255,
                // 'pattern' => '/profile/i', // Case-insensitive match for "profile"

                // 'ignore_allowed'    => false, // Default is true
                // 'ignore_forbidden'  => true,  // Default is false
                'allowed'           => ['rudy.ok.com', 'usa.com'],  // allows to add on to existing
                'forbidden'         => ['ass.ccc.com', 'russia.com'], // allows to add on to existing
                //---
                // 'required_message'  => "Custom: URL is required.",
                // 'invalid_message'   => "Custom: Please enter a valid url address with a domain.",
                // 'minlength_message' => "Custom: URL must be at least ___ characters.",
                // 'maxlength_message' => "Custom: URL should not exceed ___ characters.",
                // 'pattern_message'   => "Custom: URL does not match the required pattern.",
                'allowed_message'   => 'Custom: Please select a valid domain.',
                'forbidden_message' => 'Custom: This domain is not allowed.',
            ],
        ],
    ],
    'telephone' => [
        'label' => 'testy.telephone',
        'form' => [
            'type' => 'tel',
            // 'region' => 'PT',
            'attributes' => [
                'id' => 'telephone',
                // 'placeholder' => 'testy.telephone.placeholder', // '+1-555-123-4567',
                'required' => true,
                'maxlength' => 21,
                // 'list' => 'foo',
                // 'data-char-counter'     => true, // js-feature
                // 'data-live-validation'  => true  // js-feature
                // 'data-mask' => 'phone', // todo - mast does not validate.
                // 'data-country' => 'pt', // todo - revisit for validation -  'pattern', 'maxlength', or 'validators')
                // 'style' => 'background: cyan;',
            ],
            // 'datalist' => [],
        ],
        'formatters' => [
            'phone',
            // 'phone' => [],
            // 'phone' => [
            //     'format' => 'default', 'region' => 'US'
            // ],
        ],
        'validators' => [
            // 'phone' => [
            //     // 'invalid_region_message' => 'Custom: Invalid_region',
            //     // 'invalid_parse_message'  => 'Custom: Invalid_parse',
            //     // 'invalid_message'        => 'Custom: Invalid',
            // ],
        ],
    ],
    'my_search' => [
        'label' => 'Search',
        'form' => [
            'type' => 'url',
            'attributes' => [
                'placeholder' => 'sea....',
                'maxlength' => 255,
            ],
        ],
    ],
    // ----Date Based-------------------------------------
    'generic_date' => [
        'label' => 'testy.generic_date',
        'form' => [
            'type' => 'date',
            'attributes' => [
                'required' => true,
                'min'      => '1900-01-01',  // optional: earliest allowed date
                'max'      => date('Y-m-d'), // optional: latest allowed date (today)

                // 'data-live-validation'  => true  // js-feature
            ],
        ],
        'validators' => [
            'date' => [
                // 'max'    => '4026-09-25',
                //---
                'required_message'   => "custom - This field is required.",
                'min_message'        => "custom - Date must not be before ___.",
                'max_message'        => "custom - Date must not be after ___.",
                'invalid_message'    => "custom - Please enter a valid date.",
            ]
        ]
    ],
    'generic_datetime' => [
        'label' => 'testy.generic_datetime',
        'form' => [
            'type' => 'datetime',
            'attributes' => [
                'required' => true,
                'min' => '1900-01-01T00:00', // Earliest allowed datetime
                'max' => date('Y-m-d\TH:i'), // Latest allowed datetime (now)
                // 'data-live-validation'  => true  // js-feature
            ],
        ],
        'validators' => [
            'datetime' => [
                // 'max'    => '2026-12-25',
                //---
                'required_message'   => "custom - Date and time is required.",
                'min_message'        => "custom - Date and time must not be before ___.",
                'max_message'        => "custom - Date and time must not be after ___.",
                'invalid_message'    => "custom - Please enter a valid date and time " .
                                        "(YYYY-MM-DDTHH:MM or YYYY-MM-DDTHH:MM:SS).",
            ]
        ]
    ],
    'generic_month' => [
        'label' => 'testy.generic_month',
        'form' => [
            'type' => 'month',
            'attributes' => [
                'required' => true,
                'min' => '1900-01', // optional: earliest allowed date
                'max' => date('Y-m'), // optional: latest allowed date month (today)
                // 'data-live-validation'  => true  // js-feature
            ],
        ],
        'validators' => [
            'month' => [
                // 'max'    => '2026-09-25',
                //---
                'required_message'   => "custom - Year-Month is required.",
                'min_message'        => "custom - Month must not be before ___.",
                'max_message'        => "custom - Month must not be after ___.",
                'invalid_message'    => "custom - Please enter a valid year-month.",
            ]
        ]
    ],
    'generic_week' => [
        'label' => 'testy.generic_week',
        'form' => [
            'type' => 'week',
            'attributes' => [
                'required' => true,
                'min' => '1900-W01', // optional: earliest allowed date
                'max' => date('Y-\WW'), // latest allowed week (this week)
                // 'data-live-validation'  => true  // js-feature
            ],
        ],
        'validators' => [
            'week' => [
                // 'max'    => '2026-W12',
                //---
                'required_message'   => "custom - Year-Week is required.",
                'min_message'        => "custom - Year-Week must not be before ___.",
                'max_message'        => "custom - Year-Week must not be after ___.",
                'invalid_message'    => "custom - Please enter a valid year-week.",
            ]
        ]
    ],
    'generic_time' => [
        'label' => 'testy.generic_time',
        'form' => [
            'type' => 'time',
            'attributes' => [
                'required' => true,
                'min' => '08:00', // optional: earliest allowed date
                'max' => '12:58', // date('H:i'), // optional: latest allowed time (today)
                // 'data-live-validation'  => true  // js-feature
            ],
        ],
        'validators' => [
            'time' => [
                // 'max'    => '22:10',
                //---
                'required_message'   => "custom - Time is required.",
                'min_message'        => "custom - Time must not be before ___.",
                'max_message'        => "custom - Time must not be after ___.",
                'invalid_message'    => "custom - Please enter a valid time.",
            ]
        ]
    ],
    // ----Number Based-------------------------------------
    'generic_number' => [
        'label' => 'Generic Number DB INT, CONSTRAINT: 5-20',
        'form' => [
            'type' => 'number',
            'attributes' => [
                'placeholder' => 'Enter your number',
                'required' => true,
                'min'      => -10 ,
                'max'      => 200,
                'step'     => 2,
                'list' => 'foo',

                // 'data-char-counter'     => true, // js-feature
                // 'data-live-validation'  => true  // js-feature
            ],
            'datalist' => [
                '1', '4', '6'
            ],
        ],
        'validators' => [
            // 'length',
            // 'extratest' => [
            //     'forbidden' => [222],
            //     'forbidden_message'      => "custom - This extratest is not allowed.",

            // ],
            // 'extratest2',
            // 'extratest2' => [
            //     'forbidden' => [222],
            // ],
            'number' => [
                'enforce_step'      => true, // Only validate step if this is true
                // 'positive_only'     => true, // this looks at min value "FIRST"
                // 'negative_only'     => true, // this looks at min value "FIRST"
                'zero_not_allowed'  => true, // this looks at min value "FIRST"

                // 'ignore_allowed'    => false, // Default is true
                // 'ignore_forbidden'  => false,  // Default is true
                'allowed'           => [33],
                'forbidden'         => [11, 12],

                // ----
                'required_message'          => "custom - Number field is required.",
                'min_message'               => "custom - Value must be at least ___.",
                'max_message'               => "custom - Value must not exceed  ___.",
                'positive_only_message'     => "custom - Only positive numbers are allowed.",
                'negative_only_message'     => "custom - Only negative numbers are allowed.",
                'zero_not_allowed_message'  => "custom - Zero is not allowed.",
                'allowed_message'           => "custom - Please select a valid allowed number.",
                'forbidden_message'         => "custom - This number is not allowed.",
                'enforce_step_message'      => "custom - Number must be a multiple of ___.",
                // // 'leading_zeros_message'  => "custom - Leading zeros are not allowed.",
                'invalid_message'           => "custom - Please enter a whole number (no decimals).",
            ],
            'lengthxx' => [],
        ]
    ],
    'generic_decimal' => [
        'label' => 'generic_decimal DB 5,2',
        'form' => [
            'type' => 'decimal',
            'attributes' => [
                'placeholder' => 'Enter decimal number',
                'required' => true,
                'min'      => -5.1,
                'max'      => 100,
                'step'     => 0.00002,
                // 'list' => 'foo',



                // 'placeholder' => 'Enter decimal number',
                // 'step' => '0.01',
                // // 'data-char-  '     => true, // js-feature
                // 'data-live-validation'  => true  // js-feature
            ],
        ],
        'formatters' => [
            'decimal' => [
                // 'trim_zeros' => false, // Trim trailing zeros after formatting
            ],
        ],
        'validators' => [
            'decimal' => [
                'enforce_step'      => true, // Only validate step if this is true
                // 'positive_only'     => true, // this looks at min value "FIRST"
                // 'negative_only'     => true, // this looks at min value "FIRST"
                'zero_not_allowed'  => true, // this looks at min value "FIRST"

                // 'ignore_allowed'    => false, // Default is true
                // 'ignore_forbidden'  => false,  // Default is true
                'allowed'           => [5.5],
                'forbidden'         => [1.1, 1.22],
                // ----
                'required_message'       => "custom - Number field is required.",
                'min_message'            => "custom - Value must be at least ___.",
                'max_message'            => "custom - Value must not exceed  ___.",
                'positive_only_message'  => "custom - Only positive numbers are allowed.",
                'negative_only_message'  => "custom - Only negative numbers are allowed.",
                'zero_not_allowed_message' => "custom - Zero is not allowed.",
                'allowed_message'        => "custom - Please select a valid decimal number.",
                'forbidden_message'      => "custom - This number is not allowed.",
                'enforce_step_message'   => "custom - Number must be a multiple of ___.",
                'invalid_message'        => "custom - Please enter a decimal number.",
            ],
        ]
    ],
    'generic_decimalzzzzz' => [
        'label' => 'Generic Decimal Number',
        'form' => [
            'type' => 'number',
            'attributes' => [
                'placeholder' => 'Enter your luck Number',
                // 'min' => 0,
                'max' => 99999.99,
                'step' => "0.02",
                'required' => true,
                // 'data-char-counter'     => true, // js-feature
                // 'data-live-validation'  => true  // js-feature
            ],
        ],
        // 'formatter' => 'decimal',
        'formatters' => [
            'decimal' => [
                'decimals' => 4,      // Show up to 3 digits after decimal before trimming
                'trim_zeros' => true, // Trim trailing zeros after formatting

            ],
        ],
        'validators' => [
            'decimal_number' => [

                // 'enforce_step' => true, // Only validate step if this is true
                // 'step' => "0.00002",
                // 'positive_only' => true,
                //'negative_only' => true,
                // 'invalid_message' => "xxxxx",
            ],
        ]
    ],

    'volume_level' => [
        'label' => 'volume_level',
        'form' => [
            'type' => 'range',
            'attributes' => [
                // 'placeholder' => 'Enter temperature',
                'min' => 0,
                'max' => 100,
                'step' => 10,
                'list' => 'volumeticks',
                'data-show-value' => true,
            ],
            // Custom key for tick values (for renderer)
            'tickmarks' => [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100],
        ],
        'validators' => [
            'range' => [
                // 'min' => 50,
                // 'max' => 30,
                // 'enforce_step'      => true, // Only validate step if this is true

                // ----
                'min_message'            => "custom - Value must be at least ___.",
                'max_message'            => "custom - Value must not exceed  ___.",
                'enforce_step_message'   => "custom - Number must be a multiple of ___.",
                'invalid_message'        => "custom - This value must be a number.",
            ],
        ]
    ],
    'start_rating' => [
        'label' => 'start_rating',
        'form' => [
            'type' => 'range',
            'attributes' => [
                'min' => 0.5,
                'max' => 5,
                'step' => 0.5,
                'list' => 'starticks',
                'data-show-value' => true,

                // 'custom_invalid_message' => "custom: fil...",
                // 'custom_min_message' => "custom: This value must be at least ___.",
                // 'custom_max_message' => "custom: This value must not exceed ___.",
            ],
            'tickmarks' => [1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5],
        ],
        'validators' => [
            'range' => [
                // 'min' => 50,
                // 'max' => 30,
                // 'enforce_step'      => true, // Only validate step if this is true

                // ----
                'min_message'            => "custom - Value must be at least ___.",
                'max_message'            => "custom - Value must not exceed  ___.",
                'enforce_step_message'   => "custom - Number must be a multiple of ___.",
                'invalid_message'        => "custom - This value must be a number.",
            ],
        ]
    ],


    'generic_color' => [
        'label' => 'Choose Color',
        'form' => [
            'type' => 'color',
            'attributes' => [
                'id' => 'generic_color',
                // 'invalid_message' => 'Custom - Please select a valid color (e.g., #FF5733).',
                // 'not_allowed_message' => 'Custom - This color is not allowed.',
                // 'select_allowed_message' => 'Custom - Please select a color from the allowed list.',
                'list' => 'preset-colors', // Future - Datalists does not work reliably in chrome. Future
            ],
            'datalist' => [ // Important!!! to know, am empty datalist will dft some browser system's standard palette
                '#ff0000', // Red
                '#FF5733', // Vibrant Red-Orange
                '#33FF57', // Bright Green
                '#3357FF', // Vivid Blue
                '#FFD700', // Gold
                '#ffffff', // White
                '#800080', // Purple
                '#000000', // Black
            ],
        ],
        'validators' => [
            'color' => [
                // 'ignore_allowed'    => false, // Default is true
                // 'ignore_forbidden'  => false,  // Default is true
                'allowed'   => ['#ff0000', '#FF5733', '#33FF57'], // Optional: restrict to preset colors
                'forbidden' => ['#000000', '#3357ff'],

                'allowed_message'   => 'Custom: Please select a color from the allowed list.',
                'forbidden_message' => 'Custom: This color is not allowed.',
                'invalid_message' => 'Custom: Please select a valid color (e.g., #FF5733).',
            ],
        ],
    ],

    'is_verified' => [
        'label' => 'testy.is_verified',
        'form' => [
            'type' => 'checkbox',
            'attributes' => [
                'required' => true,
                'data-live-validation'  => true,  // js-feature
                'accesskey' => 'v', // User can press Alt+V (Windows) or Control+Option+V (Mac) to focus
            ],
        ],
        'validators' => [
            'checkbox' => [
                // ----
                'required_message' => 'custom - This field is required.',
                // 'invalid_message'        => "custom - This value must be a number.",
            ],
        ]
    ],
    'interest_soccer_ind' => [
        'label' => 'testy.interest_soccer_ind',
        'form' => [
            'type' => 'checkbox',
            'attributes' => [
            ],
        ]
    ],
    'interest_baseball_ind' => [
        'label' => 'testy.interest_baseball_ind',
        'form' => [
            'type' => 'checkbox',
            'attributes' => [
            ],
        ]
    ],
    'interest_football_ind' => [
        'label' => 'testy.interest_football_ind',
        'form' => [
            'type' => 'checkbox',
            'attributes' => [
            ],
        ]
    ],
    'interest_hockey_ind' => [
        'label' => 'testy.interest_hockey_ind',
        'form' => [
            'type' => 'checkbox',
            'attributes' => [
            ],
        ]
    ],



    'gender_id' => [
        'label' => 'Gender',
        'form' => [
            'type' => 'select',
            // 'placeholder' => '-- Please select --', // Optional: for UI hint, not a real <option>
            // 'default_choice' => '-- Please select --', // Optional: for UI hint, not a real <option>
            'choices' => [
                'm'  => 'Male',
                'f'  => 'Female',
                'o'  => 'Other',
                'nb' => 'Non-binary',
            ],
            'attributes' => [
                'id' => 'gender_id',
                'class' => 'form-select',
                // 'aria-label' => 'Gender',
                'required' => false,
            ],
            'validators' => [
                'in_array' => [
                    'values' => ['m', 'f', 'o', 'nb'],
                    'message' => 'Please select a valid gender.',
                ],
            ],
        ],
    ],
    'content' => [
        'label' => 'testy.content-local',
        'form' => [
            'type' => 'textarea',
            'attributes' => [
                'value' => 'eeeeee',
                // 'class' => 'form-control',
                'id' => 'content',
                'placeholder' => 'testy.content.placeholder',//'Enter testy content',
                'required' => true,
                // 'disabled' => true,
                'minlength' => 10,
                'maxlength' => 2000,
                'style' => 'background: cyan;',
                'rows' => '3',
                // 'cols' => '12', // notes-: this tends to be overridden by css
                // 'data-char-counter'     => true, // js-feature
                'data-live-validation'  => true  // js-feature
            ],
        ],
          'validators' => [
            // 'callback' => [
            //     'callback' => fn($value) => !empty($value) && strtoupper(substr($value, 0, 1)) !== 'U',
            //     'message' => 'The title must start with the letter "U".',
            // ],
            // 'sanitize' => function ($value, $config, $data) {
            //     // Example: trim, remove HTML tags, and normalize to null if empty
            //     if (is_string($value)) {
            //         $value = trim($value);
            //         $value = strip_tags($value);
            //     }
            //     if ($value === '') {
            //         $value = null;
            //     }
            //     return $value;
            // },
            'text' => [
                // 'minlength' => 5,
                // 'maxlength' => 15,
                // 'pattern'   => '/\d/',
                // 'pattern'   => '/[1-5]/',
                'ignore_allowed'    => true,
                'ignore_forbidden'  => true,
                'allowed'           => ['cccc'],
                'forbidden'         => ['fook'],
                 //---
                'required_message'  => "Custom: This field is required.",
                'minlength_message' => "Custom: Text must be at least ___ characters.",
                'maxlength_message' => "Custom: Text must not exceed ___ characters.",
                'pattern_message'   => "Custom: Text does not match the required pattern.",
                'allowed_message'   => "Custom: Please select a valid word.",
                'forbidden_message' => "Custom: This word is not allowed.",
            //     'invalid_message'           => "Custom: invalid...",
            ],
        ]
    ],





    'generic_text' => [
        'label' => 'testy.generic_text',
        'form' => [
            'type' => 'text',
            'attributes' => [
                'placeholder' => 'testy.generic_text.placeholder', //.Enter a testy title'
                'required' => true,
                'minlength' => 10,
                'maxlength' => 50,
                'data-char-counter'     => true, // js-feature
                'data-live-validation'  => true  // js-feature
                // 'style' => 'background: cyan;',
            ],
        ]
    ],
    'date_of_birth' => [
        'label' => 'testy.date_of_birth',
        'form' => [
            'type' => 'date',
            'attributes' => [
                // 'placeholder' => 'testy.date_of_birth.placeholder',
                'required' => true,
                'min' => '1900-01-01', // optional: earliest allowed date
                'max' => date('Y-m-d'), // optional: latest allowed date (today)
                // 'style' => 'background: cyan;',
                //'data-char-counter'     => true, // js-feature
                'data-live-validation'  => true  // js-feature
            ],
        ],
        'formatters' => '',
        'validators' => [
            'date' => [
                'max' => '2025-09-25',
                'max_message' => 'ccccccValue must be 09/25/2025 or earlier.'
            ]
        ]
    ],

    'gender_other' => [
        'label' => 'testy.gender_other',
        'form' => [
            'type' => 'text',
            'attributes' => [
                'id' => 'gender_other',
                'placeholder' => 'testy.gender_other.placeholder',
                'required' => false,
                'minlength' => 4,
                'maxlength' => 50,
                'data-char-counter'     => true, // js-feature
                'data-live-validation'  => true  // js-feature
                // 'style' => 'background: cyan;',
            ],
        ]
    ],



    'balance' => [
        'label' => 'Balance',
        'form' => [
            'type' => 'number',
            'attributes' => [
                'id' => 'balance',
                // 'placeholder' => 'Enter balance',
                // 'required' => true,
                // 'min' => 0,
                // 'max' => 100000,
                'step' => '1.02',
                // //'data-char-counter'     => true, // js-feature
                // 'data-live-validation'  => true  // js-feature
            ],
        ],
    ],


    'wake_up_time' => [
        'label' => 'Wake Up Time',
        'form' => [
            'type' => 'time',
            'attributes' => [
                'id' => 'wake_up_time',
                'placeholder' => 'Select wake up time',
                // 'data-char-counter'     => true, // js-feature
                'data-live-validation'  => true  // js-feature
            ],
        ],
    ],
    'favorite_week_day' => [
        'label' => 'Favorite Week day',
        'form' => [
            'type' => 'week',
            'attributes' => [
                'id' => 'favorite_week_day',
                'required' => false,
                'min' => '2024-W01',
                'max' => '2025-W52',
            ],
        ],
    ],

];
