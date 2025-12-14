<?php

/**
 * Generated File - Date: 20251206_075530 origgggggggggggggggggggg
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
    'title' => [
        'list' => [
            'sortable'    => false,
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
                // 'ignore_allowed'   => true,
                // 'ignore_forbidden' => false,
                // 'allowed'          => [aaaa, bbbb],
                // 'forbidden'        => [fuck, dick],
            ],
        ],
    ],
];
//334
