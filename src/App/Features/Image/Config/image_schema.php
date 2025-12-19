<?php

declare(strict_types=1);

$storeId = '$storeId';
$userId = '$userId';
$storeId = 1;
$userId = 2; // 'john.store'

return [
    // Entity Metadata
    'entity' => [
        'name'          => 'Image',
        'table'         => 'image',
        'timestamps'    => false,
        'seeder_count'  => 5,
    ],

    // Field Definitions
    'fields' => [
        'id' => [
            'db_type'   => 'bigIncrements',
            'primary'   => true,
            'auto_increment' => true,
            'comment'   => 'ID',
        ],
        'store_id'      => [
            'db_type' => 'foreignId',
            'nullable' => true,
            'comment' => 'Store this record belongs to',
            'foreign_key' => [
                'table' => 'store',
                'column' => 'id',
                'name' => 'fk_image_store',
                'on_delete' => 'CASCADE',
            ],
        ],
        'user_id' => [
            'db_type'   => 'foreignId',
            'nullable'  => false,
            'comment'   => 'User who created the record',
            'foreign_key' => [
                'table'     => 'user',
                'column'    => 'id',
                'name'      => 'fk_image_user',
                'on_delete' => 'CASCADE',
            ],
        ],
        'status' => [
            'db_type'   => 'enum',
            'default'   => 'a',
            'comment'   => 'Status',
            'comment2'  => 'P=Pending, A=Active, S=Suspended, B=Banned, D=Deleted',
            'check'     => "status IN ('p','a','s','b','d')", // Using CHECK constraint as per instructions
            'lookup'    => 'image_status',
            'enum_class' => 'ImageStatus',
            'codes'     => [
                'P'  => 'Pending',
                'a'  => 'Active',
                's'  => 'Suspended',
                'b' => 'Banned',
                'd' => 'Deleted',
            ],
        ],
        'title' => [
            'db_type' => 'string',
            'length'    => 255,
            'nullable'  => false,
            'comment'   => 'Title',
            'required'  => true,
            'minlength' => 5,
            'maxlength' => 50,
            // 'pattern'   => '[a-z0-9]/',
            // 'style'     => 'background: cyan;',
            // 'data-char-counter'    => true,
            // 'data-live-validation' => true,
        ],
        'slug' => [
            'db_type'   => 'string',
            'length'    => 100,
            'nullable'  => false,
            'unique'    => true,
            'comment'   => 'Slug',
            'comment2'   => 'Unique SEO-friendly slug for the image',
        ],
        'description' => [
            'db_type' => 'text',
            'nullable'  => false,
            'comment'   => 'Description',
            'required'  => true,
            'minlength' => 5,
        ],
        'filename' => [
            'db_type' => 'string',
            'length'    => 255,
            'nullable'  => true,
            'comment'   => 'Hash filename',
        ],

        'original_filename' => [
            'db_type' => 'string',
            'length'    => 255,
            'nullable'  => true,
            'comment'   => 'Original filename',
        ],

        'mime_type' => [
            'db_type' => 'string',
            'length' => 50,
            'nullable'  => true,
            'comment' => 'MIME type (e.g., image/jpeg)',
        ],
        'file_size_bytes' => [
            'db_type' => 'bigInteger',
            'nullable'  => true,
            'comment' => 'File size in bytes',
        ],
        'width' => [
            'db_type' => 'integer',
            'nullable' => true,
            'comment' => 'Original image width in pixels',
        ],
        'height' => [
            'db_type' => 'integer',
            'nullable' => true,
            'comment' => 'Original image height in pixels',
        ],
        'focal_point' => [
            'db_type' => 'json',
            'nullable' => true,
            'comment' => 'Smart crop focal point (e.g., {"x":0.5,"y":0.3})',
        ],
        'is_optimized' => [
            'db_type' => 'boolean',
            'default' => false,
            'comment' => 'Whether the image has been optimized',
        ],
        'checksum' => [
            'db_type' => 'string',
            'length' => 64,
            'nullable' => true,
            'comment' => 'Optional file checksum for integrity',
        ],
        'alt_text' => [
            'db_type' => 'string',
            'length' => 255,
            'nullable' => true,
            'comment' => 'Accessibility alt text',
        ],
        'license' => [
            'db_type' => 'string',
            'length' => 100,
            'nullable' => true,
            'comment' => 'Usage license',
        ],
        'created_at' => [
            'db_type'   => 'dateTime',
            'nullable'  => false,
            'comment'   => 'Created Date',
            'comment2'  => 'Timestamp when the record was created',
        ],
        'updated_at' => [
            'db_type'   => 'dateTime',
            'nullable'  => false,
            'comment'   => 'Last update',
            'comment2'  => 'Timestamp when the record was last updated',
        ],
        'deleted_at' => [
            'db_type'   => 'dateTime',
            'nullable'  => true,
            'comment'   => 'Last update',
            'comment2'  => 'Timestamp when the record was last updated',
        ]
    ],

    // Additional Indexes
    'indexes' => [
        [
            'name' => 'idx_status',
            'columns' => ['status'],
            'type' => 'index',
        ],
        [
            'name' => 'unique_slug_store',
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
        'implements' => ['ImageRepositoryInterface', 'BaseRepositoryInterface'],
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
];
