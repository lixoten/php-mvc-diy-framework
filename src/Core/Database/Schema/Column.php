<?php

declare(strict_types=1);

namespace Core\Database\Schema;

class Column
{
    protected string $name;
    protected string $type;
    protected bool $nullable = false;
    protected bool $unsigned = false;
    protected $default = null;
    protected bool $hasDefault = false;
    protected ?string $comment = null;
    protected bool $isPrimary = false;
    protected array $attributes = []; // Add this property to store attributes like UNIQUE

    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function nullable(bool $value = true): self
    {
        $this->nullable = $value;
        return $this;
    }

    public function unsigned(bool $value = true): self
    {
        $this->unsigned = $value;
        return $this;
    }

    public function default($value): self
    {
        $this->default = $value;
        $this->hasDefault = true;
        return $this;
    }

    public function comment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function primary(): self
    {
        $this->isPrimary = true;
        return $this;
    }

    public function toSql(): string
    {
        $parts = [$this->name, $this->type];

        if ($this->unsigned) {
            $parts[] = 'UNSIGNED';
        }

        if ($this->nullable) {
            $parts[] = 'NULL';
        } else {
            $parts[] = 'NOT NULL';
        }

        if ($this->hasDefault) {
            if ($this->default === null) {
                $parts[] = 'DEFAULT NULL';
            } elseif (is_string($this->default) && $this->default !== 'CURRENT_TIMESTAMP') {
                $parts[] = "DEFAULT '{$this->default}'";
            } else {
                $parts[] = "DEFAULT {$this->default}";
            }
        }

        if ($this->isPrimary) {
            $parts[] = 'PRIMARY KEY';
        }

        if ($this->comment) {
            $parts[] = "COMMENT '{$this->comment}'";
        }

        return implode(' ', $parts);
    }

    // Add this method to your Column class:
    public function unique(): self
    {
        // Mark this column as unique
        $this->attributes[] = 'UNIQUE';
        return $this;
    }
}
