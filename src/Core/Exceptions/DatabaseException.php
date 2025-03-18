<?php

declare(strict_types=1);

namespace Core\Exceptions;

class DatabaseException extends \RuntimeException
{
    protected string $sqlState;
    protected ?string $query = null;

    public function __construct(string $message, string $sqlState = '', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->sqlState = $sqlState;
    }

    public function getSqlState(): string
    {
        return $this->sqlState;
    }

    public function setQuery(string $query): self
    {
        $this->query = $query;
        return $this;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }
}
