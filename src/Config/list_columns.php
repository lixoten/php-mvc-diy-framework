<?php

// Dynamic-me 3
// Configuration for list columns per entity type
return [
    'default' => [ // Default settings applied if not overridden by entity type
        'sortable' => true,
        'formatter' => 'htmlspecialchars', // Use a simple string for common formatters
    ],

    'formatters' => [ // Define reusable formatters (optional)
        'htmlspecialchars' => function ($value) {
            return htmlspecialchars((string)($value ?? ''));
        },
        'author' => function ($value) {
            return htmlspecialchars($value ?? 'Unknown');
        },
        // Add other common formatters like 'datetime', 'currency' etc.
    ],

    'entities' => [
        'posts' => [
            // Columns to display for 'posts' list
            'display' => ['id', 'title', 'user_id', 'username', 'status', 'created_at'],
            // Specific column definitions for 'posts'
            'columns' => [
                'id' => [
                    'label' => 'ecfgID',
                ],
                'user_id' => [
                    'label' => 'ecfgUserId',
                ],
                'id' => [
                    'label' => 'ecfgID',
                ],
                'xxxstatus' => [
                    'label' => 'ecfgStatus',
                ],
                'created_at' => [
                    'label' => 'ecfgCreated_at',
                ],
                'title' => [ // Overrides default if needed, inherits 'sortable' from default
                    'label' => 'ecfgPost Title',
                    // 'formatter' => 'htmlspecialchars' // Inherited from default
                ],
                'xxxusername' => [
                    'label' => 'ecfgAuthor',
                    'formatter' => 'author' // Reference a defined formatter
                ],
                // 'id', 'status', 'created_at' will use definitions from AbstractFieldRegistry::getCommonColumn
            ],
        ],
        'stores' => [
            // Columns to display for 'stores' list
            'display' => ['id', 'name', 'username', 'status', 'created_at'],
            // Specific column definitions for 'stores'
            'columns' => [
                'name' => [ // Overrides default if needed, inherits 'sortable' from default
                    'label' => 'Store Name',
                    // 'formatter' => 'htmlspecialchars' // Inherited from default
                ],
                'username' => [
                    'label' => 'User Name',
                    // 'formatter' => 'author' // Reference a defined formatter
                ],
                // 'id', 'status', 'created_at' will use definitions from AbstractFieldRegistry::getCommonColumn
            ],
        ],

        'users' => [
            // Columns to display for 'users' list
            'display' => ['id', 'username', 'email', 'status', 'created_at'],
            // Specific column definitions for 'users'
            'columns' => [
                'username' => [
                    'label' => 'Username',
                ],
                'email' => [
                    'label' => 'Email Address',
                    'sortable' => false, // Override default sortable
                ],
                // 'id', 'status', 'created_at' will use definitions from AbstractFieldRegistry::getCommonColumn
            ],
        ],

        // Add definitions for other entity types...
    ],
];