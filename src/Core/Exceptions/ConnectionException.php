<?php

declare(strict_types=1);

namespace Core\Exceptions;

class ConnectionException extends DatabaseException
{
    protected string $driver;
    protected array $config;

    public function __construct(
        string $message,
        string $driver,
        array $config = [],
        string $sqlState = '',
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $sqlState, $code, $previous);
        $this->driver = $driver;

        // Remove sensitive information from config
        $this->config = $config;
        if (isset($this->config['password'])) {
            $this->config['password'] = '********';
        }
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
