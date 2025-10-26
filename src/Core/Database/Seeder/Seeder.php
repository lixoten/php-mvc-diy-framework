<?php

namespace Core\Database\Seeder;

use Core\Database\Connection;

abstract class Seeder
{
    protected Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Run the seeder
     */
    abstract public function run(): void;

    /**
     * Check if records exist in a table
     *
     * @param string $table Table name
     * @param array $conditions Optional WHERE conditions as column => value pairs
     * @return bool True if records exist, false otherwise
     */
    protected function hasRecords(string $table, array $conditions = []): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $column => $value) {
                $where[] = "{$column} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $result = $this->db->query($sql, $params);
        return $result[0]['count'] > 0;
    }

    /**
     * Helper to create records only if they don't exist
     *
     * @param string $table Table name
     * @param array $data Data to insert
     * @param array $uniqueCheck Fields to check for uniqueness
     * @return bool Whether records were inserted
     */
    protected function createIfNotExists(string $table, array $data, array $uniqueCheck = []): bool
    {
        if (empty($uniqueCheck)) {
            // Without unique fields specified, we check if the table has any records
            $exists = $this->hasRecords($table);
        } else {
            // With unique fields, we check for matches on those fields
            $conditions = [];
            foreach ($uniqueCheck as $field) {
                if (isset($data[$field])) {
                    $conditions[$field] = $data[$field];
                }
            }
            $exists = $this->hasRecords($table, $conditions);
        }

        if (!$exists) {
            $this->table($table)->insert([$data]);
            return true;
        }

        echo "Record already exists in {$table}, skipping...\n";
        return false;
    }

    /**
     * Get a table helper for fluent inserts
     *
     * @param string $table Table name
     * @return TableSeeder
     */
    protected function table(string $table): TableSeeder
    {
        return new TableSeeder($this->db, $table);
    }


        /**
     * Ensure a table exists before seeding. Throws if missing.
     *
     * @param string $table
     * @throws \Core\Exceptions\MissingTableException
     */
    public function requireTable(string $table): void
    {
        $schema = new \Core\Database\Schema\SchemaBuilder($this->db);
        if (!$schema->hasTable($table)) {
            throw new \Core\Exceptions\MissingTableException(
                "Required table '{$table}' not found. Run migrations before seeding."
            );
        }
    }
}
