<?php

declare(strict_types=1);

namespace Core\Database\Schema;

class ForeignKey
{
    private string $name;
    private array $columns;
    private string $referencedTable;
    private array $referencedColumns;
    private string $onDelete = 'RESTRICT';
    private string $onUpdate = 'RESTRICT';

    public function __construct(
        string $name,
        array $columns,
        string $referencedTable,
        array $referencedColumns
    ) {
        $this->name = $name;
        $this->columns = $columns;
        $this->referencedTable = $referencedTable;
        $this->referencedColumns = $referencedColumns;
    }

    // The rest of your methods stay the same

    public function onDelete(string $action): self
    {
        $this->onDelete = strtoupper($action);
        return $this;
    }

    public function onUpdate(string $action): self
    {
        $this->onUpdate = strtoupper($action);
        return $this;
    }

    public function toSql(): string
    {
        return "CONSTRAINT {$this->name} FOREIGN KEY (" . implode(', ', $this->columns) . ") " .
               "REFERENCES {$this->referencedTable}(" . implode(', ', $this->referencedColumns) . ") " .
               "ON DELETE {$this->onDelete} ON UPDATE {$this->onUpdate}";
    }
}
