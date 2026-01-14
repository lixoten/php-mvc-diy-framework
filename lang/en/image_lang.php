<?php

/**
 * Generated File - Date: 20251206_075526
 * Language File for image_main.
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
        'image_status' => [
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
            'default_choice' => 'Please select your Image xStatus.',
        ],
        'formatters' => [

        ],
        'validation' => [
                    'required'  => 'Image xStatus is required.',
                'invalid'   => 'Invalid Image xStatus.',
        ],
    ],
    'title' => [
        'list' => [
            'label'       => 'title text',
        ],
        'form' => [
            'label'       => 'title text',
            'placeholder' => 'Enter title text',
        ],
        'formatters' => [

        ],
        'validation' => [
                    'required'  => 'Image title text is required.',
                'invalid'   => 'Invalid Image title text.',
                'minlength' => 'Image title text must be at least %d characters.',
                'maxlength' => 'Image title text must not exceed %d characters.',
                'pattern'   => 'Image title text does not match the required pattern.',
                'allowed'   => 'Please select a valid Image title text.',
                'forbidden' => 'This Image title text is not allowed.',
        ],
    ],
    'filename' => [
        'list' => [
            'label'       => 'File',
        ],
        'form' => [
            'label'       => 'FFile',
            'placeholder' => 'Enter title text',
        ],
        'formatters' => [
        ],
        'validation' => [
        ],
    ]
];
