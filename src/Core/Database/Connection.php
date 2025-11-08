<?php

declare(strict_types=1);

namespace Core\Database;

use Core\Exceptions\ConnectionException;
use Core\Exceptions\QueryException;
use Core\Interfaces\ConfigInterface;
use Psr\Log\LoggerInterface;

class Connection implements ConnectionInterface
{
    protected \PDO $pdo;
    protected array $connectionConfig;
    protected ConfigInterface $configService;
    protected ?LoggerInterface $logger;
    protected int $transactionLevel = 0;
    protected int $defaultFetchMode;

    public function __construct(
        array $connectionConfig,
        ConfigInterface $configService,
        ?LoggerInterface $logger = null,
    ) {
        $this->connectionConfig = $connectionConfig;
        $this->configService = $configService;
        $this->logger = $logger;
        $this->connect();
    }

    protected function connect(): void
    {
        try {
            // Create DSN based on driver
            $dsn = $this->createDsn($this->connectionConfig);

            // Extract username, password, and options
            $username = $this->connectionConfig['username'] ?? null;
            $password = $this->connectionConfig['password'] ?? null;
            $options = $this->connectionConfig['options'] ?? [];

            // Create PDO instance
            $this->pdo = new \PDO($dsn, $username, $password, $options);

            $appTimezone = $this->configService->get('app.timezone', date_default_timezone_get());
            $offset = (new \DateTime('now', new \DateTimeZone($appTimezone)))->format('P');
             $this->pdo->exec("SET time_zone = '$offset'");
        } catch (\PDOException $e) {
            throw new ConnectionException(
                "Failed to connect to database: {$e->getMessage()}",
                $this->connectionConfig['driver'] ?? 'unknown',
                $this->connectionConfig,
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
            $this->logger->warning("Slowaaaaaaaaa query: {$sql}");
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


    protected function logQuery(string $sql, array $params, float $start): void
    {
        $loggingEnabled = $this->configService->get('database.logging.enabled', false);
        $slowThreshold  = $this->configService->get('database.logging.slow_threshold', 1000);

        if (!$this->logger || !$loggingEnabled) {
            return;
        }

        $time = (microtime(true) - $start) * 1000;
        $logParams = $this->sanitizeParams($params);

        if ($time > $slowThreshold) {
            $this->logger->warning("Slow query: {$sql} ({$time}ms)", ['params' => $logParams]);
        } else {
            $this->logger->debug("Query: {$sql} ({$time}ms)", ['params' => $logParams]);
        }
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

        // The logQuery method will calculate the time based on $startTime
        $this->logQuery($sql, $params, $startTime);

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


    /**
     * Insert a record and return the last insert ID
     *
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return string|int Last insert ID
     */
    public function insert(string $table, array $data): string
    {
        $columns = array_keys($data);
        $placeholders = array_map(function ($col) {
            return ":{$col}";
        }, $columns);

        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") " .
            "VALUES (" . implode(', ', $placeholders) . ")";

        $this->execute($sql, $data);
        return $this->lastInsertId();
    }

    public function update(string $table, array $data, array $conditions): int
    {
        $setClauses = [];
        foreach (array_keys($data) as $column) {
            $setClauses[] = "{$column} = :{$column}";
        }

        $whereClauses = [];
        foreach (array_keys($conditions) as $column) {
            $whereClauses[] = "{$column} = :where_{$column}";
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $setClauses);
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $params = $data;
        foreach ($conditions as $key => $value) {
            $params["where_{$key}"] = $value;
        }

        return $this->execute($sql, $params);
    }

    public function delete(string $table, array $conditions): int
    {
        $whereClauses = [];
        foreach (array_keys($conditions) as $column) {
            $whereClauses[] = "{$column} = :{$column}";
        }

        $sql = "DELETE FROM {$table}";
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        return $this->execute($sql, $conditions);
    }

    public function insertBatch(string $table, array $records): int
    {
        if (empty($records)) {
            return 0;
        }

        // Get column names from first record
        $columns = array_keys($records[0]);

        // Build placeholders for all records
        $allPlaceholders = [];
        $params = [];

        foreach ($records as $index => $record) {
            $recordPlaceholders = [];
            foreach ($columns as $column) {
                $param = ":{$column}_{$index}";
                $recordPlaceholders[] = $param;
                $params[$param] = $record[$column];
            }
            $allPlaceholders[] = '(' . implode(', ', $recordPlaceholders) . ')';
        }

        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES " .
               implode(', ', $allPlaceholders);

        return $this->execute($sql, $params);
    }

    public function fetch(string $sql, array $params = [], int $fetchMode = \PDO::FETCH_ASSOC)
    {
        return $this->fetchOne($sql, $params, $fetchMode);
    }

    public function fetchAll(string $sql, array $params = [], int $fetchMode = \PDO::FETCH_ASSOC): array
    {
        $stmt = $this->executeLogged($sql, $params);
        return $stmt->fetchAll($fetchMode);
    }
}
