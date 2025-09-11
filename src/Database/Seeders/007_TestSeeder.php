<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\Seeder\Seeder;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        // $this->table('test_table')->insert([
            // ['name' => 'Test 1', 'description' => 'First test record'],
            // ['name' => 'Test 2', 'description' => 'Second test record'],
        // ]);

        // Insert each record only if it doesn't exist
        $this->createIfNotExists(
            'test_table',
            ['name' => 'Test 1', 'description' => 'First test record'],
            ['name']
        );

        $this->createIfNotExists(
            'test_table',
            ['name' => 'Test 2', 'description' => 'Second test record'],
            ['name']
        );
    }
}
