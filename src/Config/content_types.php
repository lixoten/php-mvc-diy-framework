<?php

declare(strict_types=1);

// Define constants for field types if you don't have them elsewhere
// defined('FIELD_TYPE_TEXT') or define('FIELD_TYPE_TEXT', 'text');
// defined('FIELD_TYPE_TEXTAREA') or define('FIELD_TYPE_TEXTAREA', 'textarea');
// defined('FIELD_TYPE_SELECT') or define('FIELD_TYPE_SELECT', 'select');
// ... etc ...

use App\Entities\Post;
use App\Entities\Store;
use App\Repository\PostRepositoryInterface;
use App\Repository\StoreRepositoryInterface;

// use App\Features\Notes\Entity\Note; // Example for another type
// use App\Features\Notes\Repository\NoteRepositoryInterface; // Example for another type

// Dynamic-me 2 // danger danger
return [
    'zzzposts' => [ // danger danger
        'repository' => PostRepositoryInterface::class,
        'entity' => Post::class,
        'label' => 'Post', // Singular label
        'label_plural' => 'Posts', // Plural label
        'base_route' => '/content/posts', // Base URL segment for routing

        // Field definitions for GenericFormType
        'fields' => [
            'title' => [
                'type' => 'text', // Corresponds to a FieldType class (e.g., TextType)
                'label' => 'xPost Title',
                'required' => true,
                'constraints' => [ // Validation constraints
                    ['type' => 'NotBlank'],
                    ['type' => 'Length', 'options' => ['max' => 150]],
                ],
                'attr' => ['placeholder' => 'Enter the post title'],
            ],
            'content' => [
                'type' => 'textarea',
                'label' => 'xContent',
                'required' => true,
                'constraints' => [
                    ['type' => 'NotBlank'],
                ],
                'attr' => ['rows' => 10],
            ],
            // 'post_status' => [ // Example status field
            //      'type' => 'select',
            //      'label' => 'Status',
            //      'required' => true,
            //      'choices' => [ // Options for select dropdown
            //          'D' => 'Draft',
            //          'P' => 'Published',
            //          // 'A' => 'Archived'
            //      ],
            //      'default' => 'D', // Default value
            // ],
            // Add other fields as needed (e.g., slug, category_id, tags)
        ],

        // Column definitions for DynamicListType (conceptual)
        'list_columns' => [
            'title' => ['label' => 'xTitle', 'sortable' => true],
            'post_status' => ['label' => 'xStatus', 'sortable' => true, /* 'formatter' => 'status_badge' */],
            'updated_at' => ['label' => 'xLast Updated', 'sortable' => true, /* 'formatter' => 'datetime' */],
        ],

        // Default render options (optional overrides for GenericFormType defaults)
        'render_options' => [
            'form_heading_add' => 'xCreate New Post',
            'submit_text_add' => 'xCreate Post',
            'form_heading_edit' => 'xEdit Post',
            'submit_text_edit' => 'xSave Changes',
            // 'layout_type' => CONST_L::FIELDSETS, // Example override
        ],

        // Default layout definition (optional override for GenericFormType default layout)
        'layout' => [
            [
                'id' => 'main_content',
                'title' => 'xPost Details',
                'fields' => ['title', 'content'],
            ],
            [
                'id' => 'metadata',
                'title' => 'xSettings',
                'fields' => ['post_status'],
            ]
        ],
    ],
    'stores' => [
        'repository' => StoreRepositoryInterface::class,
        'entity' => Store::class,
        'label' => 'Store', // Singular label
        'label_plural' => 'Stores', // Plural label
        'base_route' => '/content/stores', // Base URL segment for routing

        // Field definitions for GenericFormType
        'fields' => [
            'title' => [
                'type' => 'text', // Corresponds to a FieldType class (e.g., TextType)
                'label' => 'xPost Title',
                'required' => true,
                'constraints' => [ // Validation constraints
                    ['type' => 'NotBlank'],
                    ['type' => 'Length', 'options' => ['max' => 150]],
                ],
                'attr' => ['placeholder' => 'Enter the post title'],
            ],
            'content' => [
                'type' => 'textarea',
                'label' => 'xContent',
                'required' => true,
                'constraints' => [
                    ['type' => 'NotBlank'],
                ],
                'attr' => ['rows' => 10],
            ],
            // 'post_status' => [ // Example status field
            //      'type' => 'select',
            //      'label' => 'Status',
            //      'required' => true,
            //      'choices' => [ // Options for select dropdown
            //          'D' => 'Draft',
            //          'P' => 'Published',
            //          // 'A' => 'Archived'
            //      ],
            //      'default' => 'D', // Default value
            // ],
            // Add other fields as needed (e.g., slug, category_id, tags)
        ],

        // Column definitions for DynamicListType (conceptual)
        'list_columns' => [
            'title' => ['label' => 'xTitle', 'sortable' => true],
            'post_status' => ['label' => 'xStatus', 'sortable' => true, /* 'formatter' => 'status_badge' */],
            'updated_at' => ['label' => 'xLast Updated', 'sortable' => true, /* 'formatter' => 'datetime' */],
        ],

        // Default render options (optional overrides for GenericFormType defaults)
        'render_options' => [
            'form_heading_add' => 'Create New Post',
            'submit_text_add' => 'Create Post',
            'form_heading_edit' => 'Edit Post',
            'submit_text_edit' => 'Save Changes',
            // 'layout_type' => CONST_L::FIELDSETS, // Example override
        ],

        // Default layout definition (optional override for GenericFormType default layout)
        'layout' => [
            [
                'id' => 'main_content',
                'title' => 'Post Details',
                'fields' => ['title', 'content'],
            ],
            [
                'id' => 'metadata',
                'title' => 'Settings',
                'fields' => ['post_status'],
            ]
        ],
    ],

    // --- Example for another type: 'notes' ---
    /*
    'notes' => [
        'repository' => NoteRepositoryInterface::class,
        'entity' => Note::class,
        'label' => 'Note',
        'label_plural' => 'Notes',
        'base_route' => '/content/notes',
        'fields' => [
            'note_title' => ['type' => 'text', 'label' => 'Note Title', 'required' => true],
            'note_body' => ['type' => 'textarea', 'label' => 'Note Body', 'attr' => ['rows' => 5]],
        ],
        'list_columns' => [
            'note_title' => ['label' => 'Title'],
            'updated_at' => ['label' => 'Updated'],
        ],
        'render_options' => [
            'form_heading_add' => 'Add Note',
            'submit_text_add' => 'Add Note',
            // ...
        ],
        // No specific layout defined, will use GenericFormType default
    ],
    */
];
