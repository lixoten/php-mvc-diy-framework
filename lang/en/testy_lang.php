<?php

/**
 * Generated File - Date: 20251120_161617cccc
 *
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
    'actions' => 'Testy Actions',
    'button'  => [
        'delete' => 'Testy Delete',
        'edit'   => 'Testy Edit',
        'add'    => 'Testy Add',
        'create' => 'Testy CREAdd',
        'view'   => 'Testy View',
        'save'   => 'Testy Save',
        'cancel' => 'Testy Cancel',
    ],
    'form' => [
        'heading' => 'Edit Recordttttttt',
    ],
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
                'invalid'   => 'Testy xGeneric text must be at least %d characters.',
                'minlength' => 'Testy xGeneric text must not exceed %d characters.',
                'maxlength' => 'Invalid Testy xGeneric text.',
                'pattern'   => 'Testy xGeneric text does not match the required pattern.',
                'allowed'   => 'Please select a valid Testy xGeneric text.',
                'forbidden' => 'This Testy xGeneric text is not allowed.',
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
                    'required'   => 'Testy xPrimary email is required.',
                'invalid'   => 'Testy xPrimary email must be at least %d characters.',
                'minlength' => 'Testy xPrimary email must not exceed %d characters.',
                'maxlength' => 'Invalid Testy xPrimary email.',
                'pattern'   => 'Testy xPrimary email does not match the required pattern.',
                'allowed'   => 'Please select a valid Testy xPrimary email.',
                'forbidden' => 'This Testy xPrimary email is not allowed.',
        ],
    ],
];
