<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\Seeders\Seeder;
use Core\Database\ConnectionInterface;

/**
 * Generated File - Date: 20251108_110713
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
                'user_id' => 3,
                'status' => 'A',
                'slug' => 'my-first-store',
                'name' => 'My First Awesome Store',
                'description' => 'This is the description for my first store. We sell amazing things!',
                'theme' => 'default',
                'created_at' => '2025-11-08 11:07:13',
                'updated_at' => '2025-11-08 11:07:13',
            ],
            [
                'user_id' => 3,
                'status' => 'I',
                'slug' => 'another-great-shop',
                'name' => 'Another Great Shop',
                'description' => 'A placeholder store that is currently inactive.',
                'theme' => 'minimal',
                'created_at' => '2025-11-07 11:07:13',
                'updated_at' => '2025-11-07 11:07:13',
            ],
            [
                'user_id' => 3,
                'status' => 'S',
                'slug' => 'suspended-boutique',
                'name' => 'Suspended Boutique',
                'description' => 'This store is temporarily suspended due to policy violations.',
                'theme' => 'default',
                'created_at' => '2025-11-06 11:07:13',
                'updated_at' => '2025-11-06 11:07:13',
            ],
            [
                'user_id' => 3,
                'status' => 'S',
                'slug' => 'john-stamps',
                'name' => 'John Stamps',
                'description' => 'This store sssssssss is temporarily suspended due to policy violations.',
                'theme' => 'default',
                'created_at' => '2025-11-06 11:07:13',
                'updated_at' => '2025-11-06 11:07:13',
            ],
            [
                'user_id' => 4,
                'status' => 'A',
                'slug' => 'tech-gadget-hub',
                'name' => 'Tech Gadget Hub',
                'description' => 'Your one-stop shop for the latest electronics and gadgets.',
                'theme' => 'modern',
                'created_at' => '2025-11-03 11:07:13',
                'updated_at' => '2025-11-03 11:07:13',
            ],
            [
                'user_id' => 2,
                'status' => 'A',
                'slug' => 'eco-friendly-living',
                'name' => 'Eco-Friendly Living',
                'description' => 'Sustainable products for a greener lifestyle.',
                'theme' => 'green',
                'created_at' => '2025-11-01 11:07:13',
                'updated_at' => '2025-11-01 11:07:13',
            ],
            [
                'user_id' => 3,
                'status' => 'I',
                'slug' => 'vintage-treasures',
                'name' => 'Vintage Treasures',
                'description' => 'Curated collection of antique and vintage items.',
                'theme' => 'classic',
                'created_at' => '2025-10-29 11:07:13',
                'updated_at' => '2025-10-29 11:07:13',
            ],
            [
                'user_id' => 4,
                'status' => 'A',
                'slug' => 'artisan-crafts-co',
                'name' => 'Artisan Crafts Co.',
                'description' => 'Handmade goods from local artists.',
                'theme' => 'rustic',
                'created_at' => '2025-10-27 11:07:13',
                'updated_at' => '2025-10-27 11:07:13',
            ],
            [
                'user_id' => 3,
                'status' => 'A',
                'slug' => 'bookworm-haven',
                'name' => 'Bookworm Haven',
                'description' => 'A cozy place for book lovers to find their next read.',
                'theme' => 'library',
                'created_at' => '2025-10-24 11:07:13',
                'updated_at' => '2025-10-24 11:07:13',
            ],
            [
                'user_id' => 2,
                'status' => 'S',
                'slug' => 'fashion-forward-hub',
                'name' => 'Fashion Forward Hub',
                'description' => 'Trendy apparel and accessories for all seasons.',
                'theme' => 'chic',
                'created_at' => '2025-10-21 11:07:13',
                'updated_at' => '2025-10-21 11:07:13',
            ],
            [
                'user_id' => 4,
                'status' => 'A',
                'slug' => 'pet-paradise-store',
                'name' => 'Pet Paradise Store',
                'description' => 'Everything your furry, scaly, or feathered friends need.',
                'theme' => 'animal',
                'created_at' => '2025-10-19 11:07:13',
                'updated_at' => '2025-10-19 11:07:13',
            ],
            [
                'user_id' => 3,
                'status' => 'I',
                'slug' => 'gourmet-food-emporium',
                'name' => 'Gourmet Food Emporium',
                'description' => 'Fine foods and delicacies from around the world.',
                'theme' => 'foodie',
                'created_at' => '2025-10-17 11:07:13',
                'updated_at' => '2025-10-17 11:07:13',
            ],
            [
                'user_id' => 4,
                'status' => 'A',
                'slug' => 'home-decor-delights',
                'name' => 'Home Decor Delights',
                'description' => 'Transform your living space with unique decorations.',
                'theme' => 'interior',
                'created_at' => '2025-10-14 11:07:13',
                'updated_at' => '2025-10-14 11:07:13',
            ],
            [
                'user_id' => 2,
                'status' => 'A',
                'slug' => 'fitness-gear-pro',
                'name' => 'Fitness Gear Pro',
                'description' => 'High-quality equipment for your fitness journey.',
                'theme' => 'sporty',
                'created_at' => '2025-10-11 11:07:13',
                'updated_at' => '2025-10-11 11:07:13',
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
