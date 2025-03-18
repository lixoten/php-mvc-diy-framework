<?php

declare(strict_types=1);

// filepath: d:\xampp\htdocs\mvclixo\src\Core\Database\Seeder\Seeder.php

namespace Core\Database\Seeder;

use Core\Database\ConnectionInterface;

abstract class Seeder
{
    protected ConnectionInterface $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    abstract public function run(): void;

    protected function table(string $table): TableSeeder
    {
        return new TableSeeder($this->db, $table);
    }
}
