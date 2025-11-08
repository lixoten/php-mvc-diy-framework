<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\Seeders\Seeder;
use Core\Database\ConnectionInterface;

/**
 * Generated File - Date: 20251102_091429
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
                'username' => 'john.store',
                'email' => 'john.store@example.com',
                'password_hash' => '$2y$10$8ypg7psjo4naut.0j9wTw.5oKrY.L6acNRtRHi1q3XFFWnXbJeCoi',
                'roles' => '["store_owner", "admin", "user"]',
                'status' => 'A',
                'activation_token' => 'some_activation_token_1234567890abcdef',
                'reset_token' => null,
                'reset_token_expiry' => null,
                'created_at' => '2025-10-31 09:14:29',
                'updated_at' => '2025-10-31 09:14:29',
            ],
            [
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password_hash' => '$2y$10$8ypg7psjo4naut.0j9wTw.5oKrY.L6acNRtRHi1q3XFFWnXbJeCoi',
                'roles' => '["store_owner", "user"]',
                'status' => 'A',
                'activation_token' => null,
                'reset_token' => null,
                'reset_token_expiry' => null,
                'created_at' => '2025-11-02 09:14:29',
                'updated_at' => '2025-11-02 09:14:29',
            ],
            [
                'username' => 'john.doe',
                'email' => 'john.doe@example.com',
                'password_hash' => '$2y$10$8ypg7psjo4naut.0j9wTw.5oKrY.L6acNRtRHi1q3XFFWnXbJeCoi',
                'roles' => '["store_owner"]',
                'status' => 'A',
                'activation_token' => null,
                'reset_token' => null,
                'reset_token_expiry' => null,
                'created_at' => '2025-11-01 09:14:29',
                'updated_at' => '2025-11-01 09:14:29',
            ],
            [
                'username' => 'jane.smith',
                'email' => 'jane.smith@example.com',
                'password_hash' => '$2y$10$8ypg7psjo4naut.0j9wTw.5oKrY.L6acNRtRHi1q3XFFWnXbJeCoi',
                'roles' => '["store_owner"]',
                'status' => 'P',
                'activation_token' => 'some_activation_token_1234567890abcdef',
                'reset_token' => null,
                'reset_token_expiry' => null,
                'created_at' => '2025-10-31 09:14:29',
                'updated_at' => '2025-10-31 09:14:29',
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
