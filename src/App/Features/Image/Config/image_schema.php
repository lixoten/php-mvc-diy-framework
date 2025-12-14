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
            // 'db_type'   => 'char',
            'db_type'   => 'enum',
            // 'length'    => 1,
            // 'nullable'  => false,
            'default'   => 'a',
            'comment'   => 'Status',
            'comment2'  => 'P=Pending, A=Active, S=Suspended, B=Banned, D=Deleted',
            'check'     => "status IN ('j', 'p','a','s','b','d')", // Using CHECK constraint as per instructions
            // 'comment'   => 'P=Pending, A=Active, I=Inactive',
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
        'slug' => [
            'db_type'   => 'string',
            'length'    => 100,
            'nullable'  => false,
            'unique'    => true, // From migration
            'comment'   => 'Unique SEO-friendly slug for the image',
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
        'generic_text' => [
            'db_type'   => 'string',
            'length'    => 60,
            'nullable'  => true,
            'comment'   => 'Generic text',
            'comment2'  => 'Generic short text',
            'required'  => true,
            'minlength' => 5,
            'maxlength' => 50,
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
