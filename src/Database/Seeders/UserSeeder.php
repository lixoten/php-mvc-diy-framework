<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\Seeders\Seeder;
use Core\Database\ConnectionInterface;

/**
 * Generated File - Date: 20251109_201423
 * Seeder for 'user' table.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class UserSeeder extends Seeder
{
    /**
     * @param ConnectionInterface $db The database connection.
     */
    public function __construct(ConnectionInterface $db)
    {
        parent::__construct($db);
    }

    /**
     * Seed the 'user' table with sample data.
     *
     * @return void
     */
    public function run(): void
    {
        $this->requireTable('user');


        $records = [
            [
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password_hash' => '$2y$10$s681/VHRjFXNnUjfPx9wBe/OWAcO6v5lpmUzde5ZshhGQq3V8oeZS',
                'roles' => '["admin"]',
                'status' => 'A',
                'activation_token' => null,
                'reset_token' => null,
                'reset_token_expiry' => null,
                'created_at' => '2025-11-09 20:14:23',
                'updated_at' => '2025-11-09 20:14:23',
            ],
            [
                'username' => 'john.store',
                'email' => 'john.store@example.com',
                'password_hash' => '$2y$10$s681/VHRjFXNnUjfPx9wBe/OWAcO6v5lpmUzde5ZshhGQq3V8oeZS',
                'roles' => '["store_owner", "admin"]',
                'status' => 'A',
                'activation_token' => 'some_activation_token_1234567890abcdef',
                'reset_token' => null,
                'reset_token_expiry' => null,
                'created_at' => '2025-11-07 20:14:23',
                'updated_at' => '2025-11-07 20:14:23',
            ],
            [
                'username' => 'mary.store',
                'email' => 'mary.store@example.com',
                'password_hash' => '$2y$10$s681/VHRjFXNnUjfPx9wBe/OWAcO6v5lpmUzde5ZshhGQq3V8oeZS',
                'roles' => '["store_owner"]',
                'status' => 'A',
                'activation_token' => 'some_activation_token_1234567890abcdef',
                'reset_token' => null,
                'reset_token_expiry' => null,
                'created_at' => '2025-11-07 20:14:23',
                'updated_at' => '2025-11-07 20:14:23',
            ],
            [
                'username' => 'john.doe',
                'email' => 'john.doe@example.com',
                'password_hash' => '$2y$10$s681/VHRjFXNnUjfPx9wBe/OWAcO6v5lpmUzde5ZshhGQq3V8oeZS',
                'roles' => '["store_owner"]',
                'status' => 'A',
                'activation_token' => null,
                'reset_token' => null,
                'reset_token_expiry' => null,
                'created_at' => '2025-11-08 20:14:23',
                'updated_at' => '2025-11-08 20:14:23',
            ],
            [
                'username' => 'jane.doe',
                'email' => 'jane.doe@example.com',
                'password_hash' => '$2y$10$s681/VHRjFXNnUjfPx9wBe/OWAcO6v5lpmUzde5ZshhGQq3V8oeZS',
                'roles' => '["user"]',
                'status' => 'P',
                'activation_token' => 'some_activation_token_1234567890abcdef',
                'reset_token' => null,
                'reset_token_expiry' => null,
                'created_at' => '2025-11-07 20:14:23',
                'updated_at' => '2025-11-07 20:14:23',
            ],
            [
                'username' => 'joe.guest',
                'email' => 'joe.guest@guest.com',
                'password_hash' => '$2y$10$s681/VHRjFXNnUjfPx9wBe/OWAcO6v5lpmUzde5ZshhGQq3V8oeZS',
                'roles' => '["guest"]',
                'status' => 'A',
                'activation_token' => 'some_activation_token_1234567890abcdef',
                'reset_token' => null,
                'reset_token_expiry' => null,
                'created_at' => '2025-11-07 20:14:23',
                'updated_at' => '2025-11-07 20:14:23',
            ]
        ];

        $inserted = 0;
        foreach ($records as $record) {
            // Assuming 'slug' is the unique field for createIfNotExists
            //if ($this->createIfNotExists('user', $record, ['slug'])) {
            if ($this->createIfNotExists('user', $record, ['username', 'email'])) {
                $inserted++;
            }
        }
        $countTried = count($records);
        echo "Seeded {$inserted} user records successfully out of {$countTried} records.\n";
    }
}
