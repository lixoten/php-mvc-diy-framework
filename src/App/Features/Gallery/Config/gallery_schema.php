<?php

declare(strict_types=1);

$storeId = '$storeId';
$userId = '$userId';
$storeId = 1;
$userId = 2; // 'john.store'

return [
    // Entity Metadata
    'entity' => [
        'name' => 'Gallery',
        'table' => 'gallery',
        'timestamps' => true,
    ],

    // Field Definitions
    'fields' => [
        'id' => [
            'db_type' => 'bigIncrements',
            'primary' => true,
            'auto_increment' => true,
            'comment' => 'Primary key for the gallery.',
        ],
        'store_id' => [
            'db_type' => 'foreignId',
            'nullable' => true,
            'comment' => 'Store this record belongs to',
            'foreign_key' => [
                'table' => 'store',
                'column' => 'id',
                'name' => 'fk_gallery_store',
                'on_delete' => 'CASCADE',
            ],
        ],
        'user_id' => [
            'db_type' => 'foreignId',
            'nullable' => true,
            'comment' => 'Foreign key to the user who created the gallery.',
            'foreign_key' => [
                'table' => 'user',
                'column' => 'id',
                'name' => 'fk_gallery_user',
                'on_delete' => 'CASCADE', // onDelete('set null');
            ],
        ],
        'status' => [
            'db_type' => 'char',
            'length' => 1,
            'nullable' => false,
            'default' => 'P',
            'comment' => 'P=Pending, A=Active, I=Inactive, D=Draft, R-Archived',
                        'check' => "status IN ('P', 'A', 'I', 'D', 'R')", // Added CHECK constraint definition

        ],
        'name' => [
            'db_type' => 'string',
            'length' => 255,
            'nullable' => false,
            'comment' => 'The display name of the gallery.',
        ],
        'slug' => [
            'db_type' => 'string',
            'length' => 255,
            'nullable' => false,
            // 'unique' => true,
            'comment' => 'URL-friendly slug for the gallery.',
        ],
        'description' => [
            'db_type' => 'text',
            'nullable' => true,
            'comment' => 'A detailed description of the gallery.',
        ],
        'image_count' => [
            'db_type' => 'integer',
            'nullable' => true,
            'comment' => 'Image Count',
        ],
        'cover_image_id' => [
            'db_type' => 'foreignId',
            'nullable' => true,
            // 'unsigned' => true,
            'comment' => 'Optional cover image id',
            'foreign_key' => [
                'table' => 'image',
                'column' => 'id',
                'name' => 'fk_gallery_cover_image',
                'on_delete' => 'SET NULL',
            ],
        ],
    ],

    // Additional Indexes
    'indexes' => [
        [
            'name' => 'idx_status',
            'columns' => ['status'],
            'type' => 'index',
        ],
        [
            'name' => 'unique_slug_gallery',
            'columns' => ['slug', 'store_id'],
            'type' => 'unique',
        ],
    ],

    // Repository Configuration
    // 'repository' => [
        // 'extends' => 'AbstractMultiTenantRepository',
    // ],
    'repository' => [
        'extends' => 'AbstractMultiTenantRepository',
        'implements' => ['GalleryRepositoryInterface', 'BaseRepositoryInterface'],
        'queries2' => [
            'findById' => "SELECT i.*, u.username
                FROM {tableName} i
                LEFT JOIN user u ON i.user_id = u.user_id
                WHERE i.{primaryKey} = :id",
            'findBy' => "SELECT i.*, u.username
                FROM {tableName} i
                LEFT JOIN user u ON i.user_id = u.user_id",
        ],
        'queries' => [
            'findById' => [
                'fromype' => 'LEFT',
                'type' => 'LEFT',
                'on' => 'i.user_id = u.user_id',
                'alias' => 'u',
                'select' => ['username'],
            ],
        ],
        'joins' => [
            'user' => [
                'type' => 'LEFT',
                'on' => 'i.user_id = u.user_id',
                'alias' => 'u',
                'select' => ['username'],
            ],
        ],
    ],

    // Controller Configuration
    'controller' => [
        'route_context' => 'account',
    ],
    'sample_data' => [
        [
            'store_id' => $storeId,
            'user_id' => $userId,
            'status' => 'P',
            'name' => 'My First Gallery',
            'slug' => 'my-first-gallery',
            'description' => 'A collection of my initial photos.',
            'image_count' => 5,
            'cover_image_id' => null,
        ],
    ],
];
