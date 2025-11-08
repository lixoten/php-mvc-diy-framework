<?php

declare(strict_types=1);

namespace Core\Database\Schema;

class CheckConstraint
{
    private string $name;
    private string $expression;

    public function __construct(string $name, string $expression)
    {
        $this->name = $name;
        $this->expression = $expression;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function toSql(): string
    {
        return "CONSTRAINT {$this->name} CHECK ({$this->expression})";
    }
}
