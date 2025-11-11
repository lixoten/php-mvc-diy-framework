<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\Seeders\Seeder;
use Core\Database\ConnectionInterface;

/**
 * Generated File - Date: 20251109_203439
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
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'P',
                'slug' => 'first-blog-testy6',
                'title' => 'My First Blog Testy6',
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
                'created_at' => '2025-11-09 20:34:39',
                'updated_at' => '2025-11-09 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'P',
                'slug' => 'learning-php6',
                'title' => 'Learning PHP in 20256',
                'content' => 'PHP has evolved significantly over the years. Here are some tips for learning ' .
                                'PHP in 2025...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-08 20:34:39',
                'updated_at' => '2025-11-08 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'P',
                'slug' => 'mvc-architecture6',
                'title' => 'Understanding MVC Architecture6',
                'content' => 'Model-View-Controller (MVC) is a software design pattern commonly used for dev' .
                                'eloping user interfaces that divide the related program logic into three int' .
                                'erconnected elements...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-07 20:34:39',
                'updated_at' => '2025-11-07 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'P',
                'slug' => 'database-migrations6',
                'title' => 'The Power of Database Migrations6',
                'content' => 'Database migrations provide a way to reliably update your database schema and ' .
                                'apply data changes...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-06 20:34:39',
                'updated_at' => '2025-11-06 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'P',
                'slug' => 'web-security6',
                'title' => 'Web Security Best Practices6',
                'content' => 'Security is critical for any web application. Here are some best practices to ' .
                                'keep your site secure...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-05 20:34:39',
                'updated_at' => '2025-11-05 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'P',
                'slug' => 'rest-api-design6',
                'title' => 'REST API Design Guidelines 56',
                'content' => 'When designing a REST API, it\'s important to follow these principles...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-04 20:34:39',
                'updated_at' => '2025-11-04 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'D',
                'slug' => 'draft-testy6',
                'title' => 'This is a Draft Testy6',
                'content' => 'This testy is still being drafted and should not appear in the public listing.',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-03 20:34:39',
                'updated_at' => '2025-11-03 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'P',
                'slug' => 'php-8-features6',
                'title' => 'New Features in PHP 86',
                'content' => 'PHP 8 introduced several amazing features that every developer should know abo' .
                                'ut...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-02 20:34:39',
                'updated_at' => '2025-11-02 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'P',
                'slug' => 'composer-best-practices6',
                'title' => 'Composer Best Practices6',
                'content' => 'Learn how to make the most of Composer for managing your PHP dependencies...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-01 20:34:39',
                'updated_at' => '2025-11-01 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'P',
                'slug' => 'dependency-injection6',
                'title' => 'Understanding Dependency Injection6',
                'content' => 'Dependency Injection is a design pattern that implements Inversion of Control ' .
                                'for resolving dependencies...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-10-31 20:34:39',
                'updated_at' => '2025-10-31 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'P',
                'slug' => 'first-blog-testy26',
                'title' => 'My First Blog Testy 106',
                'content' => 'This is the content of my first blog testy. Welcome to my blog!',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-09 20:34:39',
                'updated_at' => '2025-11-09 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'P',
                'slug' => 'learning-php26',
                'title' => 'Learning PHP in 20256',
                'content' => 'PHP has evolved significantly over the years. Here are some tips for learning ' .
                                'PHP in 2025...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-08 20:34:39',
                'updated_at' => '2025-11-08 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'P',
                'slug' => 'mvc-architecture26',
                'title' => 'Understanding MVC Architectu136',
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
                'created_at' => '2025-11-07 20:34:39',
                'updated_at' => '2025-11-07 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'P',
                'slug' => 'database-migrations26',
                'title' => 'The Power of Database Migrations6',
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
                'created_at' => '2025-11-06 20:34:39',
                'updated_at' => '2025-11-06 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'P',
                'slug' => 'web-security26',
                'title' => 'Web Security Best Practices6',
                'content' => 'Security is critical for any web application. Here are some best practices to ' .
                                'keep your site secure...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-05 20:34:39',
                'updated_at' => '2025-11-05 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'P',
                'slug' => 'rest-api-design26',
                'title' => 'REST API Design Guidelines6',
                'content' => 'When designing a REST API, it\'s important to follow these principles...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-04 20:34:39',
                'updated_at' => '2025-11-04 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'D',
                'slug' => 'draft-testy26',
                'title' => 'This is a Draft Testy6',
                'content' => 'This testy is still being drafted and should not appear in the public listing.',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-03 20:34:39',
                'updated_at' => '2025-11-03 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'P',
                'slug' => 'php-8-features26',
                'title' => 'New Features in PHP 86',
                'content' => 'PHP 8 introduced several amazing features that every developer should know abo' .
                                'ut...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-02 20:34:39',
                'updated_at' => '2025-11-02 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'P',
                'slug' => 'composer-best-practices26',
                'title' => 'Composer Best Practices6',
                'content' => 'Learn how to make the most of Composer for managing your PHP dependencies...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-11-01 20:34:39',
                'updated_at' => '2025-11-01 20:34:39',
            ],
            [
                'store_id' => 2,
                'user_id' => 6,
                'status' => 'P',
                'slug' => 'dependency-injection26',
                'title' => 'Understanding Dependency Injection6',
                'content' => 'Dependency Injection is a design pattern that implements Inversion of Control ' .
                                'for resolving dependencies...',
                'generic_text' => 'Hello',
                'date_of_birth' => '1990-01-01',
                'telephone' => '+1-555-123-4567',
                'created_at' => '2025-10-31 20:34:39',
                'updated_at' => '2025-10-31 20:34:39',
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
