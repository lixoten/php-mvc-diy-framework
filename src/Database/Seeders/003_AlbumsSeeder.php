<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\Seeder\Seeder;

class AlbumsSeeder extends Seeder
{
    public function run(): void
    {
        // Check for prerequisite data: User ID 1 and Store ID 1
        $user = $this->db->query("SELECT user_id FROM users WHERE user_id = 1 LIMIT 1");
        $store = $this->db->query("SELECT store_id FROM stores WHERE store_id = 1 LIMIT 1");

        if (empty($user)) {
            echo "Error: Can't seed albums without user ID 1. Please run the UsersSeeder first.\n";
            return;
        }
        if (empty($store)) {
            echo "Error: Can't seed albums without store ID 1. Please run the StoresSeeder first.\n";
            return;
        }

        $userId = 1;
        $storeId = 1;

        // Sample album data
        $albums = [
            [
                'album_store_id' => $storeId,
                'album_user_id' => $userId,
                'album_status' => 'A', // Active
                'slug' => 'beach-days',
                'name' => 'Beach Days',
                'description' => 'Photos from our trip to the coast. Sunny days and sandy toes!',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'album_store_id' => $storeId,
                'album_user_id' => $userId,
                'album_status' => 'P', // Pending/Private
                'slug' => 'initial-concepts',
                'name' => 'Initial Concepts',
                'description' => 'Early design sketches and mockups for the new project.',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
            [
                'album_store_id' => $storeId, // Same store, different album
                'album_user_id' => $userId,
                'album_status' => 'A', // Active
                'slug' => 'mountain-hike',
                'name' => 'Mountain Hike',
                'description' => 'Exploring the trails and enjoying the views.',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ];

        // Insert sample albums
        $insertedCount = 0;
        foreach ($albums as $album) {
            // Check based on store, user, name to avoid exact duplicates
            if ($this->createIfNotExists('albums', $album, ['album_store_id', 'album_user_id', 'name'])) {
                $insertedCount++;
            }
        }

        if ($insertedCount === 0) {
            echo "No new albums were seeded. All sample albums already exist.\n";
        } else {
            echo "Seeded " . $insertedCount . " albums successfully.\n";
        }
    }

    /**
     * Generate a URL-friendly slug from a string
     *
     * @param string $string Input string
     * @return string URL-friendly slug
     */
    private function createSlug(string $string): string
    {
        // Convert to lowercase
        $slug = strtolower($string);

        // Replace spaces with hyphens
        $slug = str_replace(' ', '-', $slug);

        // Remove special characters
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);

        // Remove duplicate hyphens
        $slug = preg_replace('/-+/', '-', $slug);

        // Trim hyphens from beginning and end
        $slug = trim($slug, '-');

        return $slug;
    }
}
