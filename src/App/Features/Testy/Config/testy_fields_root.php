<?php

/**
 * Generated File - Date: 20251113_183416
 * Field definitions for the Testy_root entity.
 *
 * This file defines how each field should be rendered in forms and lists,
 * including labels, input types, attributes, formatters, and validators.
 */

declare(strict_types=1);

return [
    'title' => [ // gen
        'label' => 'testy.title',
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
            'text' => [ // Default validator, can be refined based on db_type
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
    'generic_text' => [ // gen
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
            'text' => [ // Default validator, can be refined based on db_type
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
    'telephone' => [ // gen
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
            'tel' => [
                // 'format' => 'default', // no need. FYI National format if its detected
                // 'format' => 'dashes',  // Force dashes
                // 'format' => 'dots',    // Force dots
                // 'format' => 'spaces',  // Force spaces
                // 'region' => 'PT',      // Optional: provide a specific region context
            ],
        ],
        'validators' => [
            'tel' => [ // Default validator, can be refined based on db_type
                // 'required_mess age'  => "Custom: Phone  is required.",
                // 'invalid_message'   => "Custom: Please enter a valid international phone number
                //                         (e.g., +15551234567). Invalid Error.",
                // 'invalid_region_message' => 'Custom: Invalid_region',
                // 'invalid_parse_message'  => 'Custom: Please enter a valid international phone number
                //                             (e.g., +15551234567). Parse Error',
            ],
        ]
    ],
    'primary_email' => [ // gen
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
                'transform' => 'lowercase',
            ],
        ],
        'validators' => [
            'email' => [ // Default validator, can be refined based on db_type
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
];
