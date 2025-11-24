<?php

/**
 * Generated File - Date: 20251119_150154
 * Field definitions for the basefield_base entity.
 *
 * This file defines how each field should be rendered in forms and lists,
 * including labels, input types, attributes, formatters, and validators.
 */

declare(strict_types=1);

return [
    'id' => [
        'list' => [
            'label'      => 'basefield.id.list.label',
            'sortable'   => false,
        ],
    ],
    'generic_text' => [
        'list' => [
            'label'      => 'basefield.generic_text.list.label',
            'sortable'   => false,
        ],
        'form' => [
            'label'      => 'basefield.generic_text.form.label',
            'type'       => 'text',
            'attributes' => [
                'placeholder' => 'basefield.generic_text.form.placeholder',
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
            ]
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
                'forbidden'         => ['fook', 'shit'], // allows to add on to existing
                'allowed'           => ['fee', 'foo'],   // allows to add on to existing
                // 'ignore_forbidden'  => true,  // Default is false
                // 'ignore_allowed'    => false, // Default is true
                //---
                'required_message'  => 'basefield.generic_text.validation.required',
                'invalid_message'   => 'basefield.generic_text.validation.invalid',
                'minlength_message' => 'basefield.generic_text.validation.minlength',
                'maxlength_message' => 'basefield.generic_text.validation.maxlength',
                'pattern_message'   => 'basefield.generic_text.validation.pattern',
                'allowed_message'   => 'basefield.generic_text.validation.allowed',
                'forbidden_message' => 'basefield.generic_text.validation.forbidden',
            ]
        ],
    ],
    'primary_email' => [
        'list' => [
            'label'      => 'basefield.primary_email.list.label',
            'sortable'   => false,
        ],
        'form' => [
            'label'      => 'basefield.primary_email.form.label',
            'type'       => 'email',
            'attributes' => [
                'placeholder' => 'basefield.primary_email.form.placeholder',
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
            'text' => [
                 // 'mask'             => true, // Or false, or omit for default
            ]
        ],
        'validators' => [
            'email' => [ // Default validator, can be refined based on db_type
                'forbidden'         => ['fook', 'shit'], // allows to add on to existing
                'allowed'           => ['fee', 'foo'],   // allows to add on to existing
                // 'ignore_forbidden'  => true,  // Default is false
                // 'ignore_allowed'    => false, // Default is true
                //---
                'required_message'  => 'basefield.primary_email.validation.required',
                'invalid_message'   => 'basefield.primary_email.validation.invalid',
                'minlength_message' => 'basefield.primary_email.validation.minlength',
                'maxlength_message' => 'basefield.primary_email.validation.maxlength',
                'pattern_message'   => 'basefield.primary_email.validation.pattern',
                'allowed_message'   => 'basefield.primary_email.validation.allowed',
                'forbidden_message' => 'basefield.primary_email.validation.forbidden',
            ],
        ],
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
                'placeholder' => 'basefield.status.form.placeholder',
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
];
