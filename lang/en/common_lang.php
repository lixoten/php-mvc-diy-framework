<?php

/**
 * Generated File - Date: 20251120_161632
 * Language File for common_common.
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
    'actions' => 'Actions',
    'button'  => [
        'delete' => 'Delete',
        'edit'   => 'Edit',
        'add'    => 'Add',
        'create' => 'CREAdd',
        'view'   => 'View',
        'save'   => 'Save',
        'cancel' => 'Cancel',
    ],
    'form' => [
        'heading' => 'Edit Recordccccc',
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
                    'required'  => 'xGeneric text is required.',
                'invalid'   => 'xGeneric text must be at least %d characters.',
                'minlength' => 'xGeneric text must not exceed %d characters.',
                'maxlength' => 'Invalid xGeneric text.',
                'pattern'   => 'xGeneric text does not match the required pattern.',
                'allowed'   => 'Please select a valid xGeneric text.',
                'forbidden' => 'This xGeneric text is not allowed.',
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
                    'required'   => 'xPrimary email is required.',
                'invalid'   => 'xPrimary email must be at least %d characters.',
                'minlength' => 'xPrimary email must not exceed %d characters.',
                'maxlength' => 'Invalid xPrimary email.',
                'pattern'   => 'xPrimary email does not match the required pattern.',
                'allowed'   => 'Please select a valid xPrimary email.',
                'forbidden' => 'This xPrimary email is not allowed.',
        ],
    ],
];
