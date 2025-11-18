<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\Seeders\Seeder;
use Core\Database\ConnectionInterface;

/**
 * Generated File - Date: 20251114_191858
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
                'status' => 'A',
                'slug' => 'velit-voluptatem-facilis-ipsum-ullam-nulla-suscipit-facere-officia',
                'title' => 'Vel optio praesentium facere.',
                'content' => 'Sapiente magnam nobis excepturi ut vero asperiores dolores provident. Minus excepturi consequatur voluptas ex ut dignissimos.',
                'generic_text' => 'Architecto ut reprehenderit corporis aut fuga beatae.',
                'image_count' => 8,
                'cover_image_id' => null,
                'generic_code' => 'idt-913',
                'super_powers' => '["optionB","optionA"]',
                'date_of_birth' => '1992-07-10',
                'generic_date' => '1995-01-11',
                'generic_month' => 'Qui quis dolorem aperiam blanditiis.',
                'generic_week' => 'Non et alias nihil repellat.',
                'generic_time' => '14:47:05',
                'generic_datetime' => '2025-06-18 10:12:56',
                'telephone' => '+1-503-281-6804',
                'gender_id' => 'f',
                'gender_other' => null,
                'is_verified' => false,
                'interest_soccer_ind' => false,
                'interest_baseball_ind' => false,
                'interest_football_ind' => false,
                'interest_hockey_ind' => false,
                'primary_email' => 'vwintheiser@example.org',
                'secret_code_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'balance' => 0,
                'generic_decimal' => null,
                'volume_level' => 45,
                'start_rating' => null,
                'generic_number' => 0,
                'generic_num' => 55,
                'generic_color' => 'SeaShell',
                'wake_up_time' => '18:40:28',
                'favorite_week_day' => 'Monday',
                'online_address' => 'http://mohr.com/sint-architecto-tenetur-porro-consequuntur-vel-ratione',
                'profile_picture' => 'pictures/2df05ce739947acdd2ea1e09c6252e3d.jpg',
                'created_at' => '2025-01-16 05:53:51',
                'updated_at' => '2025-03-02 15:39:59',
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'A',
                'slug' => 'quis-dignissimos-voluptatem-sed',
                'title' => 'Molestias nihil ullam non.',
                'content' => 'Culpa quia provident animi voluptatem accusantium qui amet. Aspernatur nihil ea praesentium sed qui occaecati. Aut alias voluptas commodi quibusdam.',
                'generic_text' => 'Est ea sed autem quod facilis laboriosam adipisci sit.',
                'image_count' => 8,
                'cover_image_id' => 44723,
                'generic_code' => 'kmy-440',
                'super_powers' => '["optionC"]',
                'date_of_birth' => '1982-11-12',
                'generic_date' => '1981-05-16',
                'generic_month' => 'Sed ex occaecati soluta.',
                'generic_week' => 'Rem totam iusto veniam voluptate rerum sed.',
                'generic_time' => '10:17:19',
                'generic_datetime' => null,
                'telephone' => '442-406-5261',
                'gender_id' => 'nb',
                'gender_other' => 'nb',
                'is_verified' => false,
                'interest_soccer_ind' => false,
                'interest_baseball_ind' => false,
                'interest_football_ind' => false,
                'interest_hockey_ind' => false,
                'primary_email' => 'ottis84@example.net',
                'secret_code_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'balance' => 0,
                'generic_decimal' => 604.92491,
                'volume_level' => 59,
                'start_rating' => null,
                'generic_number' => 0,
                'generic_num' => 55,
                'generic_color' => 'SlateGray',
                'wake_up_time' => '12:54:39',
                'favorite_week_day' => 'Friday',
                'online_address' => 'http://www.stiedemann.com/omnis-delectus-illum-sapiente-sapiente.html',
                'profile_picture' => 'pictures/24a655dff7affe1e5651b0d2d07d7bb3.jpg',
                'created_at' => '2025-10-20 03:42:47',
                'updated_at' => '2025-10-16 02:43:31',
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'A',
                'slug' => 'tempora-alias-culpa-sunt-quasi-ex-quidem',
                'title' => 'Qui ut perferendis.',
                'content' => 'Et maxime nostrum rem eius asperiores illum sunt. Adipisci ut ut debitis. Aut veritatis dolores dolores eum ipsa tenetur non sunt. Sint reprehenderit nemo corporis nesciunt qui perferendis. Possimus tempore voluptas ut quasi atque eligendi totam aut. In accusantium sunt omnis error excepturi dolores voluptatem.',
                'generic_text' => 'Earum necessitatibus sapiente nostrum omnis minus.',
                'image_count' => 10,
                'cover_image_id' => 21484,
                'generic_code' => 'msm-335',
                'super_powers' => '["optionB","optionA"]',
                'date_of_birth' => '2001-06-21',
                'generic_date' => '2022-12-04',
                'generic_month' => 'Maxime omnis sed reiciendis et ipsum nemo.',
                'generic_week' => 'Omnis ex saepe odit quod omnis.',
                'generic_time' => '13:10:44',
                'generic_datetime' => '2025-07-14 11:51:54',
                'telephone' => '830-620-7898',
                'gender_id' => null,
                'gender_other' => 'o',
                'is_verified' => false,
                'interest_soccer_ind' => false,
                'interest_baseball_ind' => false,
                'interest_football_ind' => false,
                'interest_hockey_ind' => false,
                'primary_email' => 'gbergnaum@example.net',
                'secret_code_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'balance' => 0,
                'generic_decimal' => 492.42322,
                'volume_level' => 58,
                'start_rating' => 4.7,
                'generic_number' => 0,
                'generic_num' => 55,
                'generic_color' => 'DeepSkyBlue',
                'wake_up_time' => '05:13:58',
                'favorite_week_day' => 'Monday',
                'online_address' => 'http://www.yundt.com/',
                'profile_picture' => 'pictures/c3d75cffed665b83fa1a67a15e3cc47e.jpg',
                'created_at' => '2025-08-25 23:51:40',
                'updated_at' => '2025-09-08 04:52:13',
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'A',
                'slug' => 'saepe-illo-consectetur-vero-saepe-molestias-quae-itaque',
                'title' => 'Esse in esse occaecati.',
                'content' => 'Et vero molestias nemo rem dignissimos in. Voluptatibus vero consequatur dignissimos nemo totam occaecati eligendi corporis. Nulla cupiditate et qui explicabo mollitia sed. Fugiat enim quia voluptas at voluptatem.',
                'generic_text' => null,
                'image_count' => 4,
                'cover_image_id' => 54142,
                'generic_code' => 'wey-243',
                'super_powers' => '["optionA","optionB","optionC"]',
                'date_of_birth' => null,
                'generic_date' => '1993-03-04',
                'generic_month' => 'Blanditiis itaque vel aut quo.',
                'generic_week' => null,
                'generic_time' => '20:28:08',
                'generic_datetime' => '2025-08-06 01:34:10',
                'telephone' => '1-269-299-9501',
                'gender_id' => 'm',
                'gender_other' => 'nb',
                'is_verified' => false,
                'interest_soccer_ind' => false,
                'interest_baseball_ind' => false,
                'interest_football_ind' => false,
                'interest_hockey_ind' => false,
                'primary_email' => 'willa08@example.org',
                'secret_code_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'balance' => 0,
                'generic_decimal' => 247.28685,
                'volume_level' => null,
                'start_rating' => 2.5,
                'generic_number' => 0,
                'generic_num' => 55,
                'generic_color' => 'Peru',
                'wake_up_time' => '07:42:23',
                'favorite_week_day' => 'Thursday',
                'online_address' => 'http://www.wunsch.org/',
                'profile_picture' => 'pictures/7d6aff584cd5e90dd945953ffa41dbf2.jpg',
                'created_at' => '2025-01-03 15:50:32',
                'updated_at' => '2025-10-26 13:52:24',
            ],
            [
                'store_id' => $storeId,
                'user_id' => $userId,
                'status' => 'A',
                'slug' => 'earum-enim-quisquam-voluptatem-at-accusamus-qui-cumque',
                'title' => 'Iusto aspernatur similique perspiciatis.',
                'content' => 'Accusantium voluptatem et cupiditate eum autem rerum. Dolor cum rerum numquam culpa rerum. Corrupti et aut provident provident qui. Vitae nulla velit rerum nostrum sint sunt. Eveniet id nesciunt laudantium officiis. Quo assumenda blanditiis sit ut quod tempora magni.',
                'generic_text' => 'Nesciunt rerum dolor hic.',
                'image_count' => null,
                'cover_image_id' => 38246,
                'generic_code' => 'rcf-307',
                'super_powers' => '["optionC","optionA","optionB"]',
                'date_of_birth' => null,
                'generic_date' => '2003-02-17',
                'generic_month' => null,
                'generic_week' => 'Ipsum nobis numquam in laborum omnis enim.',
                'generic_time' => '21:31:19',
                'generic_datetime' => '2025-09-01 16:49:04',
                'telephone' => '1-815-392-6720',
                'gender_id' => 'nb',
                'gender_other' => null,
                'is_verified' => false,
                'interest_soccer_ind' => false,
                'interest_baseball_ind' => false,
                'interest_football_ind' => false,
                'interest_hockey_ind' => false,
                'primary_email' => 'fiona85@example.net',
                'secret_code_hash' => null,
                'balance' => 0,
                'generic_decimal' => 645.34171,
                'volume_level' => 94,
                'start_rating' => 3.9,
                'generic_number' => 0,
                'generic_num' => 55,
                'generic_color' => 'OliveDrab',
                'wake_up_time' => '16:35:17',
                'favorite_week_day' => 'Monday',
                'online_address' => 'http://dare.info/veritatis-perferendis-eum-minus-dolorem-pariatur-nihil-labore',
                'profile_picture' => 'pictures/c6f773a2d393f46f8b28609207fb74ed.jpg',
                'created_at' => '2025-10-16 10:31:41',
                'updated_at' => '2025-02-23 10:18:24',
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
