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
        'ajax_save'         => true,     // js-feature: renderer/JS should enable ajax save behavior
        'auto_save'         => false,    // js-feature: enable auto-save/draft for the whole form
        'use_local_storage' => false,    // js-feature: Use localStorage for drafts

        // 'force_captcha'        => false,
        // 'security_level'       => 'low', //CONST_SL::LOW,      // HIGH / MEDIUM / LOW
        // 'layout_type'           => 'sequential', //CONST_L::SEQUENTIAL,    // FIELDSETS / SECTIONS / SEQUENTIAL
        'layout_type'           =>  'sequential', //'fieldsets', // 'sequential', //CONST_L::SEQUENTIAL,    // FIELDSETS / SECTIONS / SEQUENTIAL
        // 'error_display'        => 'summary', //CONST_ED::SUMMARY,   // SUMMARY / SUMMARY / INLINE
        'error_display'         => 'inline', //'inline', //'summary', //CONST_ED::SUMMARY,   // SUMMARY / SUMMARY / INLINE
        'html5_validation'      => false,

        'css_form_theme_class'  => '', // "form-theme-christmas",
        'css_form_theme_file'   => '', // "christmas",
        'default_form_theme'    => '', // 'christmas' ?? 'default',

        'show_title_heading'    => true,
        'title_heading_level'         => 'h3', // Default is 'h2'
        'title_heading_class'         => null, // Use ThemeService default, or provide custom class if needed
        'form_heading_wrapper_class' => null, // Use ThemeService default, do-not-change. See note-#52

        'submit_button_variant' => 'primary',
        'cancel_button_variant' => 'secondary',



    ],
    'form_layout' => [
        [
            'title' => 'Your Favorite',
           'fields'    => [
                // 'id',
                'title',

                // 'is_verified',
                // 'generic_text',
                // 'generic_number',
                // 'status',
                // 'gender_id',
                // 'state_code',
                // 'super_powers',
                // 'telephone',
                // 'primary_email',
            ],
            'divider' => true,
        ],
        [
            'title'     => 'Your Title',
            'fields'    => [
                // 'id',
                // 'title',
                // 'status',
                // 'generic_text',
                // 'telephone',
                // 'primary_email',
                // 'super_powers',
                // ------
                // 'store_id',
                // 'user_id',
                // 'status',
                // 'slug',
                // 'content',
                // 'image_count',
                // 'cover_image_id',
                // 'generic_code',
                // 'super_powers',
                // 'date_of_birth',
                // 'generic_date',
                // 'generic_month',
                // 'generic_week',
                // 'generic_time',
                // 'generic_datetime',
                // 'gender_id',
                // 'gender_other',
                // 'is_verified',
                // 'interest_soccer_ind',
                // 'interest_baseball_ind',
                // 'interest_football_ind',
                // 'interest_hockey_ind',
                // 'secret_code_hash',
                // 'balance',
                // 'generic_decimal',
                // 'volume_level',
                // 'start_rating',
                // 'generic_number',
                // 'generic_num',
                // 'generic_color',
                // 'wake_up_time',
                // 'favorite_week_day',
                // 'online_address',
                // 'profile_picture',
                // 'created_at',
                // 'updated_at',
            ],
            'divider'   => true
        ],
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
        // 'primary_emailsssccc',
        // 'id',
        // 'store_id',
        // 'testyXxxx_user_id',
    ],
];
