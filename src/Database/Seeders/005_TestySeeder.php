<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\Seeder\Seeder;

class TestySeeder extends Seeder
{
    public function run(): void
    {
        $this->requireTable('testy');

        // First check if we have at least one user in the system
        $users = $this->db->query("SELECT user_id FROM users LIMIT 1");

        if (empty($users)) {
            echo "Error: Can't seed testy without users. Please run the UsersSeeder first.\n";
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
        $records = [
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'P',
                'slug' => 'first-blog-testy',
                'title' => 'My First Blog Testy',
                'content' => 'This is the content of my first blog testy. Welcome to my blog!',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'gender_id' => 'm',
                'gender_other' => null,
                'is_verified' => true,
                'interest_soccer_ind' => true,
                'interest_baseball_ind' => false,
                'interest_football_ind' => true,
                'interest_hockey_ind' => false,
                'primary_email'       => 'alice@example.com',
                'secret_code_hash'         => 'X7F9-ALPHA-2025',
                'balance'             => 98765.43,
                'generic_decimal'     => 36.6,
                'generic_number'      => 2,
                'volume_level'        => 100,
                'start_rating'      => 3.2,
                'generic_color'          => 'Electric Blue',
                'wake_up_time'        => '05:45:00',
                'favorite_week_day'   => 'Friday',
                'online_address'      => 'https://aliceblog.example.com',
                'profile_picture'  => 'pictures/463280b37428348f.jpg',

                // --- NEW FIELDS ---
                // type="date" -> DATE (YYYY-MM-DD)
                'generic_date' => '1963-11-22',

                // type="month" -> VARCHAR(7) (YYYY-MM)
                'generic_month' => '1963-12',

                // type="week" -> VARCHAR(7) (YYYY-Www)
                'generic_week' => '1963-W52',

                // type="time" -> TIME (HH:MM:SS)
                'generic_time' => '15:30:00',

                // type="datetime-local" -> DATETIME (YYYY-MM-DD HH:MM:SS)
                'generic_datetime' => '1963-11-22 15:30:00',
                // ------------------
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
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
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
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
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
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
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
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'P',
                'slug' => 'rest-api-design',
                'title' => 'REST API Design Guidelines 5',
                'content' => 'When designing a REST API, it\'s important to follow these principles...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'D',
                'slug' => 'draft-testy',
                'title' => 'This is a Draft Testy',
                'content' => 'This testy is still being drafted and should not appear in the public listing.',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
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
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
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
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
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
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-9 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-9 days')),
            ],


            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'P',
                'slug' => 'first-blog-testy2',
                'title' => 'My First Blog Testy 10',
                'content' => 'This is the content of my first blog testy. Welcome to my blog!',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'P',
                'slug' => 'learning-php2',
                'title' => 'Learning PHP in 2025',
                'content' => 'PHP has evolved significantly over the years. ' .
                            'Here are some tips for learning PHP in 2025...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'P',
                'slug' => 'mvc-architecture2',
                'title' => 'Understanding MVC Architectu13',
                'content' => 'Model-View-Controller (MVC) is a software design pattern commonly used for developing ' .
                          'user interfaces that divide the related program logic into three interconnected elements...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',

                'gender_id'           => 'm',
                'gender_other'        => null,
                'is_verified'         => true,
                'interest_soccer_ind' => true,
                'interest_baseball_ind' => false,
                'interest_football_ind' => true,
                'interest_hockey_ind'   => false,

                'primary_email'       => 'alice@example.com',
                'secret_code_hash'         => 'X7F9-ALPHA-2025',
                'balance'             => 98765.43,
                'generic_decimal'       => 36.6,
                'generic_number'        => 2,
                'volume_level'          => 100,
                'start_rating'          => 3.2,
                'generic_color'          => 'Electric Blue',
                'wake_up_time'        => '05:45:00',
                'favorite_week_day'   => 'Friday',
                'online_address'      => 'https://aliceblog.example.com',

                'generic_date'      => '1963-11-22',
                'generic_month'     => '1963-12',
                'generic_week'      => '1963-W52',
                'generic_time'      => '15:30:00',
                'generic_datetime'  => '1963-11-22 15:30:00',
                'profile_picture'  => 'pictures/463280b37428348f.jpg',

                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            ],
            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'P',
                'slug' => 'database-migrations2',
                'title' => 'The Power of Database Migrations',
                'content' => 'Database migrations provide a way to reliably update your database schema ' .
                            'and apply data changes...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',

                'gender_id'           => 'm',
                'gender_other'        => null,
                'is_verified'         => true,
                'interest_soccer_ind' => true,
                'interest_baseball_ind' => false,
                'interest_football_ind' => true,
                'interest_hockey_ind'   => false,

                'primary_email'       => 'alice@example.com',
                'secret_code_hash'         => 'X7F9-ALPHA-2025',
                'balance'             => 98765.43,
                'generic_decimal'         => 36.6,
                'generic_number'      => 2,
                'volume_level'        => 100,
                'start_rating'        => 3.2,
                'generic_color'       => 'Electric Blue',
                'wake_up_time'        => '05:45:00',
                'favorite_week_day'   => 'Friday',
                'online_address'      => 'https://aliceblog.example.com',

                'generic_date' => '1963-11-22',
                'generic_month' => '1963-12',
                'generic_week' => '1963-W52',
                'generic_time' => '15:30:00',
                'generic_datetime' => '1963-11-22 15:30:00',

                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
            ],
            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'P',
                'slug' => 'web-security2',
                'title' => 'Web Security Best Practices',
                'content' => 'Security is critical for any web application. ' .
                            'Here are some best practices to keep your site secure...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
            ],
            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'P',
                'slug' => 'rest-api-design2',
                'title' => 'REST API Design Guidelines',
                'content' => 'When designing a REST API, it\'s important to follow these principles...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            ],
            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'D',
                'slug' => 'draft-testy2',
                'title' => 'This is a Draft Testy',
                'content' => 'This testy is still being drafted and should not appear in the public listing.',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
            ],
            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'P',
                'slug' => 'php-8-features2',
                'title' => 'New Features in PHP 8',
                'content' => 'PHP 8 introduced several amazing features that every developer should know about...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
            ],
            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'P',
                'slug' => 'composer-best-practices2',
                'title' => 'Composer Best Practices',
                'content' => 'Learn how to make the most of Composer for managing your PHP dependencies...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-8 days')),
            ],
            [
                'store_id' => $storeStoreId,
                'user_id' => $storeUserId,
                'status' => 'P',
                'slug' => 'dependency-injection2',
                'title' => 'Understanding Dependency Injection',
                'content' => 'Dependency Injection is a design pattern that implements Inversion of Control ' .
                            'for resolving dependencies...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => date('Y-m-d H:i:s', strtotime('-9 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-9 days')),
            ],
        ];

        $inserted = 0;
        // Insert all sample testys
        foreach ($records as $record) {
            if ($this->createIfNotExists('testy', $record, ['slug'])) {
                $inserted++;
            }
        }
        $countTried = count($records);
        echo "Seeded {$inserted} testy records successfully out of {$countTried} records.\n";
    }
}
