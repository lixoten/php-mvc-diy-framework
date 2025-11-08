<?php

declare(strict_types=1);

namespace Core\Database\Migrations;

use Core\Database\ConnectionInterface;
use Core\Database\Schema\Blueprint;
use Core\Database\Schema\SchemaBuilder;

abstract class Migration
{
    protected ConnectionInterface $db;
    protected SchemaBuilder $schema;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
        $this->schema = new SchemaBuilder($db);
    }

    /**
     * Run the migration
     */
    abstract public function up(): void;

    /**
     * Reverse the migration
     */
    abstract public function down(): void;

    /**
     * Create a new table.
     *
     * @param string $tableName
     * @param \Closure(\Core\Database\Schema\Blueprint $table): void $callback // Updated PHPDoc for better IDE inference
     * @return void
     */
    protected function create(string $tableName, \Closure $callback): void
    {
        $this->schema->create($tableName, $callback);
    }

    /**
     * Drop a table
     */
    protected function drop(string $table): void
    {
        $this->schema->drop($table);
    }

    /**
     * Modify an existing table
     */
    protected function table(string $table, \Closure $callback): void
    {
        $this->schema->table($table, $callback);
    }

    /**
     * Execute raw SQL
     */
    protected function raw(string $sql): void
    {
        $this->db->execute($sql);
    }
}
