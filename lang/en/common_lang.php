<?php

/**
 * Generated File - Date: 20251125_085034
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
    //-----------------------------------------------------------------
    // SECTION: Code Lookup Translations (Gender, Payment Types, etc.)
    //-----------------------------------------------------------------

    // /**
    //  * Gender Code Translationsxxxxxx
    //  *
    //  * Used by: CodeLookupService for gender_id field
    //  * Config: src/Config/app_lookups.php['gender']
    //  */
    // 'gender' => [
    //     'male'        => 'Male',
    //     'female'      => 'Female',
    //     'other'       => 'Other',
    //     'non_binary'  => 'Non-binary',
    //     'prefer_not_to_say'  => 'Prefer not to say',
    // ],

    // /**
    //  * State Code Translations
    //  *
    //  * Used by: CodeLookupService for state_code field
    //  * Config: src/Config/app_lookups.php['state_code']
    //  */
    // 'state_code' => [
    //     'ca'       => 'California',
    //     'nj'       => 'New Jersey',
    //     'tx'       => 'Texas',
    //     'al'       => 'Alabama',
    //     'ny'       => 'New York',
    // ],

    // /**
    //  * Payment Type Code Translations
    //  *
    //  * Used by: CodeLookupService for payment_type field
    //  * Config: src/Config/app_lookups.php['payment_type']
    //  */
    // 'payment' => [
    //     'credit_card'       => 'Credit Card',
    //     'paypal'            => 'PayPal',
    //     'invoice'           => 'Invoice',
    //     'cash_on_delivery'  => 'Cash on Delivery',
    // ],

    // /**
    //  * Delivery Method Code Translations
    //  *
    //  * Used by: CodeLookupService for delivery_method field
    //  * Config: src/Config/app_lookups.php['delivery_method']
    //  */
    // 'delivery' => [
    //     'standard'  => 'Standard Delivery',
    //     'express'   => 'Express Delivery',
    //     'pickup'    => 'Store Pickup',
    // ],

    // /**
    //  * Notification Frequency Code Translations
    //  *
    //  * Used by: CodeLookupService for notification_frequency field
    //  * Config: src/Config/app_lookups.php['notification_frequency']
    //  */
    // 'notification' => [
    //     'daily'     => 'Daily',
    //     'weekly'    => 'Weekly',
    //     'monthly'   => 'Monthly',
    //     'never'     => 'Never',
    // ],

    // /**
    //  * US States Code Translations (Abbreviated Examples)
    //  *
    //  * Used by: CodeLookupService for us_states field
    //  * Config: src/Config/app_lookups.php['us_states']
    //  *
    //  * Note: Add all 50+ states for production use
    //  */
    // 'states' => [
    //     'alabama'       => 'Alabama',
    //     'alaska'        => 'Alaska',
    //     'arizona'       => 'Arizona',
    //     'california'    => 'California',
    //     'new_york'      => 'New York',
    //     'texas'         => 'Texas',
    //     // ... (add remaining 44+ states as needed)
    // ],

    // /**
    //  * Color Code Translations
    //  *
    //  * Used by: CodeLookupService for color field
    //  * Config: src/Config/app_lookups.php['color']
    //  */
    // 'color' => [
    //     'red'       => 'Red',
    //     'blue'      => 'Blue',
    //     'green'     => 'Green',
    //     'black'     => 'Black',
    // ],


    // // Boolean Translations
    // 'bool_yes_no_code' => [
    //     'yes'       => 'Yes',
    //     'no'        => 'Noo',
    // ],
    'code' => [
        'testy_status' => [
            'P' => 'Pending',
            'A' => 'Active',
            'S' => 'Suspended',
            'B' => 'Banned',
            'D' => 'Deleted',
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


    //-----------------------------------------------------------------
    // END SECTION: Code Lookup Translations
    //-----------------------------------------------------------------

    'validation' => [
        'required'  => 'xxThis is a required field..',
        'invalid'   => 'xxInvalid value.',
        'minlength' => 'xxThis value must be at least %d characters.',
        'maxlength' => 'xxThis value must not exceed %d characters.',
        'pattern'   => 'xxThis value does not match the required pattern.',
        'allowed'   => 'xxPlease select a valid allowed value.',
        'forbidden' => 'xxThis value is not allowed.',
    ],





    'form'    => [
        'hints'   => [
            'required'  => 'Required field',
            'minlength' => 'At least %d characters',
            'maxlength' => 'Maximum %d characters',
            'min'       => 'Minimum value: %s',
            'max'       => 'Maximum value: %s',
            'date_min'  => 'Not before: %s',
            'date_max'  => 'Not after: %s',
            'pattern'   => 'Must match required format',
            'email'     => 'Must be a valid email address',
            'tel'       => 'Enter with country code (e.g., +1234567890)',
            'url'       => 'Must start with http:// or https://',
        ],
        'heading' => 'Edit Recordccccc',
        'select' => [
            'default_choice' => 'Please select one',
        ],
    ],
    'menu'    => [
        'home'            => 'Home',
        'test'            => 'Test',
        'about'           => 'About',
        'contact'         => 'Contact',
        'testy'           => 'Testy',
        'head'            => [
            'core'  => 'Core',
            'user'  => 'User',
            'store' => 'Store',
            'admin' => 'Admin',
        ],
        'profile'         => 'Profile',
        'user_manage'     => 'Manage Users',
        'admin_dashboard' => 'Admin Dashboard',
        'store_dashboard' => 'Store Dashboard',
        'store_profile'   => 'Store Profile',
        'store_settings'  => 'Store Settings',
        'user_dashboard'  => 'User Dashboard',
        'user_profile'    => 'User Profile',
        'user_settings'   => 'User Settings',
        'user_notes'      => 'User Notes',
        'user_list'       => 'User List',
    ],
    'actions' => 'Actions',
    'button'  => [
        'delete'     => 'Delete',
        'edit'       => 'Edit',
        'add'        => 'Add',
        'create'     => 'CREAdd',
        'view'       => 'View',
        'save'       => 'Save',
        'cancel'     => 'Cancel',
        'view_table' => 'Table View',
        'view_list'  => 'List View',
        'view_grid'  => 'Grid View',
    ],
    'id' => [
        'list' => [
            'label'       => 'xID',
        ],
    ],
];
