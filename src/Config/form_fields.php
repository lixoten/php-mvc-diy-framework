<?php

use App\Helpers\DebugRt;

DebugRt::j('1', '', 'BOOM on Config File');
// Configuration for form fields per entity type
return [
    'default' => [ // Default settings applied if not overridden
        'required' => false, // Example default
        'attributes' => [
            'class' => 'form-control', // Default class for most fields
        ],
        // Add other potential defaults like 'label_class', 'wrapper_class' etc.
    ],

    'entities' => [
        'posts' => [
            // Fields to include in the 'posts' form
            // 'fields' => ['title', 'content', 'status'], // Add 'status' or other fields as needed
            'fields' => ['title', 'content'], // Add 'status' or other fields as needed

            // Specific field definitions/overrides for 'posts'
            'definitions' => [
                'title' => [ // Overrides common 'title' from AbstractFormFieldRegistry if needed
                    'label' => 'Post Title', // Specific label
                    'required' => true,      // Override default required
                    'maxlength' => 150,      // Specific max length for posts
                    'attributes' => [
                        // 'class' => 'form-control', // Inherited from default
                        'placeholder' => 'Enter the post title here' // Specific placeholder
                    ]
                ],
                'content' => [ // Overrides common 'content'
                    'label' => 'Post Content',
                    'required' => true,
                    'maxlength' => 3000,
                    'attributes' => [
                        'rows' => '8', // Specific rows for posts
                        'placeholder' => 'Write your post content...'
                    ]
                ],
                // 'status' => [ // Example of a field not in common fields
                //     'type' => 'select',
                //     'label' => 'Status',
                //     'required' => true,
                //     'options' => [ // Options for select dropdown
                //         'Draft' => 'Draft',
                //         'Published' => 'Published',
                //         'Archived' => 'Archived',
                //     ],
                //     'attributes' => [
                //         'id' => 'status'
                //     ]
                // ],
                // Add other post-specific fields like 'category_id', 'tags', etc.
            ],
        ],
        'stores' => [
            // Fields to include in the 'posts' form
            // 'fields' => ['title', 'content', 'status'], // Add 'status' or other fields as needed
            // 'fields' => ['name', 'description', 'status'], // Add 'status' or other fields as needed
            'fields' => ['name', 'description'], // Add 'status' or other fields as needed

            // Specific field definitions/overrides for 'posts'
            'definitions' => [
                'name' => [ // Overrides common 'title' from AbstractFormFieldRegistry if needed
                    'label' => 'Store Name', // Specific label
                    'required' => true,      // Override default required
                    'maxlength' => 150,      // Specific max length for posts
                    'attributes' => [
                        // 'class' => 'form-control', // Inherited from default
                        'placeholder' => 'Enter the store name here' // Specific placeholder
                    ]
                ],
                'description' => [ // Overrides common 'content'
                    'label' => 'Store Description',
                    'required' => true,
                    'maxlength' => 3000,
                    'attributes' => [
                        'rows' => '8', // Specific rows
                        'placeholder' => 'Write your store description...'
                    ]
                ],
                // 'status' => [ // Example of a field not in common fields
                //     'type' => 'select',
                //     'label' => 'Status',
                //     'required' => true,
                //     'options' => [ // Options for select dropdown
                //         'Draft' => 'Draft',
                //         'Published' => 'Published',
                //         'Archived' => 'Archived',
                //     ],
                //     'attributes' => [
                //         'id' => 'status'
                //     ]
                // ],
                // Add other post-specific fields like 'category_id', 'tags', etc.
            ],
        ],

        'users' => [
            // Fields for the 'users' form (e.g., profile edit)
            'fields' => ['username', 'email', 'first_name', 'last_name'],

            // Specific definitions for 'users'
            'definitions' => [
                'username' => [
                    'label' => 'Username',
                    'required' => true,
                    'minlength' => 3,
                    'maxlength' => 50,
                    'attributes' => [
                        'readonly' => true // Example: Username cannot be changed
                    ]
                ],
                'email' => [ // Overrides common 'email' if you add one
                    'label' => 'Email Address',
                    'required' => true,
                    'type' => 'email', // Ensure correct type
                ],
                'first_name' => [
                    'label' => 'First Name',
                    'required' => false,
                    'maxlength' => 100,
                ],
                'last_name' => [
                    'label' => 'Last Name',
                    'required' => false,
                    'maxlength' => 100,
                ]
            ],
        ],

        // Add definitions for other entity types...
        // 'products' => [ ... ],
    ],
];
