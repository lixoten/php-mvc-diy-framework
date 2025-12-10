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
    //     'title_heading' => "Create Post Parent",
        'submit_text'       => "Save",
    ],
    'form_layout'            => [
        [
            'title'     => 'Your Title',
            'fields'    => [
                'username',                 // text
                'password',                 // password
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
        'base_url_enum' => Url::CORE_USER,
        'edit_url_enum' => Url::CORE_USER_EDIT,
        'list_url_enum' => Url::CORE_USER_LIST,

        'create_url_enum' => Url::CORE_USER_CREATE,
        'view_url_enum' => Url::CORE_USER_VIEW,
        'delete_url_enum' => Url::CORE_USER_DELETE, // For the POST action
        'delete_confirm_url_enum' => Url::CORE_USER_DELETE_CONFIRM, // For the GET confirmation page


        'owner_foreign_key' => 'id', // For User entity, the owner is the user themselves
        // 'redirect_after_save' => 'edit', // or 'edit'
        // 'redirect_after_save' => 'list', // Redirect to list after save
        'redirect_after_add' => 'list', // or 'edit'
        // 'redirect_after_add' => 'edit', // Redirect to edit after add
        'pageKey' => 'user_edit', // or 'edit'
        'entityName' => 'user', // or 'edit'
    ],
    'form_hidden_fields' => [
        // 'id',
        // 'store_id',
        // 'texxstyddddddddddddddddd_user_id',
    ]
];
