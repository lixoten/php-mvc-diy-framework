<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\Seeders\Seeder;
use Core\Database\ConnectionInterface;

/**
 * Generated File - Date: 20251108_144238
 * Seeder for 'testy' table.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class TestySeeder extends Seeder
{
    /**
     * @param ConnectionInterface $db The database connection.
     */
    public function __construct(ConnectionInterface $db)
    {
        parent::__construct($db);
    }

    /**
     * Seed the 'testy' table with sample data.
     *
     * @return void
     */
    public function run(): void
    {
        $this->requireTable('testy');

        $userId = null;
        $users = $this->db->query("SELECT id FROM user LIMIT 1"); // Assuming 'user' table and 'id' column
        if (!empty($users)) {
            $userId = $users[0]['id'];
        } else {
            throw new \RuntimeException("No user found in 'user' table. Please seed users first.");
        }
        $storeId = null;
        $stores = $this->db->query("SELECT id FROM store LIMIT 1"); // Assuming 'store' table and 'id' column
        if (!empty($stores)) {
            $storeId = $stores[0]['id'];
        } else {
            throw new \RuntimeException("No store found in 'store' table. Please seed stores first.");
        }

        $records = [
            [
                'store_id' => 1,
                'user_id' => 2,
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
                'primary_email' => 'alice@example.com',
                'secret_code_hash' => 'X7F9-ALPHA-2025',
                'balance' => 98765.43,
                'generic_decimal' => 36.6,
                'generic_number' => 2,
                'volume_level' => 100,
                'start_rating' => 3.2,
                'generic_color' => 'Electric Blue',
                'wake_up_time' => '05:45:00',
                'favorite_week_day' => 'Friday',
                'online_address' => 'https://aliceblog.example.com',
                'profile_picture' => 'pictures/463280b37428348f.jpg',
                'generic_date' => '1963-11-22',
                'generic_month' => '1963-12',
                'generic_week' => '1963-W52',
                'generic_time' => '15:30:00',
                'generic_datetime' => '1963-11-22 15:30:00',
                'created_at' => '2025-11-08 14:42:37',
                'updated_at' => '2025-11-08 14:42:37',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'P',
                'slug' => 'learning-php',
                'title' => 'Learning PHP in 2025',
                'content' => 'PHP has evolved significantly over the years. Here are some tips for learning ' .
                                'PHP in 2025...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-07 14:42:37',
                'updated_at' => '2025-11-07 14:42:38',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'P',
                'slug' => 'mvc-architecture',
                'title' => 'Understanding MVC Architecture',
                'content' => 'Model-View-Controller (MVC) is a software design pattern commonly used for dev' .
                                'eloping user interfaces that divide the related program logic into three int' .
                                'erconnected elements...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-06 14:42:38',
                'updated_at' => '2025-11-06 14:42:38',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'P',
                'slug' => 'database-migrations',
                'title' => 'The Power of Database Migrations',
                'content' => 'Database migrations provide a way to reliably update your database schema and ' .
                                'apply data changes...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-05 14:42:38',
                'updated_at' => '2025-11-05 14:42:38',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'P',
                'slug' => 'web-security',
                'title' => 'Web Security Best Practices',
                'content' => 'Security is critical for any web application. Here are some best practices to ' .
                                'keep your site secure...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-04 14:42:38',
                'updated_at' => '2025-11-04 14:42:38',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'P',
                'slug' => 'rest-api-design',
                'title' => 'REST API Design Guidelines 5',
                'content' => 'When designing a REST API, it\'s important to follow these principles...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-03 14:42:38',
                'updated_at' => '2025-11-03 14:42:38',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'D',
                'slug' => 'draft-testy',
                'title' => 'This is a Draft Testy',
                'content' => 'This testy is still being drafted and should not appear in the public listing.',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-02 14:42:38',
                'updated_at' => '2025-11-02 14:42:38',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'P',
                'slug' => 'php-8-features',
                'title' => 'New Features in PHP 8',
                'content' => 'PHP 8 introduced several amazing features that every developer should know abo' .
                                'ut...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-01 14:42:38',
                'updated_at' => '2025-11-01 14:42:38',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'P',
                'slug' => 'composer-best-practices',
                'title' => 'Composer Best Practices',
                'content' => 'Learn how to make the most of Composer for managing your PHP dependencies...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-10-31 14:42:38',
                'updated_at' => '2025-10-31 14:42:38',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'P',
                'slug' => 'dependency-injection',
                'title' => 'Understanding Dependency Injection',
                'content' => 'Dependency Injection is a design pattern that implements Inversion of Control ' .
                                'for resolving dependencies...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-10-30 14:42:38',
                'updated_at' => '2025-10-30 14:42:38',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'P',
                'slug' => 'first-blog-testy2',
                'title' => 'My First Blog Testy 10',
                'content' => 'This is the content of my first blog testy. Welcome to my blog!',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-08 14:42:38',
                'updated_at' => '2025-11-08 14:42:38',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'P',
                'slug' => 'learning-php2',
                'title' => 'Learning PHP in 2025',
                'content' => 'PHP has evolved significantly over the years. Here are some tips for learning ' .
                                'PHP in 2025...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-07 14:42:38',
                'updated_at' => '2025-11-07 14:42:38',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'P',
                'slug' => 'mvc-architecture2',
                'title' => 'Understanding MVC Architectu13',
                'content' => 'Model-View-Controller (MVC) is a software design pattern commonly used for dev' .
                                'eloping user interfaces that divide the related program logic into three int' .
                                'erconnected elements...',
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
                'primary_email' => 'alice@example.com',
                'secret_code_hash' => 'X7F9-ALPHA-2025',
                'balance' => 98765.43,
                'generic_decimal' => 36.6,
                'generic_number' => 2,
                'volume_level' => 100,
                'start_rating' => 3.2,
                'generic_color' => 'Electric Blue',
                'wake_up_time' => '05:45:00',
                'favorite_week_day' => 'Friday',
                'online_address' => 'https://aliceblog.example.com',
                'generic_date' => '1963-11-22',
                'generic_month' => '1963-12',
                'generic_week' => '1963-W52',
                'generic_time' => '15:30:00',
                'generic_datetime' => '1963-11-22 15:30:00',
                'profile_picture' => 'pictures/463280b37428348f.jpg',
                'created_at' => '2025-11-06 14:42:38',
                'updated_at' => '2025-11-06 14:42:38',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'P',
                'slug' => 'database-migrations2',
                'title' => 'The Power of Database Migrations',
                'content' => 'Database migrations provide a way to reliably update your database schema and ' .
                                'apply data changes...',
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
                'primary_email' => 'alice@example.com',
                'secret_code_hash' => 'X7F9-ALPHA-2025',
                'balance' => 98765.43,
                'generic_decimal' => 36.6,
                'generic_number' => 2,
                'volume_level' => 100,
                'start_rating' => 3.2,
                'generic_color' => 'Electric Blue',
                'wake_up_time' => '05:45:00',
                'favorite_week_day' => 'Friday',
                'online_address' => 'https://aliceblog.example.com',
                'generic_date' => '1963-11-22',
                'generic_month' => '1963-12',
                'generic_week' => '1963-W52',
                'generic_time' => '15:30:00',
                'generic_datetime' => '1963-11-22 15:30:00',
                'created_at' => '2025-11-05 14:42:38',
                'updated_at' => '2025-11-05 14:42:38',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'P',
                'slug' => 'web-security2',
                'title' => 'Web Security Best Practices',
                'content' => 'Security is critical for any web application. Here are some best practices to ' .
                                'keep your site secure...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-04 14:42:38',
                'updated_at' => '2025-11-04 14:42:38',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'P',
                'slug' => 'rest-api-design2',
                'title' => 'REST API Design Guidelines',
                'content' => 'When designing a REST API, it\'s important to follow these principles...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-03 14:42:38',
                'updated_at' => '2025-11-03 14:42:38',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'D',
                'slug' => 'draft-testy2',
                'title' => 'This is a Draft Testy',
                'content' => 'This testy is still being drafted and should not appear in the public listing.',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-02 14:42:38',
                'updated_at' => '2025-11-02 14:42:38',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'P',
                'slug' => 'php-8-features2',
                'title' => 'New Features in PHP 8',
                'content' => 'PHP 8 introduced several amazing features that every developer should know abo' .
                                'ut...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-01 14:42:38',
                'updated_at' => '2025-11-01 14:42:38',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'P',
                'slug' => 'composer-best-practices2',
                'title' => 'Composer Best Practices',
                'content' => 'Learn how to make the most of Composer for managing your PHP dependencies...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-10-31 14:42:38',
                'updated_at' => '2025-10-31 14:42:38',
            ],
            [
                'store_id' => 1,
                'user_id' => 2,
                'status' => 'P',
                'slug' => 'dependency-injection2',
                'title' => 'Understanding Dependency Injection',
                'content' => 'Dependency Injection is a design pattern that implements Inversion of Control ' .
                                'for resolving dependencies...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-10-30 14:42:38',
                'updated_at' => '2025-10-30 14:42:38',
            ]
        ];

        $inserted = 0;
        foreach ($records as $record) {
            // Assuming 'slug' is the unique field for createIfNotExists
            //if ($this->createIfNotExists('testy', $record, ['slug'])) {
            if ($this->createIfNotExists('testy', $record, ['slug'])) {
                $inserted++;
            }
        }
        $countTried = count($records);
        echo "Seeded {$inserted} testy records successfully out of {$countTried} records.\n";
    }
}
