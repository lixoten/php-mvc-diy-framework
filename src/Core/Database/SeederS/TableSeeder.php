<?php

declare(strict_types=1);

namespace Core\Database\Seeders;

use Core\Database\ConnectionInterface;

class TableSeeder
{
    protected ConnectionInterface $db;
    protected string $table;

    public function __construct(ConnectionInterface $db, string $table)
    {
        $this->db = $db;
        $this->table = $table;
    }

    public function insert(array $data): void
    {
        if (empty($data)) {
            return;
        }

        // Handle single vs multiple inserts
        if (!isset($data[0])) {
            $data = [$data];
        }

        foreach ($data as $row) {
            $columns = array_keys($row);
            $values = array_values($row);
            $placeholders = array_fill(0, count($values), '?');

            $sql = sprintf(
                "INSERT INTO %s (%s) VALUES (%s)",
                $this->table,
                implode(', ', $columns),
                implode(', ', $placeholders)
            );

            // ⚠️ DEBUGGING START
            error_log("--- Seeder Debugging ---");
            error_log("SQL: " . $sql);
            error_log("Values for binding:");
            foreach ($values as $key => $val) {
                // Special handling for booleans to clearly show their value
                if (is_bool($val)) {
                    error_log("  [{$key}] => " . ($val ? 'true (bool)' : 'false (bool)'));
                } elseif (is_null($val)) {
                    error_log("  [{$key}] => NULL");
                } else {
                    error_log("  [{$key}] => '" . addslashes((string) $val) . "' (type: " . gettype($val) . ")");
                }
            }
            error_log("--- End Seeder Debugging ---");
            // ⚠️ DEBUGGING END



            $this->db->execute($sql, $values);
        }
    }
}
