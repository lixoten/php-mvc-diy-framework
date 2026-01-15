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

    'dev_code' => [
        'ERR-DEV-TL-001'    => 'Unexpected top-level configuration key found.',
        'ERR-DEV-TL-002'    => 'Missing top-level configuration key.',
        'ERR-DEV-TL-003'    => 'Top-level configuration key must be an array.',
        'ERR-DEV-TL-004'    => 'Entity Class not found.',
        'ERR-DEV-RO-001'    => 'Unexpected Render Option from in \'render_options\'',
        'ERR-DEV-RO-002'    => 'Render Option that should be a boolean that is not.',
        'ERR-DEV-RO-003'    => 'Invalid \'security_level\' in \'render_options\'',
        'ERR-DEV-RO-004'    => 'Invalid \'layout_type\' in \'render_options\'',
        'ERR-DEV-RO-005'    => 'Invalid \'error_display\' in \'render_options\'',
        'ERR-DEV-RO-006'    => 'Invalid \'title_heading_level\' in \'render_options\'',
        'ERR-DEV-RO-007a'   => 'Invalid \'button_variant\' in \'render_options\'',
        'ERR-DEV-RO-007b'   => 'Invalid \'button_variant\' in \'render_options\'',
        'ERR-DEV-FL-001'    => 'Form Layout Array can not be empty',
        'ERR-DEV-FL-002'    => 'Form Layout Section must be an array.',
        'ERR-DEV-FL-003'    =>  'Unexpected Key in \'form_layout\'',
        'ERR-DEV-FL-004'    => 'Form Layout \'Title\' must be a string.',
        'ERR-DEV-FL-004'    => 'Form Layout \'Divider\' must be a string.',
        'ERR-DEV-FL-005'    => 'xxxty',
        'ERR-DEV-FL-006'    => 'Form Layout Section \'field\' is missing or is not an array.',
        'ERR-DEV-FL-007'    => 'Form Layout Section must contain at least one \'field\' required.',

        'ERR-DEV-FN-031'    => 'Form Layout, Referenced Field must be a string',
        'ERR-DEV-FN-032'    => 'Form Layout, Referenced Field was not found in FieldRegistryService',
        'ERR-DEV-FN-033'    => 'xxxxxxxxxxxxxx',

        'ERR-DEV-005'       => 'Form hidden field not found in entity',
        'ERR-DEV-029'       => 'form_layout field is empty',
        'ERR-DEV-032'       => 'form_layout-field was not found  via FieldRegistryService',
        'ERR-DEV-FD-001'    => 'Form schema (forms/schema.php) not found.',
        'ERR-DEV-L-FD-012'  => 'Field Config File Duplicated validation rule(s). Attribute(s).',// list
        'ERR-DEV-L-FD-013'  => 'Field Config File List sortable field must be a boolean',       // list
        'ERR-DEV-L-FD-014'  => 'Field Config File has both \'formatters\' and \'formatters\'',  // list
        'ERR-DEV-F-FD-001'  => 'XXXX',
        'ERR-DEV-F-FD-002'  => 'Field Config File has form section without an element Type defined',
        'ERR-DEV-F-FD-003'  => 'Field Config File has unknown form element Type defined',
        'ERR-DEV-F-FD-004'  => 'Field Config File is missing \'form\' section, or not an array',
        'ERR-DEV-F-FD-005'  => 'Field Config File has has unknown attribute.',
        'ERR-DEV-F-FD-006'  => 'Field Config File has has unknown validator option.',
        'ERR-DEV-F-FD-007'  => 'Field Config File attribute found outside of attributes/direct under form section.',
        'ERR-DEV-F-FD-008'  => 'Field Config File unknown form level option direct under form section.',
        'ERR-DEV-F-FD-009'  => 'Field Config File ',
        'ERR-DEV-F-FD-010'  => 'Field Config File invalid value assignment found',
        'ERR-DEV-F-FD-011'  => 'Field Config File Formatters are not allowed in forms, unless type is \'tel\'',
        'ERR-DEV-F-FD-012'  => 'Field Config File Duplicated validation rule(s). Attribute(s).',
        'ERR-DEV-F-FD-015'  => 'Field Config File attribute is explicitly disallowed for type',
        'ERR-DEV-F-FD-016'  => 'XXXX',
        'ERR-DEV-F-FD-017'  => 'XXXX',
        'ERR-DEV-F-FD-018'  => 'XXXX',
    ],

    // // Boolean Translations
    // 'bool_yes_no_code' => [
    //     'yes'       => 'Yes',
    //     'no'        => 'Noo',
    // ],
    'code' => [
        'unknown' => 'Unknown',
        'super_powers' => [
            'flight'       => 'Flight',
            'strength'     => 'Super Strength',
            'invisibility' => 'Invisibility',
            'telepathy'    => 'Telepathy',
            'speed'        => 'Super Speed',
            'telekinesis'  => 'Telekinesis',
            'optiona'      => 'OPTION A',
            'optionb'      => 'OPTION B',
            'optionc'      => 'OPTION CCC',
        ],

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
            '0' => 'No',
            '1' => 'Yes',
        //     // 'n' => 'No',
        //     // 'y' => 'Yes',
        ],
        // 'is_verified' => [
        //     '0' => 'Unverified',
        //     '1' => 'Verified',
        // ],
    ],


    //-----------------------------------------------------------------
    // END SECTION: Code Lookup Translations
    //-----------------------------------------------------------------

    'validation' => [
        'required'  => 'xxThis is a required field..',
        'invalid'   => 'xxInvalid value.',
        'minlength' => 'xxThis value must be at least %d characters.',
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
        'file_to_large'     => 'The file is too large. Maximum allowed size is %d.',
    ],


    'list'    => [
        'title' => 'List Main Title',
        'actions' => 'Actions',
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
        'error' => [
            'instructions' => 'Please correct the following errors',
        ],


        'restore_data_from_server' => 'Restore Data from server',

        'title' => 'Edit Recordccccc',
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
