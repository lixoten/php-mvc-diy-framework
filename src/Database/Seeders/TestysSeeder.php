<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\Seeder\Seeder;

class TestysSeeder extends Seeder
{
    public function run(): void
    {
        // First check if we have at least one user in the system
        $users = $this->db->query("SELECT user_id FROM users LIMIT 1");

        if (empty($users)) {
            echo "Error: Can't seed testys without users. Please run the UsersSeeder first.\n";
            return;
        }

        $userId = $users[0]['user_id'];

        // Get a store or create one if it doesn't exist
        $stores = $this->db->query("SELECT store_id FROM stores WHERE store_user_id = :user_id LIMIT 1", [
            ':user_id' => $userId
        ]);

        $storeId = null;

        if (empty($stores)) {
            // Create a test store
            $storeId = $this->db->insert('stores', [
                'store_user_id' => $userId,
                'store_status' => 'A',
                'slug' => 'test-store',
                'name' => 'Test Store',
                'description' => 'A store created for testing purposes',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            echo "Created test store with ID: {$storeId}\n";
        } else {
            $storeId = $stores[0]['store_id'];
        }


        $storeUserId = 4;  // 'storeJohn' id is 4
        $storeStoreId = 4; // 'storeJohn has a store: 'john-stamps' id is 4 also

        // Sample testy data
        $testys = [
            [
                'testy_store_id' => $storeId,
                'testy_user_id' => $userId,
                'testy_status' => 'P',
                'slug' => 'first-blog-testy',
                'title' => 'My First Blog Testy',
                'content' => 'This is the content of my first blog testy. Welcome to my blog!',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'testy_store_id' => $storeId,
                'testy_user_id' => $userId,
                'testy_status' => 'P',
                'slug' => 'learning-php',
                'title' => 'Learning PHP in 2025',
                'content' => 'PHP has evolved significantly over the years. ' .
                            'Here are some tips for learning PHP in 2025...',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
            [
                'testy_store_id' => $storeId,
                'testy_user_id' => $userId,
                'testy_status' => 'P',
                'slug' => 'mvc-architecture',
                'title' => 'Understanding MVC Architecture',
                'content' => 'Model-View-Controller (MVC) is a software design pattern commonly used for developing ' .
                          'user interfaces that divide the related program logic into three interconnected elements...',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            ],
            [
                'testy_store_id' => $storeId,
                'testy_user_id' => $userId,
                'testy_status' => 'P',
                'slug' => 'database-migrations',
                'title' => 'The Power of Database Migrations',
                'content' => 'Database migrations provide a way to reliably update your database schema ' .
                            'and apply data changes...',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
            ],
            [
                'testy_store_id' => $storeId,
                'testy_user_id' => $userId,
                'testy_status' => 'P',
                'slug' => 'web-security',
                'title' => 'Web Security Best Practices',
                'content' => 'Security is critical for any web application. ' .
                            'Here are some best practices to keep your site secure...',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
            ],
            [
                'testy_store_id' => $storeId,
                'testy_user_id' => $userId,
                'testy_status' => 'P',
                'slug' => 'rest-api-design',
                'title' => 'REST API Design Guidelines',
                'content' => 'When designing a REST API, it\'s important to follow these principles...',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            ],
            [
                'testy_store_id' => $storeId,
                'testy_user_id' => $userId,
                'testy_status' => 'D',
                'slug' => 'draft-testy',
                'title' => 'This is a Draft Testy',
                'content' => 'This testy is still being drafted and should not appear in the public listing.',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
            ],
            [
                'testy_store_id' => $storeId,
                'testy_user_id' => $userId,
                'testy_status' => 'P',
                'slug' => 'php-8-features',
                'title' => 'New Features in PHP 8',
                'content' => 'PHP 8 introduced several amazing features that every developer should know about...',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
            ],
            [
                'testy_store_id' => $storeId,
                'testy_user_id' => $userId,
                'testy_status' => 'P',
                'slug' => 'composer-best-practices',
                'title' => 'Composer Best Practices',
                'content' => 'Learn how to make the most of Composer for managing your PHP dependencies...',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
            ],
            [
                'testy_store_id' => $storeId,
                'testy_user_id' => $userId,
                'testy_status' => 'P',
                'slug' => 'dependency-injection',
                'title' => 'Understanding Dependency Injection',
                'content' => 'Dependency Injection is a design pattern that implements Inversion of Control ' .
                            'for resolving dependencies...',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-9 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-9 days')),
            ],


            [
                'testy_store_id' => $storeStoreId,
                'testy_user_id' => $storeUserId,
                'testy_status' => 'P',
                'slug' => 'first-blog-testy2',
                'title' => 'My First Blog Testy',
                'content' => 'This is the content of my first blog testy. Welcome to my blog!',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'testy_store_id' => $storeStoreId,
                'testy_user_id' => $storeUserId,
                'testy_status' => 'P',
                'slug' => 'learning-php2',
                'title' => 'Learning PHP in 2025',
                'content' => 'PHP has evolved significantly over the years. ' .
                            'Here are some tips for learning PHP in 2025...',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
            [
                'testy_store_id' => $storeStoreId,
                'testy_user_id' => $storeUserId,
                'testy_status' => 'P',
                'slug' => 'mvc-architecture2',
                'title' => 'Understanding MVC Architecture',
                'content' => 'Model-View-Controller (MVC) is a software design pattern commonly used for developing ' .
                          'user interfaces that divide the related program logic into three interconnected elements...',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            ],
            [
                'testy_store_id' => $storeStoreId,
                'testy_user_id' => $storeUserId,
                'testy_status' => 'P',
                'slug' => 'database-migrations2',
                'title' => 'The Power of Database Migrations',
                'content' => 'Database migrations provide a way to reliably update your database schema ' .
                            'and apply data changes...',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
            ],
            [
                'testy_store_id' => $storeStoreId,
                'testy_user_id' => $storeUserId,
                'testy_status' => 'P',
                'slug' => 'web-security2',
                'title' => 'Web Security Best Practices',
                'content' => 'Security is critical for any web application. ' .
                            'Here are some best practices to keep your site secure...',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
            ],
            [
                'testy_store_id' => $storeStoreId,
                'testy_user_id' => $storeUserId,
                'testy_status' => 'P',
                'slug' => 'rest-api-design2',
                'title' => 'REST API Design Guidelines',
                'content' => 'When designing a REST API, it\'s important to follow these principles...',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            ],
            [
                'testy_store_id' => $storeStoreId,
                'testy_user_id' => $storeUserId,
                'testy_status' => 'D',
                'slug' => 'draft-testy2',
                'title' => 'This is a Draft Testy',
                'content' => 'This testy is still being drafted and should not appear in the public listing.',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
            ],
            [
                'testy_store_id' => $storeStoreId,
                'testy_user_id' => $storeUserId,
                'testy_status' => 'P',
                'slug' => 'php-8-features2',
                'title' => 'New Features in PHP 8',
                'content' => 'PHP 8 introduced several amazing features that every developer should know about...',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
            ],
            [
                'testy_store_id' => $storeStoreId,
                'testy_user_id' => $storeUserId,
                'testy_status' => 'P',
                'slug' => 'composer-best-practices2',
                'title' => 'Composer Best Practices',
                'content' => 'Learn how to make the most of Composer for managing your PHP dependencies...',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
            ],
            [
                'testy_store_id' => $storeStoreId,
                'testy_user_id' => $storeUserId,
                'testy_status' => 'P',
                'slug' => 'dependency-injection2',
                'title' => 'Understanding Dependency Injection',
                'content' => 'Dependency Injection is a design pattern that implements Inversion of Control ' .
                            'for resolving dependencies...',
                'favorite_word' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-9 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-9 days')),
            ],
        ];

        // Insert all sample testys
        foreach ($testys as $testy) {
            $this->createIfNotExists('testys', $testy, ['slug']);
        }

        echo "Seeded 10 testys successfully.\n";
    }
}
