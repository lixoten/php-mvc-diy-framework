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
        'title'                 =>  'list.posts.title 222', //shithead2
        // 'form_heading_level'         => 'h3', // Default is 'h2'
        'heading'               => "form.heading",
        'heading_class'         => null, // Use ThemeService default, or provide custom class if needed
        'show_actions'          => true,
        'show_action_add'       => true,
        'show_action_edit'      => true,
        'show_action_del'       => true,
        'show_action_view'      => true,
        'show_action_status'    => false,

            'show_pagination'       => true,
            'show_view_toggle'      => true,
            'view_type'             => 'table',
            'add_button_label'      => 'button.add',

    ],
    'list_fields' => [
        'id',
        // 'title',
        // 'status',
        // 'is_verified',
        'generic_text',
        // 'telephone',
        'primary_email',
        'primary_emailccc',
        // ------
        // 'store_id',
        // 'user_id',
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
    ]
];
