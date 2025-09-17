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
        'layout'            => [
            [
                'title'     => 'Your Title',
                'fields'    => ['title', 'content'],
                'divider'   => true
            ],
            [
                'title' => 'Your Favorite',
                // 'fields' => ['favorite_word'],
                'fields' => ['telephone', 'date_of_birth'],
                'divider' => true,
            ],
        ],
    ],
    // Entity metadata
    'metadata' => [
        'base_url_enum' => Url::CORE_TESTY,
        'edit_url_enum' => Url::CORE_TESTY_EDIT,
        'owner_foreign_key' => 'testy_user_id',
        'redirect_after_save' => 'edit', // or 'edit'
        'redirect_after_add' => 'list', // or 'edit'
    ],
    'form_fields' => [
        // 'title', 'content', 'favorite_word', 'testy_id', 'testy_user_id'
        'title', 'content', 'telephone', 'date_of_birth', 'testy_id', 'testy_user_id'
        // 'title',
    ],
];
