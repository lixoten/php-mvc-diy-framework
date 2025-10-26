<?php

declare(strict_types=1);

namespace Core\Database\Schema;

class Blueprint
{
    protected string $table;
    protected array $columns = [];
    protected array $indexes = [];
    protected array $primaryKey = [];
    protected string $engine = 'InnoDB';
    protected string $charset = 'utf8mb4';
    protected string $collation = 'utf8mb4_unicode_ci';

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    // Basic column types
    public function integer(string $name): Column
    {
        $column = new Column($name, 'INT');
        $this->columns[] = $column;
        return $column;
    }

    public function bigInteger(string $name): Column
    {
        $column = new Column($name, 'BIGINT');
        $this->columns[] = $column;
        return $column;
    }

    public function tinyInteger(string $name): Column
    {
        $column = new Column($name, 'TINYINT');
        $this->columns[] = $column;
        return $column;
    }

    public function string(string $name, int $length = 255): Column
    {
        $column = new Column($name, "VARCHAR({$length})");
        $this->columns[] = $column;
        return $column;
    }

    public function text(string $name): Column
    {
        $column = new Column($name, 'TEXT');
        $this->columns[] = $column;
        return $column;
    }

    public function char(string $name, int $length = 1): Column
    {
        $column = new Column($name, "CHAR({$length})");
        $this->columns[] = $column;
        return $column;
    }

    // public function float(string $name, int $precision = 8, int $scale = 2): Column
    // {
    //     $column = new Column($name, "FLOAT({$precision}, {$scale})");
    //     $this->columns[] = $column;
    //     return $column;
    // }

    public function decimal(string $name, int $precision = 8, int $scale = 2): Column
    {
        $column = new Column($name, "DECIMAL({$precision}, {$scale})");
        $this->columns[] = $column;
        return $column;
    }

    public function boolean(string $name): Column
    {
        $column = new Column($name, 'BOOLEAN');
        $this->columns[] = $column;
        return $column;
    }

    // public function enum(string $name, array $values): Column
    // {
    //     $escapedValues = array_map(fn($v) => "'" . addslashes($v) . "'", $values);
    //     $valuesString = implode(', ', $escapedValues);
    //     $column = new Column($name, "ENUM({$valuesString})");
    //     $this->columns[] = $column;
    //     return $column;
    // }

    public function json(string $name): Column
    {
        // Use JSON type if supported, otherwise TEXT
        $column = new Column($name, 'JSON');
        $this->columns[] = $column;
        return $column;
    }

    // Date and time related columns
    public function timestamp(string $name): Column
    {
        $column = new Column($name, 'TIMESTAMP');
        $this->columns[] = $column;
        return $column;
    }

    public function dateTime(string $name): Column
    {
        $column = new Column($name, 'DATETIME');
        $this->columns[] = $column;
        return $column;
    }

    public function date(string $name): Column
    {
        $column = new Column($name, 'DATE');
        $this->columns[] = $column;
        return $column;
    }

    public function time(string $name): Column
    {
        $column = new Column($name, 'TIME');
        $this->columns[] = $column;
        return $column;
    }

    // Convenience methods
    public function timestamps(): void
    {
        $this->timestamp('created_at')->nullable(false)->default('CURRENT_TIMESTAMP');
        $this->timestamp('updated_at')->nullable()->default(null);
    }

    public function increments(string $name): Column
    {
        $column = new Column($name, 'INT');
        $column->unsigned()->autoIncrement()->primary();
        $this->columns[] = $column;
        return $column;
    }

    public function bigIncrements(string $name): Column
    {
        $column = new Column($name, 'BIGINT');
        $column->unsigned()->autoIncrement()->primary();
        $this->columns[] = $column;
        return $column;
    }

    // Index methods
    public function primary(string|array $columns): void
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $this->primaryKey = $columns;
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
        $this->indexes[] = new Index($name, $columns);
    }

    // Schema modifiers
    public function engine(string $engine): self
    {
        $this->engine = $engine;
        return $this;
    }

    public function charset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }

    public function collation(string $collation): self
    {
        $this->collation = $collation;
        return $this;
    }

    // Generate SQL
    public function toSql(): string
    {
        $columnDefinitions = [];
        foreach ($this->columns as $column) {
            $columnDefinitions[] = "    " . $column->toSql();
        }

        $indexDefinitions = [];

        // Add indexes from columns marked as 'unique'
        foreach ($this->columns as $column) {
            if (in_array('UNIQUE', $column->getAttributes())) {
                $uniqueIndex = new Index(
                    "unique_{$this->table}_{$column->getName()}",
                    [$column->getName()],
                    'UNIQUE'
                );
                $indexDefinitions[] = "    " . $uniqueIndex->toSql();
            }
        }

        // Add explicit indexes
        foreach ($this->indexes as $index) {
            $indexDefinitions[] = "    " . $index->toSql();
        }

        $parts = array_merge($columnDefinitions, $indexDefinitions);

        $sql = "CREATE TABLE {$this->table} (\n";
        $sql .= implode(",\n", $parts);
        $sql .= "\n) ENGINE={$this->engine} DEFAULT CHARSET={$this->charset} COLLATE={$this->collation};";

        return $sql;
    }

    /**
     * Generate SQL for an ALTER TABLE statement
     *
     * @return string
     */
    public function toAlterSql(): string
    {
        $alterStatements = [];

        // Add columns
        foreach ($this->columns as $column) {
            $alterStatements[] = "ADD COLUMN " . $column->toSql();
        }

        // Add indexes
        foreach ($this->indexes as $index) {
            $alterStatements[] = "ADD " . $index->toSql();
        }

        // Generate primary key if needed
        if (!empty($this->primaryKey)) {
            $alterStatements[] = "ADD PRIMARY KEY (" . implode(', ', $this->primaryKey) . ")";
        }

        if (empty($alterStatements)) {
            return '';
        }

        return "ALTER TABLE {$this->table} \n" . implode(",\n", $alterStatements) . ";";
    }

    /**
     * Create a foreign ID column
     *
     * @param string $name Column name
     * @return Column
     */
    public function foreignId(string $name): Column
    {
        $column = $this->bigInteger($name)->unsigned();
        return $column;
    }


    // public function foreign(
    //     string|array $columns,
    //     string $table,
    //     string|array $references = ['id'],
    //     ?string $name = null
    // ): void {
    //     $columns = is_array($columns) ? $columns : [$columns];
    //     $references = is_array($references) ? $references : [$references];
    //     $name = $name ?? "fk_{$this->table}_" . implode('_', $columns);

    //     $sql = "CONSTRAINT {$name} FOREIGN KEY (" . implode(', ', $columns) . ") " .
    //         "REFERENCES {$table}(" . implode(', ', $references) . ")";

    //     $this->indexes[] = new ForeignKey($name, $columns, $table, $references);
    // }

    /**
     * Add a foreign key constraint
     *
     * @param string|array $columns Local column(s)
     * @param string $table Referenced table
     * @param string|array $references Referenced column(s)
     * @param string|null $name Constraint name
     * @return void
     */
    public function foreign(
        string|array $columns,
        string $table,
        string|array $references = ['id'],
        ?string $name = null
    ): ForeignKey {
        $columns = is_array($columns) ? $columns : [$columns];
        $references = is_array($references) ? $references : [$references];
        $name = $name ?? "fk_{$this->table}_" . implode('_', $columns);

        $foreignKey = new ForeignKey($name, $columns, $table, $references);
        $this->indexes[] = $foreignKey;

        return $foreignKey;
    }




    /**
     * Add an auto-incrementing ID column (shorthand for bigIncrements('id'))
     *
     * @return Column
     */
    public function id(): Column
    {
        //return $this->bigIncrements('id');
        return $this->increments('id');
    }
}
