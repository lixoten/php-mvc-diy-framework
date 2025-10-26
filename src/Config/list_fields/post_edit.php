<?php

declare(strict_types=1);

return [
    'id' => [
        'label' => 'posts.id---postField', //ok
        'list' => [
            'sortable' => true,
            'formatter' => null,
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
        // 'sanitize' => function ($value, $config, $data) { //fixme maybe???? inside the actually validators as inside "text"
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


    // 'title' => [
    //     'label' => 'posts.title',
    //     'list' => [
    //         'sortable' => true,
    //         'formatter' => fn($value) => htmlspecialchars($value ?? ''),
    //         // 'formatter' => function ($value) {
    //             // return htmlspecialchars($value ?? '');
    //         // },
    //     ],
    //     'form' => [
    //         'type' => 'text',
    //         'required' => true,
    //         'minlength' => 10,
    //         'maxlength' => 30,
    //         'attributes' => [
    //             'class' => 'form-control',
    //             'id' => 'title',
    //             'placeholder' => 'posts.title.placeholder', //.Enter a post title'
    //         ]
    //     ]
    // ],
    'content' => [
        'label' => 'posts.content-local',
        'form' => [
            'type' => 'textarea',
            'required' => true,
            'minlength' => 10,
            'maxlength' => 2000,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'content',
                'placeholder' => 'posts.content.placeholder',//'Enter post content',
                'rows' => '6'
            ]
        ]
    ],
];
