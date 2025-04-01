<?php

declare(strict_types=1);

namespace Core\Database;

interface ConnectionInterface
{
    // Query methods
    public function query(string $sql, array $params = []): array;
    public function execute(string $sql, array $params = []): int;
    public function lastInsertId(): string;
    public function prepare(string $sql): \PDOStatement;

    // Transaction methods
    public function beginTransaction(): bool;
    public function commit(): bool;
    public function rollback(): bool;
    public function transaction(callable $callback);
}
