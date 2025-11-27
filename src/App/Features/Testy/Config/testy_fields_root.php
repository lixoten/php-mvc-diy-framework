<?php

/**
 * Generated File - Date: 20251127_122226
 * Field definitions for the testy_root entity.
 *
 * This file defines how each field should be rendered in forms and lists,
 * including labels, input types, attributes, formatters, and validators.
 */

declare(strict_types=1);

// id
// generic_text
// gender_id
// primary_email
return [
    'id' => [
        'list' => [
            'label'      => 'id.list.label',
            'sortable'   => false,
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
    'gender_id' => [
        'list' => [
            'label'      => 'gender_id.list.label',
            'sortable'   => false,
        ],
        'form' => [
            'label'      => 'gender_id.form.label',
            'type'       => 'select',
            'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, 'getSelectOptions'],
            'options_provider_params' => ['type' => 'gender'],
            'default_choice' => 'gender_id.form.default_choice',
            'attributes' => [
                // 'required'    => false,
            ],
        ],
        'formatters' => [
            'text' => [
                'options_provider' => [
                            \Core\Interfaces\CodeLookupServiceInterface::class, 'getFormatterOptions'
                ],
                'options_provider_params' => ['type' => 'gender'],
            ],
        ],
        'validators' => [
    
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
