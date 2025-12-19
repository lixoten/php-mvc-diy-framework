<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\Seeders\Seeder;
use Core\Database\ConnectionInterface;

/**
 * ferated File - Date: 20251217_163019
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
                'title' => 'Praesentium praesentium quae nisi rerum aut.',
                'slug' => 'illo-et-sit-similique-ut-omnis',
                'description' => 'Voluptatum iure laborum officiis dolor aut. Quisquam hic accusantium in officia nihil ipsa officia.',
                'filename' => null,
                'original_filename' => null,
                'mime_type' => null,
                'file_size_bytes' => null,
                'width' => null,
                'height' => null,
                'focal_point' => null,
                'is_optimized' => false,
                'checksum' => null,
                'alt_text' => null,
                'license' => null,
                'created_at' => '2025-02-25 06:26:52',
                'updated_at' => '2025-03-22 19:41:00',
                'deleted_at' => '2025-07-12 07:55:53',
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'a',
                'title' => 'Ullam ab ut dolorem facere consectetur tempore cupiditate.',
                'slug' => 'eum-modi-id-molestias-perferendis',
                'description' => 'Deserunt unde magni esse et et iure occaecati. Architecto excepturi provident omnis soluta et minima. Officia nihil veniam nostrum est illo quo. Ea nesciunt ab ipsa magni numquam temporibus. Soluta mollitia numquam velit omnis. Vel facere ipsam quae numquam voluptatem.',
                'filename' => null,
                'original_filename' => null,
                'mime_type' => null,
                'file_size_bytes' => null,
                'width' => null,
                'height' => null,
                'focal_point' => null,
                'is_optimized' => false,
                'checksum' => null,
                'alt_text' => null,
                'license' => null,
                'created_at' => '2025-01-25 11:36:05',
                'updated_at' => '2025-05-17 01:54:42',
                'deleted_at' => null,
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'a',
                'title' => 'Optio facere libero autem.',
                'slug' => 'quaerat-excepturi-facere-ducimus-omnis-distinctio',
                'description' => 'Tenetur placeat enim culpa. Quisquam molestiae facere labore rerum pariatur at. Ea molestias tenetur vel laborum expedita. Est aut vel qui sequi quia. Voluptas veritatis et quia accusantium architecto. Labore voluptatibus excepturi nulla temporibus sit quisquam quia.',
                'filename' => null,
                'original_filename' => null,
                'mime_type' => null,
                'file_size_bytes' => null,
                'width' => null,
                'height' => null,
                'focal_point' => null,
                'is_optimized' => false,
                'checksum' => null,
                'alt_text' => null,
                'license' => null,
                'created_at' => '2025-05-15 14:05:36',
                'updated_at' => '2025-10-23 09:27:20',
                'deleted_at' => '2025-01-24 03:27:25',
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'a',
                'title' => 'Vel aliquid nostrum ut suscipit aspernatur minus.',
                'slug' => 'suscipit-eum-libero-accusamus',
                'description' => 'Inventore eum quod eius quo minus enim. Perferendis harum reprehenderit expedita dolores voluptates facilis culpa aut. Et qui architecto quia omnis et tenetur. Aut expedita delectus necessitatibus quasi magni.',
                'filename' => null,
                'original_filename' => null,
                'mime_type' => null,
                'file_size_bytes' => null,
                'width' => null,
                'height' => null,
                'focal_point' => null,
                'is_optimized' => false,
                'checksum' => null,
                'alt_text' => null,
                'license' => null,
                'created_at' => '2025-01-29 10:32:52',
                'updated_at' => '2025-12-12 06:51:14',
                'deleted_at' => '2025-09-29 02:18:25',
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'a',
                'title' => 'Sit ut nobis ullam rerum ipsum dolores sint.',
                'slug' => 'exercitationem-enim-qui-ex-adipisci-eveniet-a',
                'description' => 'Cum incidunt eum voluptas aut soluta. Eum optio error itaque neque. At reprehenderit sit odio dolorum ab ratione quia quaerat. Et sapiente fugiat ipsa ab. Voluptatem doloremque sit officia eveniet. Corrupti est cum qui fugit dolorem.',
                'filename' => null,
                'original_filename' => null,
                'mime_type' => null,
                'file_size_bytes' => null,
                'width' => null,
                'height' => null,
                'focal_point' => null,
                'is_optimized' => false,
                'checksum' => null,
                'alt_text' => null,
                'license' => null,
                'created_at' => '2025-10-04 23:33:06',
                'updated_at' => '2025-05-13 12:59:24',
                'deleted_at' => '2025-08-03 16:02:49',
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
