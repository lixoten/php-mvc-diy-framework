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

    /**
     * Set ON DELETE action
     *
     * @param string $action RESTRICT, CASCADE, SET NULL, NO ACTION
     * @return $this
     */
    public function onDelete(string $action): self
    {
        $this->onDelete = $action;
        return $this;
    }

    /**
     * Set ON UPDATE action
     *
     * @param string $action RESTRICT, CASCADE, SET NULL, NO ACTION
     * @return $this
     */
    public function onUpdate(string $action): self
    {
        $this->onUpdate = $action;
        return $this;
    }

    /**
     * Generate SQL for this foreign key constraint
     *
     * @return string
     */
    public function toSql(): string
    {
        return "CONSTRAINT {$this->name} FOREIGN KEY (" . implode(', ', $this->columns) . ") " .
               "REFERENCES {$this->referencedTable}(" . implode(', ', $this->referencedColumns) . ") " .
               "ON DELETE {$this->onDelete} ON UPDATE {$this->onUpdate}";
    }
}
