<?php

declare(strict_types=1);

return [
    'actions' => 'Actions',
    // 'id' => 'id',
    'id'  => [
        'list' => [
            'label'         => 'ID',
        ],
    ],
    'generic_text'  => [
        'list' => [
            'label'         => 'Generic Text',
        ],
        'form' => [
            'label'         => 'Generic Text',
            'placeholder'   => 'Enter Generic Text',
        ],
        'validation' => [
            'required'      => 'The Generic Text is required.',
            'minlength'     => 'Generic Text must be at least {minlength} characters.',
            'maxlength'     => 'Generic Text must not exceed %d characters.',
            'invalid'       => 'Invalid Generic Text.',
            'pattern'       => 'Generic Text does not match the required pattern.',
            'allowed'       => 'Please select a valid Generic Text.',
            'forbidden'     => 'This Generic Text is not allowed.',
        ],
         'hints' => [
            'required'      => 'Required field',
            'minlength'     => 'At least %d characters',
            'maxlength'     => 'Maximum %d characters',
            'min'           => 'Minimum value: %s',
            'max'           => 'Maximum value: %s',
            'date_min'      => 'Not before: %s',
            'date_max'      => 'Not after: %s',
            'pattern'       => 'Must match required format',
            'email'         => 'Must be a valid email address',
            'tel'           => 'Enter with country code (e.g., +1234567890)',
            'url'           => 'Must start with http:// or https://',
        ],
    ],

    // 'generic_textbb'  => 'Generic Text aaa..',
    // 'id'         => 'ID_base',
    // 'store_id'   => 'StoreID',
    // 'user_id'    => 'UserID',
    // 'title'      => 'Titleccc',
    // 'author'     => 'Author',
    // 'username'   => 'Username',
    // 'created_at' => 'Created At',
    // 'updated_at' => 'Updated At',
    // 'status'     => 'Status',
    // 'is_verified'     => 'Verified',

    // 'list' => [
    //     // 'generic_text'  => 'Generic Text 123..',
    //     'title'                 => 'Testy Items Listcccc',
    //     'column_id'             => 'Testy ID', // If you need a specific ID label for the list
    //     'action_add'            => 'Add New Testy',
    //     'empty_state_message'   => 'No Testy items found.',
    // ],


    // // Form-specific translations (for both add and edit)
    // 'form' => [
    //     // 'generic_text'  => 'Generic Text 999..',
    //     'title'                 => 'Testy Items xxxxxxxxxxxxxxxxxxxxxxListcccc',
    //     'add_title'             => 'Create New Testy Item',
    //     'edit_title'            => 'Edit Testy Item',
    //     'save_button'           => 'Save Testy',
    //     'cancel_button'         => 'Cancel',
    //     'placeholder' => [
    //         'title'             => 'Enter a descriptive title for the testy item',
    //         'primary_email'     => 'e.g., testy@example.com',
    //         'telephone'         => 'e.g., +1 555 123 4567',
    //     ],

    //     'hints' => [
    //         'required'  => 'Required field_base',
    //         'minlength' => 'At least %d characters_base',
    //         'maxlength' => 'Maximum %d characters',
    //         'min'       => 'Minimum value: %s',
    //         'max'       => 'Maximum value: %s',
    //         'date_min'  => 'Not before: %s',
    //         'date_max'  => 'Not after: %s',
    //         'pattern'   => 'Must match required format',
    //         'email'     => 'Must be a valid email address',
    //         'tel'       => 'Enter with country code (e.g., +1234567890)',
    //         'url'       => 'Must start with http:// or https://',
    //     ],


    // ],
    // ...other shared labels
];
