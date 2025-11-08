<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\Seeder\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // First check if we have at least one user in the system
        $users = $this->db->query("SELECT user_id FROM users LIMIT 1");

        if (empty($users)) {
            echo "Error: Can't seed posts without users. Please run the UsersSeeder first.\n";
            return;
        }

        $userId = $users[0]['user_id'];

        // Get a store or create one if it doesn't exist
        $store = $this->db->query("SELECT store_id FROM store WHERE store_user_id = :user_id LIMIT 1", [
            ':user_id' => $userId
        ]);

        $storeId = null;

        if (empty($store)) {
            // Create a test store
            $storeId = $this->db->insert('store', [
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
            $storeId = $store[0]['store_id'];
        }


        $storeUserId = 4;  // 'storeJohn' id is 4
        $storeStoreId = 4; // 'storeJohn has a store: 'john-stamps' id is 4 also

        // Sample post data
        $posts = [
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'P',
                'slug' => 'first-blog-post',
                'title' => 'My First Blog Post',
                'content' => 'This is the content of my first blog post. Welcome to my blog!',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'P',
                'slug' => 'learning-php',
                'title' => 'Learning PHP in 2025',
                'content' => 'PHP has evolved significantly over the years. ' .
                            'Here are some tips for learning PHP in 2025...',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'P',
                'slug' => 'mvc-architecture',
                'title' => 'Understanding MVC Architecture',
                'content' => 'Model-View-Controller (MVC) is a software design pattern commonly used for developing ' .
                          'user interfaces that divide the related program logic into three interconnected elements...',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'P',
                'slug' => 'database-migrations',
                'title' => 'The Power of Database Migrations',
                'content' => 'Database migrations provide a way to reliably update your database schema ' .
                            'and apply data changes...',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'P',
                'slug' => 'web-security',
                'title' => 'Web Security Best Practices',
                'content' => 'Security is critical for any web application. ' .
                            'Here are some best practices to keep your site secure...',
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'P',
                'slug' => 'rest-api-design',
                'title' => 'REST API Design Guidelines',
                'content' => 'When designing a REST API, it\'s important to follow these principles...',
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'D',
                'slug' => 'draft-post',
                'title' => 'This is a Draft Post',
                'content' => 'This post is still being drafted and should not appear in the public listing.',
                'created_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'P',
                'slug' => 'php-8-features',
                'title' => 'New Features in PHP 8',
                'content' => 'PHP 8 introduced several amazing features that every developer should know about...',
                'created_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'P',
                'slug' => 'composer-best-practices',
                'title' => 'Composer Best Practices',
                'content' => 'Learn how to make the most of Composer for managing your PHP dependencies...',
                'created_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'P',
                'slug' => 'dependency-injection',
                'title' => 'Understanding Dependency Injection',
                'content' => 'Dependency Injection is a design pattern that implements Inversion of Control ' .
                            'for resolving dependencies...',
                'created_at' => date('Y-m-d H:i:s', strtotime('-9 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-9 days')),
            ],


            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'P',
                'slug' => 'first-blog-post',
                'title' => 'My First Blog Post',
                'content' => 'This is the content of my first blog post. Welcome to my blog!',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'P',
                'slug' => 'learning-php',
                'title' => 'Learning PHP in 2025',
                'content' => 'PHP has evolved significantly over the years. ' .
                            'Here are some tips for learning PHP in 2025...',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'P',
                'slug' => 'mvc-architecture',
                'title' => 'Understanding MVC Architecture',
                'content' => 'Model-View-Controller (MVC) is a software design pattern commonly used for developing ' .
                          'user interfaces that divide the related program logic into three interconnected elements...',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            ],
            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'P',
                'slug' => 'database-migrations',
                'title' => 'The Power of Database Migrations',
                'content' => 'Database migrations provide a way to reliably update your database schema ' .
                            'and apply data changes...',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
            ],
            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'P',
                'slug' => 'web-security',
                'title' => 'Web Security Best Practices',
                'content' => 'Security is critical for any web application. ' .
                            'Here are some best practices to keep your site secure...',
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
            ],
            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'P',
                'slug' => 'rest-api-design',
                'title' => 'REST API Design Guidelines',
                'content' => 'When designing a REST API, it\'s important to follow these principles...',
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            ],
            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'D',
                'slug' => 'draft-post',
                'title' => 'This is a Draft Post',
                'content' => 'This post is still being drafted and should not appear in the public listing.',
                'created_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
            ],
            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'P',
                'slug' => 'php-8-features',
                'title' => 'New Features in PHP 8',
                'content' => 'PHP 8 introduced several amazing features that every developer should know about...',
                'created_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
            ],
            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'P',
                'slug' => 'composer-best-practices',
                'title' => 'Composer Best Practices',
                'content' => 'Learn how to make the most of Composer for managing your PHP dependencies...',
                'created_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
            ],
            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'P',
                'slug' => 'dependency-injection',
                'title' => 'Understanding Dependency Injection',
                'content' => 'Dependency Injection is a design pattern that implements Inversion of Control ' .
                            'for resolving dependencies...',
                'created_at' => date('Y-m-d H:i:s', strtotime('-9 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-9 days')),
            ],
        ];

        // Insert all sample posts
        foreach ($posts as $post) {
            $this->createIfNotExists('post', $post, ['slug']);
        }

        echo "Seeded 10 posts successfully.\n";
    }
}
