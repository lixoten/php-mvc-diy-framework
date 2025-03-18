<?php

declare(strict_types=1);

namespace Core\Exceptions;

class QueryException extends DatabaseException
{
    protected array $bindings;

    public function __construct(
        string $message,
        string $query,
        array $bindings = [],
        string $sqlState = '',
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $sqlState, $code, $previous);
        $this->setQuery($query);
        $this->bindings = $bindings;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }
}
