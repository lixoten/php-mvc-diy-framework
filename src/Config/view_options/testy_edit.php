<?php

declare(strict_types=1);

use App\Enums\Url;

/*
* Options for PostsListType:
'default_sort_direction' => 'ASC', // 'ASC', 'DESC'
ListOptions::DEFAULT_SORT_KEY => PostFields2::ID->value,

* - default_sort_key: string (e.g. 'created_at')
* - pagination: array
*     - current_page: int
*     - total_pages: int
*     - total_items: int
*     - per_page: int
* - render_options: array
*     - title: string
*     - list_columns: array of strings
*     - show_actions: bool
*     - add_button_label: string
*     - add_button_icon: string
*     - add_url: string
*     - pagination_url: string
*     - ...other render options
*/
return [
    'render_options' => [
        // 'attributes'        => [
        //     'data-ajax_save'         => true,    // js-feature
        //     'data-auto_save'         => true,    // js-feature Enable auto-save/draft for the whole form
        //     'data-use_local_storage' => true,
        // ],
        'ajax_save'         => true,    // js-feature
        'auto_save'         => false,    // js-feature Enable auto-save/draft for the whole form
        'use_local_storage' => false,    // js-feature Use localStorage for drafts
        'data-ajax-save'    => true,



    //     'force_captcha' => false,
        'layout_type'       => 'sequential', //CONST_L::SEQUENTIAL,    // FIELDSETS / SECTIONS / SEQUENTIAL
    //     'security_level' => 'low', //CONST_SL::LOW,      // HIGH / MEDIUM / LOW
    //     'error_display' => 'summary', //CONST_ED::SUMMARY,   // SUMMARY / SUMMARY / INLINE
        'html5_validation'  => false,
    //     'css_form_theme_class' => "form-theme-christmas",
    //     'css_form_theme_file' => "christmas",
    //     'form_heading' => "Create Post Parent",
        'submit_text'       => "Save",
    ],
    'form_layout'            => [
        [
            'title'     => 'Your Title',
            'fields'    => [
                'title',                 // text
                // 'secret_code_hash',      // password
                // 'primary_email',         // email
                // 'online_address',        // url
                // 'telephone',             // tel

                // 'generic_date',          // date
                // 'generic_datetime',      // datetime-local
                // 'generic_month',         // month
                // 'generic_week',          // week
                // 'generic_time',          // time

                // 'generic_number',        // number
                // 'generic_decimal',       // number

                // 'volume_level',          // range int
                // 'start_rating',          // range dec


                // 'generic_color',         // color

                // 'profile_picture',

                // 'is_verified',           // checkbox
                // 'interest_soccer_ind',   // checkbox
                // 'interest_baseball_ind', // checkbox
                // 'interest_football_ind', // checkbox
                // 'interest_hockey_ind',   // checkbox

                // 'gender_id',             // select
                // 'gender_other',


                // 'balance',
                // 'date_of_birth',
                // 'favorite_week_day',
                'content',

                // 'is_boodddddvssssserified',
                // 'gender_id',
                // 'gender_other',


                // 'temperature',
                // 'my_search',                // search // Todo this had to be NOT tied to DB
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
    // Entity metadata
    'metadata' => [
        'base_url_enum' => Url::CORE_TESTY,
        'edit_url_enum' => Url::CORE_TESTY_EDIT,
        'owner_foreign_key' => 'user_id',
        'redirect_after_save' => 'edit', // or 'edit'
        'redirect_after_add' => 'list', // or 'edit'
        'pageName' => 'testy_edit', // or 'edit'
        'entityName' => 'testy', // or 'edit'
    ],
    'form_hidden_fields' => [
        // 'id',
        // 'user_id',
        // 'testyddddddddddddddddd_user_id',
    ]
];
