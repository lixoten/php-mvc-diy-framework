<?php

declare(strict_types=1);

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
*     - add_button_icon: string
*     - add_url: string
*     - pagination_url: string
*     - ...other render options
*/
return [
    'render_options' => [
        'force_captcha' => true,
        'layout_type' => 'sequential', //CONST_L::SEQUENTIAL,    // FIELDSETS / SECTIONS / SEQUENTIAL
        'security_level' => 'high', //CONST_SL::LOW,      // HIGH / MEDIUM / LOW
        'error_display' => 'inline', //CONST_ED::SUMMARY,   // SUMMARY / SUMMARY / INLINE
        'html5_validation' => false,
        'c.ss_form_theme_class' => "form-theme-christmas",
        'c.ss_form_theme_file' => "christmas",
        'title_heading' => "Login NOW",
        'submit_text' => "Submit Login",
        'layout'        => [],
        // 'form_fields' => [
        //     'username', 'password', 'remember'
        //     // 'title',
        // ],

    ],
        'form_layout'            => [
        [
            'title'     => 'Your Title',
            'fields'    => [
                'username',
                'password',
                'remember',
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
];
