<?php

declare(strict_types=1);

// Placeholders for dynamic values, typically resolved at runtime (e.g., in seeders)
$storeUserId = '$storeUserId'; // Example: will be replaced with an actual user ID
$storeUserId = 3; //fixme

return [
    // Entity Metadata
    'entity' => [
        'name' => 'Store', // Singular name for the entity
        'table' => 'store', // Singular table name
        'timestamps' => true, // Automatically adds created_at and updated_at
    ],

    // Field Definitions
    'fields' => [
        'id' => [
            'db_type' => 'bigIncrements',
            'primary' => true,
            'auto_increment' => true,
        ],
        'user_id' => [
            'db_type' => 'foreignId', // Use foreignId for consistency with schema_testy
            'nullable' => false,
            'unsigned' => true, // From migration
            'comment' => 'User who owns this store',
            'foreign_key' => [
                'table' => 'user', // Singular table name for users
                'column' => 'id', // Assuming 'id' is the primary key of the user table
                'name' => 'fk_store_user', // Constraint name
                'on_delete' => 'CASCADE',
            ],
        ],
        'status' => [
            'db_type' => 'char',
            'length' => 1,
            'nullable' => false,
            'default' => 'I',
            'comment' => 'I=Inactive, A=Active, S=Suspended',
            'check' => "status IN ('I','A','S')", // CHECK constraint for allowed values
        ],
        'slug' => [
            'db_type' => 'string',
            'length' => 50,
            'nullable' => false,
            'unique' => true, // From migration
            'comment' => 'Unique SEO-friendly slug for the store',
        ],
        'name' => [
            'db_type' => 'string',
            'length' => 100,
            'nullable' => false,
            'comment' => 'Name of the store',
        ],
        'description' => [
            'db_type' => 'text',
            'nullable' => true,
            'comment' => 'Description of the store',
        ],
        'theme' => [
            'db_type' => 'string',
            'length' => 50,
            'nullable' => false,
            'default' => 'default',
            'comment' => 'Theme used by the store',
        ],
        // 'created_at' and 'updated_at' are handled by 'timestamps' => true in entity metadata
    ],

    // Additional Indexes
    'indexes' => [
        [
            'name' => 'idx_user_id',
            'columns' => ['user_id'],
            'type' => 'index',
        ],
        // 'slug' is already unique, so an additional index might be redundant but included for consistency if desired
        // [
        //     'name' => 'idx_slug',
        //     'columns' => ['slug'],
        //     'type' => 'index',
        // ],
        [
            'name' => 'idx_status',
            'columns' => ['status'],
            'type' => 'index',
        ],
    ],

    // Repository Configuration
    'repository' => [
        'extends' => 'AbstractMultiTenantRepository', // Assuming stores are multi-tenant
        'implements' => ['StoreRepositoryInterface', 'BaseRepositoryInterface'],
        'queries' => [
            'findById' => "SELECT s.*, u.username
                FROM {tableName} s
                LEFT JOIN user u ON s.user_id = u.id
                WHERE s.{primaryKey} = :id",
            'findBy' => "SELECT s.*, u.username
                FROM {tableName} s
                LEFT JOIN user u ON s.user_id = u.id",
        ],
        'joins' => [
            'user' => [ // Singular table name
                'type' => 'LEFT',
                'on' => 't.user_id = u.id',
                'alias' => 'u',
                'select' => ['username'],
            ],
        ],
    ],

    // Controller Configuration
    'controller' => [
        'route_context' => 'account', // Example context
    ],

    // Sample Data for Seeding
    'sample_data' => [
        [
            'user_id' => $storeUserId, // This will still use the fixed $storeUserId for existing samples
            'status' => 'A',
            'slug' => 'my-first-store',
            'name' => 'My First Awesome Store',
            'description' => 'This is the description for my first store. We sell amazing things!',
            'theme' => 'default',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ],
        [
            'user_id' => $storeUserId,
            'status' => 'I',
            'slug' => 'another-great-shop',
            'name' => 'Another Great Shop',
            'description' => 'A placeholder store that is currently inactive.',
            'theme' => 'minimal',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        ],
        [
            'user_id' => $storeUserId,
            'status' => 'S',
            'slug' => 'suspended-boutique',
            'name' => 'Suspended Boutique',
            'description' => 'This store is temporarily suspended due to policy violations.',
            'theme' => 'default',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        ],
        [
            'user_id' => $storeUserId,
            'status' => 'S',
            'slug' => 'john-stamps',
            'name' => 'John Stamps',
            'description' => 'This store sssssssss is temporarily suspended due to policy violations.',
            'theme' => 'default',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        ],
        // --- Additional 10 Sample Records ---
        [
            'user_id' => 4, // store.admin
            'status' => 'A',
            'slug' => 'tech-gadget-hub',
            'name' => 'Tech Gadget Hub',
            'description' => 'Your one-stop shop for the latest electronics and gadgets.',
            'theme' => 'modern',
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
        ],
        [
            'user_id' => 2, //john.store
            'status' => 'A',
            'slug' => 'eco-friendly-living',
            'name' => 'Eco-Friendly Living',
            'description' => 'Sustainable products for a greener lifestyle.',
            'theme' => 'green',
            'created_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
        ],
        [
            'user_id' => 3,
            'status' => 'I',
            'slug' => 'vintage-treasures',
            'name' => 'Vintage Treasures',
            'description' => 'Curated collection of antique and vintage items.',
            'theme' => 'classic',
            'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
        ],
        [
            'user_id' => 4,
            'status' => 'A',
            'slug' => 'artisan-crafts-co',
            'name' => 'Artisan Crafts Co.',
            'description' => 'Handmade goods from local artists.',
            'theme' => 'rustic',
            'created_at' => date('Y-m-d H:i:s', strtotime('-12 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-12 days')),
        ],
        [
            'user_id' => 4,
            'status' => 'A',
            'slug' => 'bookworm-haven',
            'name' => 'Bookworm Haven',
            'description' => 'A cozy place for book lovers to find their next read.',
            'theme' => 'library',
            'created_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
        ],
        [
            'user_id' => 4,
            'status' => 'S',
            'slug' => 'fashion-forward-hub',
            'name' => 'Fashion Forward Hub',
            'description' => 'Trendy apparel and accessories for all seasons.',
            'theme' => 'chic',
            'created_at' => date('Y-m-d H:i:s', strtotime('-18 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-18 days')),
        ],
        [
            'user_id' => 4,
            'status' => 'A',
            'slug' => 'pet-paradise-store',
            'name' => 'Pet Paradise Store',
            'description' => 'Everything your furry, scaly, or feathered friends need.',
            'theme' => 'animal',
            'created_at' => date('Y-m-d H:i:s', strtotime('-20 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-20 days')),
        ],
        [
            'user_id' => 4,
            'status' => 'I',
            'slug' => 'gourmet-food-emporium',
            'name' => 'Gourmet Food Emporium',
            'description' => 'Fine foods and delicacies from around the world.',
            'theme' => 'foodie',
            'created_at' => date('Y-m-d H:i:s', strtotime('-22 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-22 days')),
        ],
        [
            'user_id' => 4,
            'status' => 'A',
            'slug' => 'home-decor-delights',
            'name' => 'Home Decor Delights',
            'description' => 'Transform your living space with unique decorations.',
            'theme' => 'interior',
            'created_at' => date('Y-m-d H:i:s', strtotime('-25 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-25 days')),
        ],
        [
            'user_id' => 4,
            'status' => 'A',
            'slug' => 'fitness-gear-pro',
            'name' => 'Fitness Gear Pro',
            'description' => 'High-quality equipment for your fitness journey.',
            'theme' => 'sporty',
            'created_at' => date('Y-m-d H:i:s', strtotime('-28 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-28 days')),
        ],
    ],
];
