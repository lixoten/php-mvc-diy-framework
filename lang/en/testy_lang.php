<?php

/**
 * Generated File - Date: 20251127_122004
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

    'id' => [
        'list' => [
            'label'       => 'xID',
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
    'gender_id' => [
        'list' => [
            'label'       => 'xGender',
        ],
        'form' => [
            'label'       => 'xGender',
            'default_choice' => 'Please select your Testy xGender.',
        ],
        'formatters' => [

        ],
        'validation' => [
                    'required'  => 'Testy xGender is required.',
                'invalid'   => 'Invalid Testy xGender.',
                'minlength' => 'Testy xGender must be at least %d characters.',
                'maxlength' => 'Testy xGender must not exceed %d characters.',
                'pattern'   => 'Testy xGender does not match the required pattern.',
                'allowed'   => 'Please select a valid Testy xGender.',
                'forbidden' => 'This Testy xGender is not allowed.',
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
