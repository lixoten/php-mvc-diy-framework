<?php

/**
 * Generated File - Date: 20251206_075526
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
            'P' => 'Pending',
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
        // 'bool_yes_no_code.....................' => [
        //     '1' => 'Yes',
        //     '0' => 'No',
        // ],
    ],

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
            'default_choice' => 'Please select your Testy xStatus.',
        ],
        'formatters' => [

        ],
        'validation' => [
                    'required'  => 'Testy xStatus is required.',
                'invalid'   => 'Invalid Testy xStatus.',
        ],
    ],
    'title' => [
        'list' => [
            'label'       => 'Title',
        ],
        'form' => [
            'label'       => 'Title',
            'placeholder' => 'Enter Title',
        ],
        'validation' => [
            'required'  => 'xxssssThis is a required field..',
            'invalid'   => 'xxInvalid value.',
            'minlength' => 'xxTssssssssssssssssssshis value must be at least %d characters.',
            'maxlength' => 'xxThis value must not exceed %d characters.',
            'min'       => 'xxThis value must be at least %d.',
            'max'       => 'xxThis value must not exceed %d.',
            'pattern'   => 'xxThis value does not match the required pattern.',
            'allowed'   => 'xxPlease select a valid allowed value.',
            'forbidden' => 'xxThis value is not allowed.',
            'positive_only'    => 'xxThis value must be a positive number.',
            'negative_only'    => 'xxThis value must be a negative number.',
            'zero_not_allowed' => 'xxZero value is not allowed.',
            'enforce_step'     => 'xxNumber must be a multiple of %d.',
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
    'telephone' => [
        'list' => [
            'label'       => 'xTelephone number',
        ],
        'form' => [
            'label'       => 'xTelephone number',
            'placeholder' => 'Enter xTelephone number',
        ],
        'formatters' => [

        ],
        'validation' => [
                    'required'  => 'Testy xTelephone number is required.',
                'invalid'   => 'Invalid Testy xTelephone number.',
                'minlength' => 'Testy xTelephone number must be at least %d characters.',
                'maxlength' => 'Testy xTelephone number must not exceed %d characters.',
                'pattern'   => 'Testy xTelephone number does not match the required pattern.',
                'allowed'   => 'Please select a valid Testy xTelephone number.',
                'forbidden' => 'This Testy xTelephone number is not allowed.',
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
    'generic_number' => [
        'list' => [
            'label'       => 'xGeneric Number',
        ],
        'form' => [
            'label'       => 'xGeneric Number',
            'placeholder' => 'Enter xGeneric Number',
        ],
        'formatters' => [

        ],
        'validation' => [
                    'required'   => 'Testy xGeneric Number is required.',
                'invalid'   => 'Invalid Testy xGeneric Number.',
                'minlength' => 'Testy xGeneric Number must be at least %d characters.',
                'maxlength' => 'Testy xGeneric Number must not exceed %d characters.',
                'min'       => 'Testy xGeneric Number must be at least %d.',
                'max'       => 'Testy xGeneric Number must not exceed %d.',
                'pattern'   => 'Testy xGeneric Number does not match the required pattern.',
                'allowed'   => 'Please select a valid Testy xGeneric Number.',
                'forbidden' => 'This Testy xGeneric Number is not allowed.',

                'positive_only' => 'This Testy xGeneric Number must be a positive number.',
                'negative_only' => 'This Testy xGeneric Number must be a negative number.',
                'zero_not_allowed' => 'This Testy xGeneric Number Zero value is not allowed.',
                'enforce_step'  => 'This Testy xGeneric Number must be a multiple of %d.',
        ],
    ],
    'super_powers' => [
        'list' => [
            'label'       => 'xSuper Powers',
        ],
        'form' => [
            'label'       => 'xSuper Powers',
        ],
        'formatters' => [

        ],
        'validation' => [
        ],
    ],
];
