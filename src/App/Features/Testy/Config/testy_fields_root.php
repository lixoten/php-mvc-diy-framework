<?php

/**
 * Generated File - Date: 20251116_222332
 * Field definitions for the Testy_root entity.
 *
 * This file defines how each field should be rendered in forms and lists,
 * including labels, input types, attributes, formatters, and validators.
 */

declare(strict_types=1);

return [
    'generic_text' => [ // gen
        'list' => [
            'label'      => 'testy.generic_text.list.label',
            'sortable'   => false,
        ],
        'form' => [
            'label'      => 'testy.generic_text.form.label',
            'type'       => 'text',
            'attributes' => [
                'placeholder' => 'testy.generic_text.form.placeholder',
                'required'    => true,
                'minlength'   => 5,
                'maxlength'   => 50,
                // 'pattern'     => '/^[a-z]/',
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
                'required_message'  => 'testy.generic_text.validation.required',
                'invalid_message'   => 'testy.generic_text.validation.invalid',
                'minlength_message' => 'testy.generic_text.validation.minlength',
                'maxlength_message' => 'testy.generic_text.validation.maxlength',
                'pattern_message'   => 'testy.generic_text.validation.pattern',
                'allowed_message'   => 'testy.generic_text.validation.allowed',
                'forbidden_message' => 'testy.generic_text.validation.forbidden',
            ],
        ]
    ],
];
