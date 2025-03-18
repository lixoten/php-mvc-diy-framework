<?php

declare(strict_types=1);

namespace Core\Database\Migrations;

use Core\Database\ConnectionInterface;

class MigrationRepository
{
    protected ConnectionInterface $db;
    protected string $table;

    public function __construct(ConnectionInterface $db, string $table = 'migrations')
    {
        $this->db = $db;
        $this->table = $table;
    }

    public function createRepository(): void
    {
        // Create migrations table if it doesn't exist
        $this->db->execute("
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function getMigrations(): array
    {
        return $this->db->query("SELECT * FROM {$this->table} ORDER BY batch, id");
    }

    public function getMigrationsByBatch(int $batch): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE batch = ? ORDER BY id",
            [$batch]
        );
    }

    public function getLastBatchNumber(): int
    {
        $result = $this->db->query("SELECT MAX(batch) as batch FROM {$this->table}");
        return (int) ($result[0]['batch'] ?? 0);
    }

    public function log(string $migration, int $batch): void
    {
        $this->db->execute(
            "INSERT INTO {$this->table} (migration, batch) VALUES (?, ?)",
            [$migration, $batch]
        );
    }

    public function delete(string $migration): void
    {
        $this->db->execute(
            "DELETE FROM {$this->table} WHERE migration = ?",
            [$migration]
        );
    }

    public function getMigratedFiles(): array
    {
        $result = $this->db->query("SELECT migration FROM {$this->table}");
        return array_column($result, 'migration');
    }
}
