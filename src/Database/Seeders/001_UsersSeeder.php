<?php

namespace Database\Seeders;

use Core\Database\Seeder\Seeder;

/**
 * Seeds the users table with initial admin user
 *
 * This seeder creates a default admin user that can be used
 * for initial application setup and testing.
 *
 * Usage:
 * ```
 * php bin/console.php seed UsersSeeder
 * ```
 *
 * @package Database\Seeders
 */
class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);

        $this->createIfNotExists('users', [
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => $passwordHash,
            'roles' => json_encode(['admin']),
            'status' => 'A'
        ], ['username', 'email']);

        $this->createIfNotExists('users', [
            'username' => 'storeadmin',
            'email' => 'admin2@example.com',
            'password_hash' => $passwordHash,
            'roles' => json_encode(['admin']),
            'status' => 'A'
        ], ['username', 'email']);

         $passwordHash = password_hash('q1Q!', PASSWORD_DEFAULT);

        $this->createIfNotExists('users', [
            'username' => 'storejohn',
            'email' => 'storejohn@example.com',
            'password_hash' => $passwordHash,
            'roles' => json_encode(['user']),
            'status' => 'A'
        ], ['username', 'email']);
    }
}
