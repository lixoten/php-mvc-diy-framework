<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\Seeder\Seeder;

class StoresSeeder extends Seeder
{
    public function run(): void
    {
        // First check if we have user with ID 1 in the system
        $user = $this->db->query("SELECT user_id FROM users WHERE user_id = 1 LIMIT 1");

        if (empty($user)) {
            echo "Error: Can't seed stores without user ID 1. Please run the UsersSeeder first.\n";
            return;
        }

        $userId = 1; // Explicitly assigning to user ID 1

        // Sample store data
        $stores = [
            [
                'store_user_id' => $userId,
                'store_status' => 'A', // Active
                'slug' => 'digital-emporium',
                'name' => 'Digital Emporium',
                'description' => 'A premium store for all your digital needs. Featuring software, e-books, digital art, and more. Quality digital products for every digital enthusiast.',
                'theme' => 'modern',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'store_user_id' => $userId,
                'store_status' => 'I', // Inactive
                'slug' => 'craft-corner',
                'name' => 'Craft Corner',
                'description' => 'Handmade crafts, artisanal goods, and unique creations. Each product is carefully crafted with love and attention to detail. Find one-of-a-kind items for your home or as special gifts.',
                'theme' => 'vintage',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
            [
                'store_user_id' => 2,
                'store_status' => 'A',
                'slug' => 'john-stamps',
                'name' => 'John Stamps',
                'description' => 'Handmade stamps, artisanal goods, and unique creations. Each product is carefully crafted with love and attention to detail. Find one-of-a-kind items for your home or as special gifts.',
                'theme' => 'vintage',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
        ];

        // Insert sample stores
        $anyInserted = false;
        foreach ($stores as $store) {
            if ($this->createIfNotExists('stores', $store, ['slug'])) {
                $anyInserted = true;
            }
        }

        if ($anyInserted === 0) {
            echo "No new stores were seeded. All sample stores already exist.\n";
        } else {
            echo "Seeded 2 stores successfully.\n";
        }
    }
}
