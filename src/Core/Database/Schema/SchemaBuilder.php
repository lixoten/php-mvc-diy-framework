<?php

declare(strict_types=1);

namespace Core\Database\Schema;

use Core\Database\ConnectionInterface;

class SchemaBuilder
{
    protected ConnectionInterface $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Create a new table
     *
     * @param string $table Table name
     * @param \Closure $callback Blueprint configuration function
     * @return void
     */
    public function create(string $table, \Closure $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);

        $sql = $blueprint->toSql();
        $this->connection->execute($sql);
    }

    /**
     * Drop a table if it exists
     *
     * @param string $table Table name
     * @return void
     */
    public function drop(string $table): void
    {
        $sql = "DROP TABLE IF EXISTS {$table}";
        $this->connection->execute($sql);
    }

    /**
     * Modify an existing table
     *
     * @param string $table Table name
     * @param \Closure $callback Blueprint configuration function
     * @return void
     */
    public function table(string $table, \Closure $callback): void
    {
        $blueprint = new Blueprint($table, true); // true = alter mode
        $callback($blueprint);

        $sql = $blueprint->toAlterSql();
        if (!empty($sql)) {
            $this->connection->execute($sql);
        }
    }

    /**
     * Check if a table exists
     *
     * @param string $table Table name
     * @return bool
     */
    public function hasTable(string $table): bool
    {
        $result = $this->connection->query(
            "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?",
            [$table]
        );
        return !empty($result);
    }

    /**
     * Check if a column exists in a table
     *
     * @param string $table Table name
     * @param string $column Column name
     * @return bool
     */
    public function hasColumn(string $table, string $column): bool
    {
        $result = $this->connection->query(
            "SELECT 1 FROM information_schema.columns " .
            "WHERE table_schema = DATABASE() " .
            "AND table_name = ? " .
            "AND column_name = ?",
            [$table, $column]
        );
        return !empty($result);
    }

    /**
     * Rename a table
     *
     * @param string $from Original table name
     * @param string $to New table name
     * @return void
     */
    public function rename(string $from, string $to): void
    {
        $sql = "RENAME TABLE {$from} TO {$to}";
        $this->connection->execute($sql);
    }
}
