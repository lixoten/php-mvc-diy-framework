<?php

/**
 * Generated File - Date: 20251114_094235
 * Field definitions for the Testy_list entity.
 *
 * This file defines how each field should be rendered in forms and lists,
 * including labels, input types, attributes, formatters, and validators.
 */

declare(strict_types=1);

return [
    'options' => [ // gen
        'default_sort_key' => 'created_at',
        'default_sort_direction' => 'DESC'
    ],
    'pagination' => [
        'per_page' => 12,
        'window_size' => 2,
    ],
    'render_options' => [
        'from'                  => 'testy_view_list-config',
        'show_title_heading'    => true,
        'title_heading_level'   => 'h2',
        'title_heading_class'   => null, // Use ThemeService default, or provide custom class if needed
        // 'title_heading_level'         => 'h3', // Default is 'h2'
        // 'heading'               => "form.heading",
        // 'heading_class'         => null, // Use ThemeService default, or provide custom class if needed

        'show_actions_label'    => true, // Table-view: Show "Actions" column header for this entity
        // 'actions_label'         => 'list.actions', // ✅ Custom translation key for this entity


        'show_actions'          => true,
        'show_action_add'       => true,
        'show_action_edit'      => true,
        'show_action_del'       => true,
        'show_action_view'      => true,
        'show_action_status'    => false,

        'show_pagination'       => true,
        'show_view_toggle'      => true,
        'view_type'             => 'table',
        // 'add_button_label'      => 'button.add',

    ],
    'list_fields' => [
        'id',
        'title',
        'generic_text',
        'status',
        'is_verified',
        'primary_email',
        'super_powers',
        'telephone',


        // 'generic_number',
        // 'gender_id',
        // 'state_code',
        // 'super_powers',
        // 'telephone',
        // 'profile_picture',



        // 'primary_emailccc',
        // ------
        // 'store_id',
        // 'user_id',
        // 'slug',
        // 'content',
        // 'image_count',
        // 'cover_image_id',
        // 'generic_code',
        // 'date_of_birth',
        // 'generic_date',
        // 'generic_month',
        // 'generic_week',
        // 'generic_time',
        // 'generic_datetime',
        // 'gender_id',
        // 'gender_other',
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
        // 'created_at',
        // 'updated_at',
    ]
];
