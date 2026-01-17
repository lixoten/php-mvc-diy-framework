<?php

declare(strict_types=1);

$storeId = '$storeId';
$userId = '$userId';
$storeId = 1;
$userId = 2; // 'john.store'

return [
    // Entity Metadata
    'entity' => [
        'name'          => 'PendingImageUpload',
        'table'         => 'pending_image_upload',
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
        'upload_token' => [
            'db_type'   => 'string',
            'length'    => 36,
            'unique'    => true,
            'nullable'  => false,
            'comment'   => 'upload_token',
        ],
        'store_id'      => [
            'db_type' => 'foreignId',
            'nullable' => true,
            'comment' => 'Store this record belongs to',
            'foreign_key' => [
                'table' => 'store',
                'column' => 'id',
                'name' => 'fk_store',
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
                'name'      => 'fk_user',
                'on_delete' => 'CASCADE',
            ],
        ],
        'temp_path' => [
            'db_type' => 'string',
            'length'    => 500,
            'nullable'  => false,
            'comment'   => 'temp_path',
        ],
        'original_filename' => [
            'db_type' => 'string',
            'length'    => 255,
            'nullable'  => false,
            'comment'   => 'original_filename',
        ],
        'client_mime_type' => [
            'db_type' => 'string',
            'length'    => 50,
            'nullable'  => false,
            'comment'   => 'client_mime_type',
        ],
        'file_size_bytes' => [
            'db_type' => 'bigIncrements',
            'nullable'  => false,
            'comment'   => 'original_filename',
        ],
        'created_at' => [
            'db_type'   => 'dateTime',
            'nullable'  => false,
            'comment'   => 'Created Date',
        ],
        'updated_at' => [
            'db_type'   => 'dateTime',
            'nullable'  => false,
            'comment'   => 'Last update',
        ],
    ],

    // Additional Indexes
    'indexes' => [
        [
            'name' => 'idx_expires_at',
            'columns' => ['expires_at'],
            'type' => 'index',
        ],
        [
            'name' => 'idx_user_store',
            'columns' => ['user_id', 'store_id'],
            'type' => 'unique',
        ],
    ],

];
