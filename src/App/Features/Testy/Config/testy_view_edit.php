<?php

/**
 * Generated File - Date: 20251114_094216
 * Field definitions for the Testy_edit entity.
 *
 * This file defines how each field should be rendered in forms and lists,
 * including labels, input types, attributes, formatters, and validators.
 */

declare(strict_types=1);

return [
    'render_options' => [ // gen
        'from'                  => 'testy_view_create-config',

        // HTML attributes (id, data-*, aria-*)
        'attributes' => [
            // 'id' => 'testy-edit-form',
            // 'data-analytics' => 'form-testy',
        ],

        // Behavior flags
        'ajax_save'         => true,    // js-feature: renderer/JS should enable ajax save behavior
        'auto_save'         => true,    // js-feature: enable auto-save/draft for the whole form
        'use_local_storage' => true,    // js-feature: Use localStorage for drafts


        // 'uszzzzzzzzzzzzrage' => false,    // js-feature: Use localStorage for drafts



        // 'force_captcha'        => false,
        'security_level'       => 'low', // 'low', 'medium', 'high'
        'layout_type'          => 'sequential', //'fieldsets', 'sequential', 'sections'
        'error_display'        => 'inline', //'inline', 'summary',
        'html5_validation'     => false,

        'css_form_theme_class' => "form-theme-neon",
        'css_form_theme_file'  => 'neon', // "christmas", neon

        'show_title_heading'   => true,
        'title_heading_level'  => 'h3', // Default is 'h2'
        'title_heading_class'  => null, // Use ThemeService default, or provide custom class if needed
        'form_heading_wrapper_class' => null, // Use ThemeService default, do-not-change. See note-#52

        'submit_button_variant' => 'primary',
        'cancel_button_variant' => 'secondary',

        // 'theme'

    ],
    'form_layout' => [
        [
            'title' => 'Your Favorite',
            'fields'    => [
                'title',
            ],
            // 'fields' => 'Your Favorite',
            // 'fieldxxs'    => [
                // 'id',
                // 'title',
                // 'titlessss',

                // 'is_verified',
                // 'status',
                // 'primary_email',

                // 'generic_text',
                // 'profile_picture',
                // 'generic_number',
                // 'gender_id',
                // 'state_code',
                // 'super_powers',
                // 'telephone',
            // ],
            'divider' => true,
        ],
        // [
        //     'title'     => 'Your Title',
        //     // 'fields'    => [
        //         'generic_text',
        //     // ],
        //     'divider'   => true
        // ],
        // [
        //     'title' => 'Your Favorite',
        //     'fields' => [
        //         'content',
        //         // 'generic_text',
        //         // 'telephone',
        //         // 'date_of_birth',
        //         // 'interest_soccer_ind',
        //         // 'interest_baseball_ind',
        //         // 'interest_football_ind',
        //         // 'interest_hockey_ind',
        //     ],
        //     'divider' => true,
        // ],
    ],
    'form_hidden_fields' => [
        'primary_email',
        // 'id',
        // 'store_id',
        // 'testyXxxx_user_id',
    ],
    'form_extra_fields' => [
        'telephone', // Needed for business logic or display
        // 'primary_emailsssccc',
        // Add any other fields needed for checks, validation, or logic
    ]
];
