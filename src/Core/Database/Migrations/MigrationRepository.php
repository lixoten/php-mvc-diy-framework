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
        try {
            return $this->db->query("SELECT * FROM {$this->table} ORDER BY batch, id");
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                $this->createRepository();
                return [];
            }
            throw $e;
        }
    }

    public function getMigrationsByBatch(int $batch): array
    {
        try {
            return $this->db->query(
                "SELECT * FROM {$this->table} WHERE batch = ? ORDER BY id",
                [$batch]
            );
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                $this->createRepository();
                return [];
            }
            throw $e;
        }
    }

    public function getLastBatchNumber(): int
    {
        try {
            $result = $this->db->query("SELECT MAX(batch) as batch FROM {$this->table}");
            return (int) ($result[0]['batch'] ?? 0);
        } catch (\Exception $e) {
            // If table doesn't exist, create it and return 0
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                $this->createRepository();
                return 0;
            }
            throw $e;
        }
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

    // Fix the getMigratedFiles() method (line 69-72) - keep it returning array:
    public function getMigratedFiles(): array
    {
        try {
            $result = $this->db->query("SELECT migration FROM {$this->table}");
            return array_column($result, 'migration');
        } catch (\Exception $e) {
            // If table doesn't exist, create it and return empty array
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                $this->createRepository();
                return []; // EMPTY ARRAY, not 0!
            }
            throw $e;
        }
    }
}
