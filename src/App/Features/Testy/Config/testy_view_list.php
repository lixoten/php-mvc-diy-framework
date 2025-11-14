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
*     - add_button_label: string
*     - add_button_icon: string
*     - add_url: string
*     - pagination_url: string
*     - ...other render options
*/
return [
    'options' => [
        'default_sort_key' => 'created_at',
        'default_sort_direction' => 'DESC'
    ],
    'pagination' => [
        'per_page' => 12,
        'window_size' => 2, // Optional: for pagination link window
    ],
    'render_options' => [
        'title'                 =>  'list.posts.title 111',
        'show_actions'          => true,
        'show_action_add'       => true,
        'show_action_edit'      => true,
        'show_action_del'       => true,
        'show_action_view'      => true,
        'show_action_status'    => false,
    ],
    'list_fields' => [
        // 'id', 'title',  'content', 'generic_text', 'status', 'created_at'
        // 'status', 'created_at', 'id', 'store_id', 'user_id', 'title',  'content', 'is_verified', 'super_powers', 'generic_code'
        'id',
        'title',
        // 'content',
        'generic_text',
        'primary_email',         // email
        'telephone'
    ],
];
