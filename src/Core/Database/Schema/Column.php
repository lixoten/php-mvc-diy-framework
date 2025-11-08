<?php

declare(strict_types=1);

namespace Core\Database\Schema;

class Column
{
    private string $name;
    private string $type;
    private bool $nullable = false;
    private bool $unsigned = false;
    private bool $primary = false;
    private bool $hasDefault = false;
    private mixed $default = null;
    private ?string $comment = null;
    private array $attributes = [];

    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Make column nullable
     */
    public function nullable(bool $value = true): self
    {
        $this->nullable = $value;
        return $this;
    }

    /**
     * Make column unsigned
     */
    public function unsigned(bool $value = true): self
    {
        $this->unsigned = $value;
        return $this;
    }

    /**
     * Add AUTO_INCREMENT attribute
     */
    public function autoIncrement(): self
    {
        $this->attributes[] = 'AUTO_INCREMENT';
        return $this;
    }

    /**
     * Make column primary key
     */
    public function primary(): self
    {
        $this->primary = true;
        $this->attributes[] = 'PRIMARY KEY';
        return $this;
    }

    /**
     * Set default value
     */
    public function default(mixed $value): self
    {
        $this->hasDefault = true;
        $this->default = $value;
        return $this;
    }

    /**
     * Add comment
     */
    public function comment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * Make column unique
     */
    public function unique(): self
    {
        $this->attributes[] = 'UNIQUE';
        return $this;
    }

    /**
     * Get attributes array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Generate SQL for this column
     */
    public function toSql(): string
    {
        $parts = [$this->name];

        // Add type with UNSIGNED if needed
        $typeString = $this->type;
        if ($this->unsigned && !str_contains($typeString, 'UNSIGNED')) {
            $typeString .= ' UNSIGNED';
        }
        $parts[] = $typeString;

        // Add NULL/NOT NULL
        if ($this->nullable) {
            $parts[] = 'NULL';
        } else {
            $parts[] = 'NOT NULL';
        }

        // Add default value if specified
        if ($this->hasDefault) {
            if ($this->default === null) {
                $parts[] = 'DEFAULT NULL';
            } elseif (is_bool($this->default)) {
                $parts[] = 'DEFAULT ' . ($this->default ? '1' : '0');
            } elseif (is_numeric($this->default)) {
                $parts[] = 'DEFAULT ' . $this->default;
            } elseif (is_string($this->default) && !in_array($this->default, ['CURRENT_TIMESTAMP'])) {
                $parts[] = "DEFAULT '" . addslashes($this->default) . "'";
            } else {
                $parts[] = "DEFAULT " . $this->default;
            }
        }

        // Add PRIMARY KEY constraint if this column is primary
        if ($this->primary) {
            $parts[] = "PRIMARY KEY";
        }

        // Add comments if specified
        if ($this->comment !== null) {
            $parts[] = "COMMENT '" . addslashes($this->comment) . "'";
        }

        // Add AUTO_INCREMENT if needed
        if (in_array('AUTO_INCREMENT', $this->attributes)) {
            if (!in_array('AUTO_INCREMENT', $parts)) {
                $parts[] = 'AUTO_INCREMENT';
            }
        }

        return implode(' ', $parts);
    }
}
