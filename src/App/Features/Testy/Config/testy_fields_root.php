<?php

/**
 * Generated File - Date: 20251206_075530 origggggggggggggggggg
 * Field definitions for the testy_root entity.
 *
 * This file defines how each field should be rendered in forms and lists,
 * including labels, input types, attributes, formatters, and validators.
 */

declare(strict_types=1);

// id
// super_powers
// status
// generic_text
// telephone
// state_code
// gender_id
// is_verified
// primary_email
// generic_number
return [
    'id' => [
        'list' => [
            'sortable'    => false,
        ],
    ],
    'super_powers' => [
        'data_transformer' => 'json_array',
        'list' => [
            'sortable'   => false,
            'formatters' => [
                'array' => [
                    'enum_class' => \App\Enums\SuperPower::class,
                    'separator' => ', ',
                    'empty_text' => 'None',
                ],
                'badge' => [
                    'variant' => 'primary',
                ],
                'badge_collection' => [
                    'options_provider' => [\App\Enums\SuperPower::class, 'getFormatterOptions'],
                    'separator' => ' ',
                ],
            ]
        ],
        'form' => [
            'type'       => 'checkbox_group',
            'options_provider' => [\App\Enums\SuperPower::class, 'toSelectArray'],
            'inline' => false, // or true for horizontal layout
            'attributes' => [
                // 'required' => false,
                // 'style'       => 'background:yellow;',
            ],
        ],
        'validators' => [
            'checkbox_group' => [
                // 'min_choices' => 1,
                // 'max_choices' => 3,
            ],
        ],
    ],
    'status' => [
        'list' => [
            'sortable'   => false,
            'formatters' => [
                'text' => [
                    // 'enum_class' => \App\Enums\TestyStatus::class,
                    'options_provider' => [\App\Enums\TestyStatus::class, 'getFormatterOptions'],
                ],
                'badge' => [
                    // 'enum_class' => \App\Enums\TestyStatus::class,
                    'options_provider' => [\App\Enums\TestyStatus::class, 'getFormatterOptions'],
                ],
            ],
        ],
        'form' => [
            'type'       => 'select',
            'options_provider' => [\App\Enums\TestyStatus::class, 'toSelectArray'],
            'display_default_choice' => true,
            'attributes' => [
                // 'required'    => false,
                // 'style'       => 'background:yellow;',
            ],
        ],
        'validators' => [
            'select' => [
            ],
        ],
    ],
    'generic_text' => [
        'list' => [
            'sortable'    => false,
            'formatters' => [
                'text' => [
                    // 'xxxxxxmax_length' => 5,
                    // 'truncate_suffix' => '...',          // Defaults to ...
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
        ],
        'form' => [
            'type'        => 'text',
            'placeholder' => true,
            'attributes'  => [
                'required'    => true,
                'minlength'   => 5,
                'maxlength'   => 50,
                // 'pattern'     => '/^[a-z0-9./',
                // 'style'       => 'background:yellow;',
                // 'data-char-counter'    => false,
                // 'data-live-validation' => false,
            ],
        ],
        'validators' => [
            'text' => [
                // 'ignore_allowed'   => true,
                // 'ignore_forbidden' => false,
                // 'allowed'          => [aaaa, bbbb],
                // 'forbidden'        => [fuck, dick],
            ],
        ],
    ],
    'telephone' => [
        'list' => [
            'sortable'    => false,
            'formatters' => [
                'tel' => [
                    // 'format' => 'default', // not needed, is default. FYI National format if its detected
                    // 'format' => 'dashes',  // Force dashes
                    // 'format' => 'dots',    // Force dots
                    // 'format' => 'spaces',  // Force spaces
                    // 'region' => 'PT',      // Optional: provide a specific region context
                ],
            ],
        ],
        'form' => [
             //  'region' => 'US',
            'type'        => 'tel',
            'placeholder' => true,
            'attributes'  => [
                // 'required'    => false,
                // 'style'       => 'background:yellow;',
                'data-char-counter'    => true,
                'data-live-validation' => true,
                // 'xxxxlist'                  => 'foo',
                // 'xxdata-char-counter'     => true,     // js-feature
                // 'data-live-validation'  => true      // js-feature
                // 'xxdata-mask'             => 'phone', // todo - mast does not validate.
                // 'xxdata-country'          => 'pt',    // todo - revisit for validation -  'pattern, maxlength
            ],
            'formatters' => [
                'tel' => [
                    // 'format' => 'default', // not needed, is default. FYI National format if its detected
                    // 'format' => 'dashes',  // Force dashes
                    // 'format' => 'dots',    // Force dots
                    // 'format' => 'spaces',  // Force spaces
                    // 'region' => 'PT',      // Optional: provide a specific region context
                ],
            ],
        ],
        'validators' => [
            'tel' => [
            ],
            // 'required_mess age'  => "Custom: Phone  is required.",
            // 'invalid_message'   => "Custom: Please enter a valid international phone number
            //                         (e.g., +15551234567). Invalid Error.",
            // 'invalid_region_message' => 'Custom: Invalid_region',
            // 'invalid_parse_message'  => 'Custom: Please enter a valid international phone number
            //                             (e.g., +15551234567). Parse Error',
        ],
    ],
    'state_code' => [
        'list' => [
            'sortable'   => false,
            'formatters' => [
                'text' => [
                    'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, 'getFormatterOptions'],
                    'lookup_type' => 'state_code',
                ],
                'badge' => [
                    'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, 'getFormatterOptions'],
                    'lookup_type' => 'state_code',
                ],
            ],
        ],
        'form' => [
            'type'       => 'select',
            'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, 'getSelectChoices'],
            'options_provider_params' => ['type' => 'state_code'],
            'display_default_choice' => true,
            'attributes' => [
                'required'    => true,
                // 'style'       => 'background:yellow;',
            ],
        ],
        'validators' => [
            'select' => [
            ],
        ],
    ],
    'gender_id' => [
        'list' => [
            'sortable'   => false,
            'formatters' => [
                'text' => [
                    'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, 'getFormatterOptions'],
                    'lookup_type' => 'gender',
                ],
                'badge' => [
                    'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, 'getFormatterOptions'],
                    'lookup_type' => 'gender',
                ],
            ],
        ],
        'form' => [
            'type'       => 'radio_group',
            'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, 'getSelectChoices'],
            'options_provider_params' => ['type' => 'gender'],
            // 'display_default_choice' => true,
            'attributes' => [
                'required'    => true,
                // 'style'       => 'background:yellow;',
            ],
        ],
        'validators' => [
            'radio_group' => [
            ],
        ],
    ],
    'is_verified' => [
        'data_transformer' => 'boolean',
        'list' => [
            'sortable'   => false,
            'formatters' => [
                'boolean' => [
                    'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, 'getFormatterOptions'],
                    'lookup_type' => 'bool_yes_no_code',
                ],
                'badge' => [
                    'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, 'getFormatterOptions'],
                    'lookup_type' => 'bool_yes_no_code',
                ],
            ],
            // 'formatters' => [
            //     'boolean' => [
            //         'true_code' => 'y',
            //         'false_code' => 'n',
            //     ],
            //     'badge' => [
            //         'boolean_badges' => [
            //             'true' => [
            //                 'code' => 'code.is_verified.v',
            //                 'variant' => 'success',
            //             ],
            //             'false' => [
            //                 'code' => 'code.is_verified.u',
            //                 'variant' => 'secondary',
            //             ],
            //         ],
            //     ],
            // ],
        ],
        'form' => [
            'type'       => 'checkbox',
            'attributes' => [
                // 'required'    => false,
                // 'style'       => 'background:yellow;',
            ],
        ],
        'validators' => [
            'checkbox' => [
            ],
        ],
    ],
    'primary_email' => [
        'list' => [
            'sortable'    => false,
            'formatters' => [
                'email' => [
                    // 'mask'             => true, // Or false, or omit for default
                ],
                'text' => [
                    // 'xxxxxxmax_length' => 5,
                    // 'truncate_suffix' => '...',          // Defaults to ...
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
        ],
        'form' => [
            'type'        => 'email',
            'placeholder' => true,
            'attributes'  => [
                'required'    => true,
                'maxlength'   => 255,
                // 'style'       => 'background:yellow;',
                // 'data-char-counter'    => false,
                // 'data-live-validation' => false,
            ],
        ],
        'validators' => [
            'email' => [
                // 'ignore_allowed'   => true,
                // 'ignore_forbidden' => false,
                // 'allowed'          => [good.com, heaven.org],
                // 'forbidden'        => [xxx.com, bad.com],
            ],
        ],
    ],
    'generic_number' => [
        'list' => [
            'sortable'    => true,
            'formatters' => [
                'text' => [
                    // 'xxxxxxmax_length' => 5,
                    // 'truncate_suffix' => '...',          // Defaults to ...
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
        ],
        'form' => [
            'type'        => 'number',
            'placeholder' => true,
            'attributes'  => [
                // 'required'    => false,
                'min'   => 11,
                // 'style'       => 'background:yellow;',
            ],
        ],
        'validators' => [
            'number' => [
                // 'positive_only'    => false,
                // 'negative_only'    => false,
                // 'zero_not_allowed' => false,
                // 'enforce_step'     => false,
                // 'ignore_allowed'   => true,
                // 'ignore_forbidden' => false,
                // 'allowed'          => [111],
                // 'forbidden'        => [444, 888],
            ],
        ],
    ],
    'profile_picture' => [
        'list' => [
            'sortable'    => false,
            'formatters' => [
                'image_link' => [
                    'preset' => 'thumbs', // ✅ Use 'preset' for ImageStorageService to resolve path
                    'default_image' => '/assets/images/default-avatar.png', // ✅ Fallback
                    'alt_field' => 'generic_text', // ✅ Use this field for alt text
                    'width' => 150, // ✅ Thumbnail size
                    'height' => 150,
                    // 'link_to' => '/testy/view/{id}', // ✅ OPTIONAL: Make image clickable
                ],
            ],
        ],
        'form' => [
            'type'        => 'file',
            // 'placeholder' => true,
            'attributes'  => [
                'accept' => 'image/*',
            ],
        ],
        'validators' => [
            'text' => [
            ],
        ],
    ],
];
//334
