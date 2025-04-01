<?php

declare(strict_types=1);

namespace Core\Database;

use Core\Exceptions\ConnectionException;
use Core\Exceptions\QueryException;
use Psr\Log\LoggerInterface;

class Connection implements ConnectionInterface
{
    protected \PDO $pdo;
    protected array $config;
    protected ?LoggerInterface $logger;
    protected int $transactionLevel = 0;
    protected int $defaultFetchMode;

    public function __construct(array $config, ?LoggerInterface $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->connect();
    }

    protected function connect(): void
    {
        try {
            // Create DSN based on driver
            $dsn = $this->createDsn($this->config);

            // Extract username, password, and options
            $username = $this->config['username'] ?? null;
            $password = $this->config['password'] ?? null;
            $options = $this->config['options'] ?? [];

            // Create PDO instance
            $this->pdo = new \PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            throw new ConnectionException(
                "Failed to connect to database: {$e->getMessage()}",
                $this->config['driver'] ?? 'unknown',
                $this->config,
                (string)$e->getCode(),   // Cast to string to match parameter type
                0,
                $e
            );
        }
    }

    public function isConnected(): bool
    {
        try {
            $this->pdo->query("SELECT 1");
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }


    protected function createDsn(array $config): string
    {
        $driver = $config['driver'] ?? 'mysql';

        switch ($driver) {
            case 'mysql':
                return "mysql:host={$config['host']};" .
                       "port={$config['port']};" .
                       "dbname={$config['database']};" .
                       "charset={$config['charset']}";

            case 'sqlite':
                return "sqlite:{$config['database']}";

            default:
                throw new ConnectionException(
                    "Unsupported database driver: {$driver}",
                    $driver,
                    $config
                );
        }
    }

    public function query(string $sql, array $params = []): array
    {
        try {
            $statement = $this->executeLogged($sql, $params);
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw $this->convertException($e, $sql, $params);
        }
    }

    public function execute(string $sql, array $params = []): int
    {
        try {
            $statement = $this->executeLogged($sql, $params);
            return $statement->rowCount();
        } catch (\PDOException $e) {
            throw $this->convertException($e, $sql, $params);
        }
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }


    /**
     * Prepare a SQL statement
     *
     * @param string $sql The SQL statement to prepare
     * @return \PDOStatement The prepared statement
     */
    public function prepare(string $sql): \PDOStatement
    {
        try {
            $startTime = microtime(true);
            $stmt = $this->pdo->prepare($sql);
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000; // in ms

            if ($this->logger) {
                $this->logger->debug("Prepare SQL: {$sql} | Time: {$executionTime}ms");
            }

            return $stmt;
        } catch (\PDOException $e) {
            throw $this->convertException($e, $sql, []);
        }
    }



    public function beginTransaction(): bool
    {
        if ($this->transactionLevel === 0) {
            $this->pdo->beginTransaction();
        } else {
            $this->pdo->exec("SAVEPOINT trans{$this->transactionLevel}");
        }

        $this->transactionLevel++;

        return true;
    }

    public function commit(): bool
    {
        if ($this->transactionLevel === 1) {
            $this->pdo->commit();
        }

        $this->transactionLevel = max(0, $this->transactionLevel - 1);

        return true;
    }

    public function rollback(): bool
    {
        if ($this->transactionLevel === 1) {
            $this->pdo->rollBack();
        } elseif ($this->transactionLevel > 1) {
            $this->pdo->exec("ROLLBACK TO SAVEPOINT trans" . ($this->transactionLevel - 1));
        }

        $this->transactionLevel = max(0, $this->transactionLevel - 1);

        return true;
    }

    public function transaction(callable $callback)
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    protected function logQuery(string $sql, array $params, float $start): void
    {
        if (!$this->logger || !($this->config['logging']['enabled'] ?? false)) {
            return;
        }

        $time = (microtime(true) - $start) * 1000;
        $threshold = $this->config['logging']['slow_threshold'] ?? 1000;

        if ($time > $threshold) {
            $this->logger->warning("Slow query: {$sql} ({$time}ms)", ['params' => $params]);
        } else {
            $this->logger->debug("Query: {$sql} ({$time}ms)", ['params' => $params]);
        }
    }

    protected function convertException(\PDOException $e, string $sql, array $params): QueryException
    {
        return new QueryException(
            $e->getMessage(),
            $sql,
            $params,
            $e->getCode(),
            (int)$e->getCode(),
            $e
        );
    }




    /**
     * Fetch a single record
     *
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @param int $fetchMode PDO fetch mode
     * @return array|object|null Single record or null if none found
     */
    public function fetchOne(string $sql, array $params = [], int $fetchMode = \PDO::FETCH_ASSOC)
    {
        $fetchMode = $fetchMode ?? $this->defaultFetchMode;

        $stmt = $this->executeStatement($sql, $params);
        $result = $stmt->fetch($fetchMode);
        return $result === false ? null : $result;
    }

    /**
     * Fetch a single column value
     *
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @param int $column Zero-indexed column number
     * @return mixed|null Column value or null
     */
    public function fetchColumn(string $sql, array $params = [], int $column = 0)
    {
        $stmt = $this->executeStatement($sql, $params);
        $result = $stmt->fetchColumn($column);
        return $result === false ? null : $result;
    }

    /**
     * Fetch results as objects of specified class
     *
     * @param string $sql SQL query
     * @param string $className Class to instantiate
     * @param array $params Parameters to bind
     * @return array Array of objects
     */
    public function fetchObjects(string $sql, string $className, array $params = [])
    {
        $stmt = $this->executeStatement($sql, $params);
        return $stmt->fetchAll(\PDO::FETCH_CLASS, $className);
    }


    /**
     * Execute a statement with type detection
     *
     * @param string $sql SQL statement
     * @param array $params Parameters to bind
     * @return \PDOStatement
     */
    protected function executeStatement(string $sql, array $params = [])
    {
        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $type = $this->determineType($value);

            if (is_int($key)) {
                // Positional parameter (indexed by number)
                $stmt->bindValue($key + 1, $value, $type);
            } else {
                // Named parameter (starts with : or @)
                $param = (strpos($key, ':') === 0) ? $key : ":{$key}";
                $stmt->bindValue($param, $value, $type);
            }
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * Determine PDO parameter type
     *
     * @param mixed $value Value to check
     * @return int PDO parameter type
     */
    protected function determineType($value): int
    {
        if (is_int($value)) {
            return \PDO::PARAM_INT;
        }

        if (is_bool($value)) {
            return \PDO::PARAM_BOOL;
        }

        if (is_null($value)) {
            return \PDO::PARAM_NULL;
        }

        return \PDO::PARAM_STR;
    }


    /**
     * Execute a query with logging
     *
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return \PDOStatement
     */
    protected function executeLogged(string $sql, array $params = [])
    {
        $startTime = microtime(true);
        $stmt = $this->executeStatement($sql, $params);
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // in ms

        if ($this->logger) {
            $logParams = $this->sanitizeParams($params);
            $this->logger->debug("SQL: {$sql} | Params: " . json_encode($logParams) . " | Time: {$executionTime}ms");

            // Log slow queries with warning level
            if ($executionTime > ($this->config['slow_threshold'] ?? 1000)) {
                $this->logger->warning("SLOW QUERY: {$sql} | Time: {$executionTime}ms");
            }
        }

        return $stmt;
    }

    /**
     * Sanitize parameters for logging
     *
     * @param array $params Parameters to sanitize
     * @return array Sanitized parameters
     */
    protected function sanitizeParams(array $params): array
    {
        $sanitized = [];

        foreach ($params as $key => $value) {
            // Mask sensitive data like passwords
            if (
                is_string($key) &&
                (stripos($key, 'password') !== false ||
                stripos($key, 'secret') !== false ||
                stripos($key, 'token') !== false)
            ) {
                $sanitized[$key] = '******';
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
