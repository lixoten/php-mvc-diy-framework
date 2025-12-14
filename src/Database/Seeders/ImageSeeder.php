<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\Seeders\Seeder;
use Core\Database\ConnectionInterface;

/**
 * ferated File - Date: 20251212_181555
 * Seeder for 'image' table.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class ImageSeeder extends Seeder
{
    /**
     * @param ConnectionInterface $db The database connection.
     */
    public function __construct(ConnectionInterface $db)
    {
        parent::__construct($db);
    }

    /**
     * Seed the 'image' table with sample data.
     *
     * @return void
     */
    public function run(): void
    {
        $this->requireTable('image');

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
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'a',
                'slug' => 'velit-aspernatur-saepe-distinctio-molestias-rerum-dicta',
                'title' => 'Non tempore odio sed necessitatibus tenetur atque.',
                'generic_text' => null,
                'created_at' => '2025-01-23 23:47:26',
                'updated_at' => '2025-11-14 16:51:59',
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'a',
                'slug' => 'magni-qui-hic-eligendi',
                'title' => 'Explicabo expedita aut.',
                'generic_text' => 'Reiciendis repudiandae asperiores harum.',
                'created_at' => '2025-10-25 16:45:31',
                'updated_at' => '2025-08-24 01:14:57',
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'a',
                'slug' => 'quas-voluptas-perspiciatis-et-necessitatibus',
                'title' => 'Incidunt rem sed est et libero enim.',
                'generic_text' => 'Dicta similique minus sunt dolorem.',
                'created_at' => '2025-03-11 23:42:57',
                'updated_at' => '2025-09-12 23:45:17',
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'a',
                'slug' => 'sapiente-quaerat-consequuntur-qui',
                'title' => 'Voluptatem rerum fuga eaque exercitationem laboriosam sed.',
                'generic_text' => 'Amet nisi reprehenderit numquam officia.',
                'created_at' => '2025-07-09 14:58:20',
                'updated_at' => '2025-10-19 02:22:03',
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'a',
                'slug' => 'quasi-consequatur-iste-et-repellat',
                'title' => 'Illum sit accusamus voluptatem.',
                'generic_text' => 'Aut officia quia quisquam tenetur.',
                'created_at' => '2025-08-29 18:24:59',
                'updated_at' => '2025-10-29 18:26:31',
            ]
        ];

        $inserted = 0;
        foreach ($records as $record) {
            // Assuming 'slug' is the unique field for createIfNotExists
            $record['user_id'] = random_int(1, 6);
            $record['store_id'] = random_int(1, 14);

            //if ($this->createIfNotExists('image', $record, ['slug'])) {
            if ($this->createIfNotExists('image', $record, ['slug'])) {
                $inserted++;
            }
        }
        $countTried = count($records);
        echo "Seeded {$inserted} image records successfully out of {$countTried} records.\n";
    }
}
