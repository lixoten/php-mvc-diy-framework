<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\Seeders\Seeder;
use Core\Database\ConnectionInterface;

/**
 * Generated File - Date: 20251109_204049
 * Seeder for 'gallery' table.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class GallerySeeder extends Seeder
{
    /**
     * @param ConnectionInterface $db The database connection.
     */
    public function __construct(ConnectionInterface $db)
    {
        parent::__construct($db);
    }

    /**
     * Seed the 'gallery' table with sample data.
     *
     * @return void
     */
    public function run(): void
    {
        $this->requireTable('gallery');

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
                'name' => 'My First Gallery',
                'slug' => 'my-first-gallery',
                'description' => 'A collection of my initial photos.',
                'image_count' => 5,
                'cover_image_id' => null,
            ]
        ];

        $inserted = 0;
        foreach ($records as $record) {
            // Assuming 'slug' is the unique field for createIfNotExists
            //if ($this->createIfNotExists('gallery', $record, ['slug'])) {
            if ($this->createIfNotExists('gallery', $record, [])) {
                $inserted++;
            }
        }
        $countTried = count($records);
        echo "Seeded {$inserted} gallery records successfully out of {$countTried} records.\n";
    }
}
