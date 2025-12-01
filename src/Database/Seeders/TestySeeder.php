<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\Seeders\Seeder;
use Core\Database\ConnectionInterface;

/**
 * ferated File - Date: 20251129_124442
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
                'store_id' => $storeId,
                'user_id' => $userId,
                'super_powers' => '["optionA","optionC"]',
                'status' => 'A',
                'slug' => 'vero-corrupti-dicta-tempora-dolores-debitis-vel',
                'title' => 'Accusamus nam cumque accusantium.',
                'generic_text' => 'Est debitis repellendus labore quos consequuntur omnis in.',
                'content' => 'Ut quibusdam autem mollitia possimus impedit. Libero id inventore tenetur velit laborum. Magnam temporibus aut labore ipsam nobis temporibus.',
                'image_count' => 3,
                'cover_image_id' => 57605,
                'generic_code' => 'yav-622',
                'date_of_birth' => '2000-02-08',
                'generic_date' => '2012-12-04',
                'generic_month' => 'Corrupti architecto et dolorem et omnis eaque.',
                'generic_week' => 'Autem ducimus vero eum quia.',
                'generic_time' => '16:51:23',
                'generic_datetime' => '2025-01-21 16:33:28',
                'telephone' => '+1.281.951.6771',
                'state_code' => 'tx',
                'gender_id' => 'pns',
                'gender_other' => 'nb',
                'is_verified' => false,
                'interest_soccer_ind' => false,
                'interest_baseball_ind' => false,
                'interest_football_ind' => false,
                'interest_hockey_ind' => false,
                'primary_email' => 'robel.jody@example.com',
                'secret_code_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'balance' => 0,
                'generic_decimal' => 899.2421,
                'volume_level' => 94,
                'start_rating' => 2.5,
                'generic_number' => 0,
                'generic_num' => 55,
                'generic_color' => 'Silver',
                'wake_up_time' => null,
                'favorite_week_day' => 'Tuesday',
                'online_address' => 'http://zboncak.com/',
                'profile_picture' => 'pictures/6d2c91e2d25622243fb7b025aef94d7d.jpg',
                'created_at' => '2025-10-06 06:17:58',
                'updated_at' => '2025-07-24 23:12:15',
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'super_powers' => '["optionA"]',
                'status' => 'A',
                'slug' => 'repellat-sit-odit-voluptates-cupiditate-id',
                'title' => 'Quo quam quaerat esse sit rerum eos sunt impedit quibusdam.',
                'generic_text' => 'Repellat quo facere facilis porro.',
                'content' => 'Cum quidem sunt voluptate et expedita officiis. Impedit quae quis nihil quia. Vel voluptatem nulla iure iste. Officia et consectetur perferendis mollitia sapiente voluptatibus.',
                'image_count' => null,
                'cover_image_id' => null,
                'generic_code' => 'zyd-101',
                'date_of_birth' => '2004-09-02',
                'generic_date' => '1970-02-02',
                'generic_month' => 'Nesciunt dolorem qui sed omnis quaerat.',
                'generic_week' => 'Vitae architecto labore et aliquid.',
                'generic_time' => '21:55:42',
                'generic_datetime' => '2025-03-29 11:56:36',
                'telephone' => '(805) 957-4821',
                'state_code' => 'tx',
                'gender_id' => 'f',
                'gender_other' => 'o',
                'is_verified' => false,
                'interest_soccer_ind' => false,
                'interest_baseball_ind' => false,
                'interest_football_ind' => false,
                'interest_hockey_ind' => false,
                'primary_email' => 'robert.tromp@example.org',
                'secret_code_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'balance' => 0,
                'generic_decimal' => null,
                'volume_level' => 99,
                'start_rating' => 4.2,
                'generic_number' => 0,
                'generic_num' => 55,
                'generic_color' => null,
                'wake_up_time' => null,
                'favorite_week_day' => 'Wednesday',
                'online_address' => 'https://www.bode.org/est-doloremque-rem-omnis-illum',
                'profile_picture' => null,
                'created_at' => '2025-10-23 13:01:44',
                'updated_at' => '2025-01-31 15:06:23',
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'super_powers' => '["optionA","optionC"]',
                'status' => 'A',
                'slug' => 'quibusdam-perspiciatis-enim-maxime-est-quis-quidem',
                'title' => 'Quis qui enim veritatis.',
                'generic_text' => 'Nostrum et ea consequatur et repellat est tempore ut.',
                'content' => 'Voluptate nisi qui dolores odit facere quis. Velit voluptatem nihil asperiores est ullam voluptatem. Non aspernatur blanditiis eius quos incidunt.',
                'image_count' => 5,
                'cover_image_id' => 92733,
                'generic_code' => 'frm-064',
                'date_of_birth' => '2002-11-15',
                'generic_date' => '2004-02-17',
                'generic_month' => 'Unde et dolor ea dolor possimus.',
                'generic_week' => 'Nihil magni neque cum consequuntur.',
                'generic_time' => null,
                'generic_datetime' => '2025-08-06 12:23:21',
                'telephone' => '(615) 860-4938',
                'state_code' => 'nj',
                'gender_id' => 'f',
                'gender_other' => 'f',
                'is_verified' => false,
                'interest_soccer_ind' => false,
                'interest_baseball_ind' => false,
                'interest_football_ind' => false,
                'interest_hockey_ind' => false,
                'primary_email' => null,
                'secret_code_hash' => null,
                'balance' => 0,
                'generic_decimal' => 576.10039,
                'volume_level' => 91,
                'start_rating' => 1.3,
                'generic_number' => 0,
                'generic_num' => 55,
                'generic_color' => null,
                'wake_up_time' => '13:42:58',
                'favorite_week_day' => 'Friday',
                'online_address' => 'https://www.abernathy.com/ea-debitis-atque-quia-id-nihil',
                'profile_picture' => null,
                'created_at' => '2025-02-23 07:12:01',
                'updated_at' => '2025-11-08 00:33:17',
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'super_powers' => '["optionB","optionC","optionA"]',
                'status' => 'A',
                'slug' => 'autem-sunt-quia-omnis-illum-sint-rerum-quasi',
                'title' => 'Nam eaque numquam velit quis dolorem.',
                'generic_text' => 'Reiciendis optio dolorum dolorum pariatur ad vero nisi sed.',
                'content' => 'Vel vel soluta expedita est iste deserunt necessitatibus. Dolores omnis reprehenderit debitis reiciendis. Sunt numquam nesciunt quia odit. Aut autem quis et nisi. Ut neque beatae nulla molestiae.',
                'image_count' => 7,
                'cover_image_id' => 51695,
                'generic_code' => 'klo-530',
                'date_of_birth' => '1971-08-17',
                'generic_date' => '2009-06-08',
                'generic_month' => 'Debitis nesciunt animi aliquid omnis cum.',
                'generic_week' => 'Dolorem sed velit eius quos dolor.',
                'generic_time' => null,
                'generic_datetime' => '2025-05-28 07:58:29',
                'telephone' => null,
                'state_code' => 'ny',
                'gender_id' => null,
                'gender_other' => 'm',
                'is_verified' => false,
                'interest_soccer_ind' => false,
                'interest_baseball_ind' => false,
                'interest_football_ind' => false,
                'interest_hockey_ind' => false,
                'primary_email' => 'ignatius21@example.net',
                'secret_code_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'balance' => 0,
                'generic_decimal' => 709.0007,
                'volume_level' => 36,
                'start_rating' => 2.4,
                'generic_number' => 0,
                'generic_num' => 55,
                'generic_color' => null,
                'wake_up_time' => '08:18:40',
                'favorite_week_day' => 'Monday',
                'online_address' => 'http://wisozk.com/rem-nihil-perspiciatis-amet-molestiae-placeat-recusandae',
                'profile_picture' => 'pictures/01f108e7b876160205c987483b505640.jpg',
                'created_at' => '2025-01-21 13:33:04',
                'updated_at' => '2025-06-05 00:23:43',
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'super_powers' => '["optionC","optionA","optionB"]',
                'status' => 'A',
                'slug' => 'dolores-repudiandae-sit-aliquam-est-blanditiis-aut',
                'title' => 'Cum velit ipsam.',
                'generic_text' => 'Ratione et dicta nobis repellendus facere est quae.',
                'content' => 'Qui incidunt sed voluptas tempora quia. Voluptate similique rerum cum ipsum sit maiores. Vero sed officia officia praesentium laudantium. Dolorem sequi amet assumenda id accusamus natus. Doloremque animi corrupti accusamus voluptas eum rem sed.',
                'image_count' => 5,
                'cover_image_id' => 12532,
                'generic_code' => 'wuu-612',
                'date_of_birth' => '1989-11-01',
                'generic_date' => '2018-09-21',
                'generic_month' => null,
                'generic_week' => 'Nam deleniti rem aut vitae sit eos.',
                'generic_time' => '12:37:20',
                'generic_datetime' => '2025-06-07 13:09:31',
                'telephone' => '(351) 784-5542',
                'state_code' => 'al',
                'gender_id' => 'm',
                'gender_other' => 'nb',
                'is_verified' => false,
                'interest_soccer_ind' => false,
                'interest_baseball_ind' => false,
                'interest_football_ind' => false,
                'interest_hockey_ind' => false,
                'primary_email' => 'lois.gislason@example.net',
                'secret_code_hash' => null,
                'balance' => 0,
                'generic_decimal' => 702.62696,
                'volume_level' => 58,
                'start_rating' => 4.4,
                'generic_number' => 0,
                'generic_num' => 55,
                'generic_color' => null,
                'wake_up_time' => '20:56:51',
                'favorite_week_day' => 'Tuesday',
                'online_address' => 'http://connelly.biz/voluptas-aut-enim-harum-vitae-error',
                'profile_picture' => 'pictures/08165ad9af8b235671a34af4986a1996.jpg',
                'created_at' => '2025-02-05 01:53:32',
                'updated_at' => '2025-03-26 05:33:26',
            ]
        ];

        $inserted = 0;
        foreach ($records as $record) {
            // Assuming 'slug' is the unique field for createIfNotExists
            $record['user_id'] = random_int(1, 6);
            $record['store_id'] = random_int(1, 14);

            //if ($this->createIfNotExists('testy', $record, ['slug'])) {
            if ($this->createIfNotExists('testy', $record, ['slug'])) {
                $inserted++;
            }
        }
        $countTried = count($records);
        echo "Seeded {$inserted} testy records successfully out of {$countTried} records.\n";
    }
}
