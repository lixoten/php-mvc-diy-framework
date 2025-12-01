<?php

/**
 * Generated File - Date: 20251129_160212
 * Language File for testy_main.
 *
 * This file provides localized strings for the application, specifically for a given entity.
 * Depending on the configuration type ('main' or 'common'), it contains:
 * - Labels for fields in lists and forms.
 * - Placeholder texts for input elements.
 * - Button texts (e.g., 'Add', 'Edit', 'Delete', 'Cancel').
 * - Validation messages (e.g., 'is required', 'minlength').
 * - Other general UI texts and actions.
 *
 * The 'main' type includes the entity name in relevant translations (e.g., "Post title is required"),
 * while the 'common' type provides generic, entity-agnostic phrases (e.g., "title is required").
 */

declare(strict_types=1);

return [
    'code' => [
        'testy_status' => [
            'p' => 'Pending',
            'a' => 'Active',
            's' => 'Suspended',
            'b' => 'Banned',
            'd' => 'Deleted',
        ],
        'state_code' => [
            'ca' => 'California',
            'nj' => 'New Jersey',
            'al' => 'Alabama',
            'tx' => 'Texas',
            'ny' => 'New York',
        ],
        'gender' => [
            'm' => 'Male',
            'f' => 'Female',
            'o' => 'Other',
            'nb' => 'Non-binary',
            'pns' => 'Prefer not to say',
        ],
        'bool_yes_no_code' => [
            '1' => 'Yes',
            '0' => 'No',
        ],
    ],

    // 'label' => [
    //     'yes' => 'Yes',
    //     'no'  => 'No',
    // ],

    'id' => [
        'list' => [
            'label'       => 'xID',
        ],
    ],
    'status' => [
        'list' => [
            'label'       => 'xStatus',
        ],
        'form' => [
            'label'       => 'xStatus',
            'default_choice' => 'Please select your Testy status.',
        ],
        'formatters' => [

        ],
        'validation' => [
                    'required'  => 'Testy xStatus is required.',
                'invalid'   => 'Invalid Testy xStatus.',
        ],
    ],
    'generic_text' => [
        'list' => [
            'label'       => 'xGeneric text',
        ],
        'form' => [
            'label'       => 'xGeneric text',
            'placeholder' => 'Enter xGeneric text',
        ],
        'formatters' => [

        ],
        'validation' => [
                    'required'  => 'Testy xGeneric text is required.',
                'invalid'   => 'Invalid Testy xGeneric text.',
                'minlength' => 'Testy xGeneric text must be at least %d characters.',
                'maxlength' => 'Testy xGeneric text must not exceed %d characters.',
                'pattern'   => 'Testy xGeneric text does not match the required pattern.',
                'allowed'   => 'Please select a valid Testy xGeneric text.',
                'forbidden' => 'This Testy xGeneric text is not allowed.',
        ],
    ],
    'state_code' => [
        'list' => [
            'label'       => 'xStates',
        ],
        'form' => [
            'label'       => 'xStates',
            'default_choice' => 'Please select your Testy xStates.',
        ],
        'formatters' => [

        ],
        'validation' => [
                    'required'  => 'Testy xStates is required.',
                'invalid'   => 'Invalid Testy xStates.',
        ],
    ],
    'gender_id' => [
        'list' => [
            'label'       => 'xGender',
        ],
        'form' => [
            'label'       => 'xGender',
//             'default_choice' => 'Please select your Testy xGender.',
        ],
        'formatters' => [

        ],
        'validation' => [
                    'required'  => 'Testy xGender is required.',
                'invalid'   => 'Invalid Testy xGender.',
        ],
    ],
    'is_verified' => [
        'list' => [
            'label'       => 'xIs Verified',
        ],
        'form' => [
            'label'       => 'xIs Verified',
        ],
        'formatters' => [

        ],
        'validation' => [
                    'required'  => 'Testy xIs Verified is required.',
                'invalid'   => 'Invalid Testy xIs Verified.',
        ],
    ],
    'primary_email' => [
        'list' => [
            'label'       => 'xPrimary email',
        ],
        'form' => [
            'label'       => 'xPrimary email',
            'placeholder' => 'lixoten@gmail.com',
        ],
        'formatters' => [

        ],
        'validation' => [
                    'required'  => 'Testy xPrimary email is required.',
                'invalid'   => 'Invalid Testy xPrimary email.',
                'minlength' => 'Testy xPrimary email must be at least %d characters.',
                'maxlength' => 'Testy xPrimary email must not exceed %d characters.',
                'pattern'   => 'Testy xPrimary email does not match the required pattern.',
                'allowed'   => 'Please select a valid Testy xPrimary email.',
                'forbidden' => 'This Testy xPrimary email is not allowed.',
        ],
    ],
];
