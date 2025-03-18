<?php

declare(strict_types=1);

namespace Core\Database\Schema;

class Blueprint
{
    protected string $table;
    protected array $columns = [];
    protected array $indexes = [];
    protected array $foreignKeys = [];
    protected bool $alterMode = false;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function id(string $name = 'id'): Column
    {
        return $this->integer($name, true)->primary();
    }

    public function string(string $name, int $length = 255): Column
    {
        $column = new Column($name, "VARCHAR({$length})");
        $this->columns[] = $column;
        return $column;
    }

    public function integer(string $name, bool $autoIncrement = false): Column
    {
        $type = $autoIncrement ? 'INT AUTO_INCREMENT' : 'INT';
        $column = new Column($name, $type);
        $this->columns[] = $column;
        return $column;
    }

    public function text(string $name): Column
    {
        $column = new Column($name, 'TEXT');
        $this->columns[] = $column;
        return $column;
    }

    public function boolean(string $name): Column
    {
        $column = new Column($name, 'TINYINT(1)');
        $this->columns[] = $column;
        return $column;
    }

    public function timestamp(string $name): Column
    {
        $column = new Column($name, 'TIMESTAMP');
        $this->columns[] = $column;
        return $column;
    }

    public function timestamps(): void
    {
        $this->timestamp('created_at')->default('CURRENT_TIMESTAMP');
        $this->timestamp('updated_at')->nullable();
    }

    public function primary(string|array $columns, ?string $name = null): void
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $name = $name ?? "pk_{$this->table}_" . implode('_', $columns);
        $this->indexes[] = new Index($name, $columns, 'PRIMARY KEY');
    }

    public function unique(string|array $columns, ?string $name = null): void
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $name = $name ?? "unique_{$this->table}_" . implode('_', $columns);
        $this->indexes[] = new Index($name, $columns, 'UNIQUE');
    }

    public function index(string|array $columns, ?string $name = null): void
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $name = $name ?? "idx_{$this->table}_" . implode('_', $columns);
        $this->indexes[] = new Index($name, $columns, 'INDEX');
    }

    public function foreignKey(string $column, string $targetTable, string $targetColumn = 'id'): void
    {
        $name = "fk_{$this->table}_{$column}";
        $this->foreignKeys[] = [
            'name' => $name,
            'columns' => [$column],
            'table' => $targetTable,
            'target' => [$targetColumn],
            'onDelete' => 'RESTRICT',
            'onUpdate' => 'RESTRICT'
        ];
    }

    public function toSql(): string
    {
        $columnDefinitions = [];
        foreach ($this->columns as $column) {
            $columnDefinitions[] = "    " . $column->toSql();
        }

        $indexDefinitions = [];
        foreach ($this->indexes as $index) {
            $indexDefinitions[] = "    " . $index->toSql();
        }

        $foreignKeyDefinitions = [];
        foreach ($this->foreignKeys as $fk) {
            $foreignKeyDefinitions[] = sprintf(
                "    CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s(%s) ON DELETE %s ON UPDATE %s",
                $fk['name'],
                implode(', ', $fk['columns']),
                $fk['table'],
                implode(', ', $fk['target']),
                $fk['onDelete'],
                $fk['onUpdate']
            );
        }

        $allDefinitions = array_merge($columnDefinitions, $indexDefinitions, $foreignKeyDefinitions);

        return sprintf(
            "CREATE TABLE %s (\n%s\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            $this->table,
            implode(",\n", $allDefinitions)
        );
    }


    public function toAlterSql(): string
    {
        $statements = [];

        // Add columns
        foreach ($this->columns as $column) {
            $statements[] = "ALTER TABLE {$this->table} ADD COLUMN " . $column->toSql();
        }

        // Add indexes
        foreach ($this->indexes as $index) {
            if ($index->getType() === 'PRIMARY KEY') {
                $statements[] = "ALTER TABLE {$this->table} ADD " . $index->toSql();
            } else {
                $indexType = $index->getType();
                $indexName = $index->getName();
                $indexColumns = implode(', ', $index->getColumns());
                $statements[] = "CREATE {$indexType} INDEX {$indexName} ON {$this->table} ({$indexColumns})";
            }
        }

        // Add foreign keys
        foreach ($this->foreignKeys as $fk) {
            $sql = sprintf(
                "ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s(%s) ON DELETE %s ON UPDATE %s",
                $this->table,
                $fk['name'],
                implode(', ', $fk['columns']),
                $fk['table'],
                implode(', ', $fk['target']),
                $fk['onDelete'],
                $fk['onUpdate']
            );
            $statements[] = $sql;
        }

        return empty($statements) ? '' : implode(";\n", $statements);
    }
}
