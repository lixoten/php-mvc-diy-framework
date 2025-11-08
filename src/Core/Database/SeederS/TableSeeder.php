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

            $this->db->execute($sql, $values);
        }
    }
}
