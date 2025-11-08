<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\Seeders\Seeder;
use Core\Database\ConnectionInterface;

/**
 * Generated File - Date: 20251102_093148
 * Seeder for 'store' table.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class StoreSeeder extends Seeder
{
    /**
     * @param ConnectionInterface $db The database connection.
     */
    public function __construct(ConnectionInterface $db)
    {
        parent::__construct($db);
    }

    /**
     * Seed the 'store' table with sample data.
     *
     * @return void
     */
    public function run(): void
    {
        $this->requireTable('store');

        $userId = null;
        $users = $this->db->query("SELECT id FROM user LIMIT 1"); // Assuming 'user' table and 'id' column
        if (!empty($users)) {
            $userId = $users[0]['id'];
        } else {
            throw new \RuntimeException("No user found in 'user' table. Please seed users first.");
        }

        $records = [
            [
                'user_id' => 1,
                'status' => 'A',
                'slug' => 'my-first-store',
                'name' => 'My First Awesome Store',
                'description' => 'This is the description for my first store. We sell amazing things!',
                'theme' => 'default',
                'created_at' => '2025-11-02 09:31:48',
                'updated_at' => '2025-11-02 09:31:48',
            ],
            [
                'user_id' => 1,
                'status' => 'I',
                'slug' => 'another-great-shop',
                'name' => 'Another Great Shop',
                'description' => 'A placeholder store that is currently inactive.',
                'theme' => 'minimal',
                'created_at' => '2025-11-01 09:31:48',
                'updated_at' => '2025-11-01 09:31:48',
            ],
            [
                'user_id' => 1,
                'status' => 'S',
                'slug' => 'suspended-boutique',
                'name' => 'Suspended Boutique',
                'description' => 'This store is temporarily suspended due to policy violations.',
                'theme' => 'default',
                'created_at' => '2025-10-31 09:31:48',
                'updated_at' => '2025-10-31 09:31:48',
            ],
            [
                'user_id' => 1,
                'status' => 'S',
                'slug' => 'john-stamps',
                'name' => 'John Stamps',
                'description' => 'This store sssssssss is temporarily suspended due to policy violations.',
                'theme' => 'default',
                'created_at' => '2025-10-31 09:31:48',
                'updated_at' => '2025-10-31 09:31:48',
            ]
        ];

        $inserted = 0;
        foreach ($records as $record) {
            // Assuming 'slug' is the unique field for createIfNotExists
            //if ($this->createIfNotExists('store', $record, ['slug'])) {
            if ($this->createIfNotExists('store', $record, ['slug'])) {
                $inserted++;
            }
        }
        $countTried = count($records);
        echo "Seeded {$inserted} store records successfully out of {$countTried} records.\n";
    }
}
