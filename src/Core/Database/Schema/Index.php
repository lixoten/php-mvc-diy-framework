<?php

declare(strict_types=1);

namespace Core\Database\Schema;

class Index
{
    protected string $name;
    protected array $columns;
    protected string $type;

    public function __construct(string $name, array $columns, string $type = 'INDEX')
    {
        $this->name = $name;
        $this->columns = $columns;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function toSql(): string
    {
        if ($this->type === 'PRIMARY KEY') {
            return "PRIMARY KEY (" . implode(', ', $this->columns) . ")";
        }

        return "{$this->type} {$this->name} (" . implode(', ', $this->columns) . ")";
    }
}
