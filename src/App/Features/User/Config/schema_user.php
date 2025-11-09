<?php

declare(strict_types=1);
    $defaultPassword = (string)password_hash('password', PASSWORD_DEFAULT);

return [
    // Entity Metadata
    'entity' => [
        'name' => 'User',
        'table' => 'user',
        'timestamps' => false,
    ],

    // Field Definitions
    'fields' => [
        'id' => [
            'db_type' => 'bigIncrements',
            'primary' => true,
            'auto_increment' => true,
        ],
        'username' => [
            'db_type' => 'string',
            'length' => 50,
            'nullable' => false,
            'unique' => true,
            'comment' => 'Unique username for login',
        ],
        'email' => [
            'db_type' => 'string',
            'length' => 255,
            'nullable' => false,
            'unique' => true,
            'comment' => 'Unique email address for login and communication',
        ],
        'password_hash' => [
            'db_type' => 'string',
            'length' => 255,
            'nullable' => false,
            'comment' => 'Hashed password for user authentication',
        ],
        'roles' => [
            'db_type' => 'array',
            'nullable' => false,
            // 'default' => [],
            'comment' => 'JSON encoded array of user roles/permissions',
        ],
        'status' => [
            'db_type' => 'enum',
            // 'length' => 1,
            // 'nullable' => false,
            'default' => 'A',
            'comment' => 'P=Pending, A=Active, S=Suspended, B=Banned, D=Deleted',
            'check' => "status IN ('P','A','S','B','D')", // Using CHECK constraint as per instructions
        ],
        'activation_token' => [
            'db_type' => 'string',
            'length' => 64,
            'nullable' => true,
            'comment' => 'Token for account activation',
        ],
        'reset_token' => [
            'db_type' => 'string',
            'length' => 64,
            'nullable' => true,
            'comment' => 'Token for password reset',
        ],
        'reset_token_expiry' => [
            'db_type' => 'timestamp',
            'nullable' => true,
            'comment' => 'Expiry time for password reset token',
        ],
        'is_green' => [
            'db_type' => 'boolean',
            'nullable' => false,
            'default' => false,
            'comment' => 'Is Green',
        ],
        'is_blue' => [
            'db_type' => 'boolean',
            'nullable' => false,
            'default' => false,
            'comment' => 'Is Blue',
        ],
        'is_red' => [
            'db_type' => 'boolean',
            'nullable' => false,
            'default' => false,
            'comment' => 'Is Red',
        ],
        'generic_code' => [
            'db_type' => 'string',
            'nullable' => false,
            'comment' => 'Generic Code',
        ],
        'created_at' => [
            'db_type' => 'dateTime',
            'nullable' => false,
            'comment' => 'Timestamp when the record was created',
        ],
        'updated_at' => [
            'db_type' => 'dateTime',
            'nullable' => false,
            'comment' => 'Timestamp when the record was last updated',
        ]
    ],

    // Additional Indexes
    'indexes' => [
        [
            'name' => 'idx_user_username',
            'columns' => ['username'],
            'type' => 'index',
        ],
        [
            'name' => 'idx_user_email',
            'columns' => ['email'],
            'type' => 'index',
        ],
        [
            'name' => 'idx_user_status',
            'columns' => ['status'],
            'type' => 'index',
        ],
        [
            'name' => 'idx_user_activation_token',
            'columns' => ['activation_token'],
            'type' => 'index',
        ],
        [
            'name' => 'idx_user_reset_token',
            'columns' => ['reset_token'],
            'type' => 'index',
        ],
    ],

    // Repository Configuration
    'repository' => [
        'extends' => 'AbstractRepository', // User is typically not multi-tenant itself
        'extends' => 'AbstractMultiTenantRepository', // User is typically not multi-tenant itself
        // 'implements' => ['UserRepositoryInterface', 'BaseRepositoryInterface'],
        'queries' => [
            'findById2' => "SELECT u.* FROM {tableName} u WHERE u.{primaryKey} = :id",
            'findById' => "SELECT *
                           FROsM {tableName} u WHERE {primaryKey} = :id",
            'findBy' => "SELECT u.* FROM {tableName} u",
        ],
        'joins' => [], // No joins for the user entity itself in basic queries
    ],

    // Controller Configuration
    'controller' => [
        'route_context' => 'core', // User management is a core function
    ],

    // Sample Data
    'sample_data' => [
        [ // 1
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => $defaultPassword,
            'roles' => '["admin"]',
            'status' => 'A',
            'activation_token' => null,
            'reset_token' => null,
            'reset_token_expiry' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ],
        [ // 2
            'username' => 'john.store',
            'email' => 'john.store@example.com',
            'password_hash' => $defaultPassword,
            'roles' => '["store_owner", "admin"]',
            'status' => 'A',
            'activation_token' => 'some_activation_token_1234567890abcdef',
            'reset_token' => null,
            'reset_token_expiry' => null,
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        ],
        [ // 3
            'username' => 'mary.store',
            'email' => 'mary.store@example.com',
            'password_hash' => $defaultPassword,
            'roles' => '["store_owner"]',
            'status' => 'A',
            'activation_token' => 'some_activation_token_1234567890abcdef',
            'reset_token' => null,
            'reset_token_expiry' => null,
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        ],
        [ // 4
            'username' => 'john.doe',
            'email' => 'john.doe@example.com',
            'password_hash' => $defaultPassword,
            'roles' => '["store_owner"]',
            'status' => 'A',
            'activation_token' => null,
            'reset_token' => null,
            'reset_token_expiry' => null,
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        ],
        [ // 5
            'username' => 'jane.doe',
            'email' => 'jane.doe@example.com',
            'password_hash' => $defaultPassword,
            'roles' => '["user"]',
            'status' => 'P', // Pending activation
            'activation_token' => 'some_activation_token_1234567890abcdef',
            'reset_token' => null,
            'reset_token_expiry' => null,
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        ],
        [ // 6
            'username' => 'joe.guest',
            'email' => 'joe.guest@guest.com',
            'password_hash' => $defaultPassword,
            'roles' => '["guest"]',
            'status' => 'A',
            'activation_token' => 'some_activation_token_1234567890abcdef',
            'reset_token' => null,
            'reset_token_expiry' => null,
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        ],
    ],
];
