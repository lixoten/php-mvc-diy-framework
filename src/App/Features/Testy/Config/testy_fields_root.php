<?php

/**
 * Generated File - Date: 20251206_075530
 * Field definitions for the testy_root entity.
 *
 * This file defines how each field should be rendered in forms and lists,
 * including labels, input types, attributes, formatters, and validators.
 */

declare(strict_types=1);

// id
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
            'sortable'   => false,
        ],
    ],
    'status' => [
        'list' => [
            'sortable'   => false,
        ],
        'form' => [
            'type'       => 'select',
            'options_provider' => [\App\Enums\TestyStatus::class, 'toSelectArray'],
            'display_default_choice'   => true,
            'attributes' => [
                // 'required'    => false,
                // 'style'       => 'background:yellow;',
            ],
        ],
        'formatters' => [
            'text' => [
                'options_provider' => [\App\Enums\TestyStatus::class, 'getFormatterOptions'],
            ],
            // 'badge' => [
            //     'options_provider' => [TestyStatus::class, 'getFormatterOptions'],
            // ],
        ],
        'validators' => [
            'select' => [
            ],
        ],
    ],
    'generic_text' => [
        'list' => [
            'sortable'   => false,
        ],
        'form' => [
            'type'       => 'text',
            'placeholder'   => false,
            'attributes' => [
                'required'    => true,
                'minlength'   => 5,
                'maxlength'   => 50,
                // 'pattern'     => '/^[a-z0-9./',
                // 'style'       => 'background:yellow;',
                // 'data-char-counter'    => false,
                // 'data-live-validation' => false,
            ],
        ],
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
        'validators' => [
            'text' => [
                // 'ignore_allowed'    => true,
                // 'ignore_forbidden'  => false,
                // 'allowed'           => [aaaa, bbbb],
                // 'forbidden'         => [fuck, dick],
            ],
        ],
    ],
    'telephone' => [
        'list' => [
                'sortable'   => false,
        ],
        'form' => [
            //  'region' => 'US',
            'type'       => 'tel',
            'placeholder' => true,
            'attributes' => [
                'required'    => true,
                // 'maxlength'   => 20,
                // 'pattern'     => '[a-z0-9]/',
                // 'style'       => 'background:yellow;',
                // 'xxrequired'              => true,
                // 'xxlist'                  => 'foo',
                // 'xxdata-char-counter'     => true,     // js-feature
                // 'data-live-validation'  => true      // js-feature
                // 'xxdata-mask'             => 'phone', // todo - mast does not validate.
                // 'xxdata-country'          => 'pt',    // todo - revisit for validation -  'pattern, maxlength
                // 'xxstyle' => 'background: cyan;',
            ],
        ],
        'formatters' => [
            'tel' => [
                 // 'format' => 'default', // no need. FYI National format if its detected
                // 'format' => 'dashes',  // Force dashes
                'format' => 'dots',    // Force dots
                // 'format' => 'spaces',  // Force spaces
                // 'region' => 'PT',      // Optional: provide a specific region context
            ],
        ],
        'validators' => [
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
        ],
        'form' => [
            'type'       => 'select',
            'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, 'getSelectChoices'],
            'options_provider_params' => ['type' => 'state_code'],
            'display_default_choice'   => true,
            'attributes' => [
                'required'    => true,
                // 'style'       => 'background:yellow;',
            ],
        ],
        'formatters' => [
            'text' => [
                'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, 'getFormatterOptions'],
                'options_provider_params' => ['type' => 'state_code'],
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
        ],
        'form' => [
            'type'       => 'radio_group',
            'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, 'getSelectChoices'],
            'options_provider_params' => ['type' => 'gender'],
            // 'default_choice'   => 'gender_id.form.default_choice',
            'attributes' => [
                'required'    => true,
                // 'style'       => 'background:yellow;',
            ],
        ],
        'formatters' => [
            'text' => [
                'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, 'getFormatterOptions'],
                'options_provider_params' => ['type' => 'gender'],
            ],
        ],
        'validators' => [
            'radio_group' => [
            ],
        ],
    ],
    'is_verified' => [
        'list' => [
            'sortable'   => false,
        ],
        'form' => [
            'type'       => 'checkbox',
            'attributes' => [
                // 'required'    => false,
                // 'style'       => 'background:yellow;',
            ],
        ],
        'formatters' => [
            'text' => [
                'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, 'getFormatterOptions'],
                'options_provider_params' => ['type' => 'bool_yes_no_code'],
            ],
            // 'badge' => [
            //     'options_provider' => [\App\Features\Testy\Testy::class, 'getIsVerifiedBadgeOptions'],
            // ],
        ],
        'validators' => [
            'checkbox' => [
            ],
        ],
    ],
    'primary_email' => [
        'list' => [
            'sortable'   => false,
        ],
        'form' => [
            'type'       => 'email',
            'placeholder' => true,
            'attributes' => [
                'required'    => true,
                'maxlength'   => 255,
                // 'style'       => 'background:yellow;',
                // 'data-char-counter'    => false,
                // 'data-live-validation' => false,
            ],
        ],
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
        'validators' => [
            'email' => [
                // 'ignore_allowed'    => true,
                // 'ignore_forbidden'  => false,
                // 'allowed'           => [good.com, heaven.org],
                // 'forbidden'         => [xxx.com, bad.com],
            ],
        ],
    ],
    'generic_number' => [
        'list' => [
            'sortable'   => true,
        ],
        'form' => [
            'type'       => 'number',
            'placeholder' => true,
            'attributes' => [
                // 'required'    => false,
                'min'   => 11,
                // 'style'       => 'background:yellow;',
            ],
        ],
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
        'validators' => [
            'number' => [
                // 'positive_only'            => false,
                // 'negative_only'            => false,
                // 'zero_not_allowed'         => false,
                // 'enforce_step'             => false,
                // 'ignore_allowed'           => true,
                // 'ignore_forbidden'         => false,
                // 'allowed'                  => [111],
                // 'forbidden'                => [444, 888],
            ],
        ],
    ],
];
//334
