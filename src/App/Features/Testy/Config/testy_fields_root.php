<?php

/**
 * Generated File - Date: 20251129_154818 xxxxxxxxxxxxxxxxxxxx
 * Field definitions for the testy_root entity.
 *
 * This file defines how each field should be rendered in forms and lists,
 * including labels, input types, attributes, formatters, and validators.
 */

declare(strict_types=1);

// id
// status
// generic_text
// state_code
// gender_id
// is_verified
// primary_email
return [
    'id' => [
        'list' => [
            'label'      => 'id.list.label',
            'sortable'   => false,
        ],
    ],
    'status' => [
        'list' => [
            'label'      => 'status.list.label',
            'sortable'   => false,
        ],
        'form' => [
            'label'      => 'status.form.label',
            'type'       => 'select',
            'options_provider' => [\App\Enums\TestyStatus::class, 'toSelectArray'],
            'default_choice'   => 'status.form.default_choice',
            'attributes' => [
                'required' => true,
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
                'required_message'  => 'status.validation.required',
                'invalid_message'   => 'status.validation.invalid',
            ]
        ],
    ],
    'generic_text' => [
        'list' => [
            'label'      => 'generic_text.list.label',
            'sortable'   => false,
        ],
        'form' => [
            'label'      => 'generic_text.form.label',
            'type'       => 'text',
            'attributes' => [
                'placeholder' => 'generic_text.form.placeholder',
                'required'    => true,
                'minlength'   => 5,
                'maxlength'   => 50,
                // 'pattern'     => '/^[a-z0-9]/',
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
            'text' => [ // Default validator, can be refined based on db_type
                'forbidden'         => ['fook', 'shit'], // allows to add on to existing
                'allowed'           => ['fee', 'foo'],   // allows to add on to existing
                // 'ignore_forbidden'  => true,  // Default is false
                // 'ignore_allowed'    => false, // Default is true
                //---
                'required_message'  => 'generic_text.validation.required',
                'invalid_message'   => 'generic_text.validation.invalid',
                'minlength_message' => 'generic_text.validation.minlength',
                'maxlength_message' => 'generic_text.validation.maxlength',
                'pattern_message'   => 'generic_text.validation.pattern',
                'allowed_message'   => 'generic_text.validation.allowed',
                'forbidden_message' => 'generic_text.validation.forbidden',
            ],
        ],
    ],
    'state_code' => [
        'list' => [
            'label'      => 'state_code.list.label',
            'sortable'   => false,
        ],
        'form' => [
            'label'      => 'state_code.form.label',
            'type'       => 'select',
            'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, 'getSelectChoices'],
            'options_provider_params' => ['type' => 'state_code'],
            'default_choice'   => 'state_code.form.default_choice',
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
                'required_message'  => 'state_code.validation.required',
                'invalid_message'   => 'state_code.validation.invalid',
            ],
        ],
    ],
    'gender_id' => [
        'list' => [
            'label'      => 'gender_id.list.label',
            'sortable'   => false,
        ],
        'form' => [
            'label'      => 'gender_id.form.label',
            'type'       => 'radio_group',
            'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, 'getSelectChoices'],
            'options_provider_params' => ['type' => 'gender'],
            // 'default_choice'   => 'gender_id.form.default_choice', gen
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
                'required_message'  => 'gender_id.validation.required',
                'invalid_message'   => 'gender_id.validation.invalid',
            ],
        ],
    ],
    'is_verified' => [ // good one
        'list' => [
            'label'      => 'is_verified.list.label',
            'sortable'   => false,
        ],
        'form' => [
            'label'      => 'is_verified.form.label',
            'type'       => 'checkbox',
            'attributes' => [
                'required'    => false,
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
                'required_message'  => 'is_verified.validation.required',
                'invalid_message'   => 'is_verified.validation.invalid',
            ],
        ],
    ],
    'primary_email' => [
        'list' => [
            'label'      => 'primary_email.list.label',
            'sortable'   => false,
        ],
        'form' => [
            'label'      => 'primary_email.form.label',
            'type'       => 'email',
            'attributes' => [
                'placeholder' => 'primary_email.form.placeholder',
                'required'    => true,
                'minlength'   => 5,
                'maxlength'   => 255,
                // 'pattern'     => '/^user[a-z0-9._%+-]*@/',
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
            'email' => [ // Default validator, can be refined based on db_type
                'forbidden'         => ['fook', 'shit'], // allows to add on to existing
                'allowed'           => ['fee', 'foo'],   // allows to add on to existing
                // 'ignore_forbidden'  => true,  // Default is false
                // 'ignore_allowed'    => false, // Default is true
                //---
                'required_message'  => 'primary_email.validation.required',
                'invalid_message'   => 'primary_email.validation.invalid',
                'minlength_message' => 'primary_email.validation.minlength',
                'maxlength_message' => 'primary_email.validation.maxlength',
                'pattern_message'   => 'primary_email.validation.pattern',
                'allowed_message'   => 'primary_email.validation.allowed',
                'forbidden_message' => 'primary_email.validation.forbidden',
            ],
        ],
    ],
];
