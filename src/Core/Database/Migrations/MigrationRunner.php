<?php

declare(strict_types=1);

namespace Core\Database\Migrations;

use Core\Database\ConnectionInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class MigrationRunner
{
    protected ConnectionInterface $db;
    protected MigrationRepository $repository;
    protected ?LoggerInterface $logger;
    protected string $path;
    protected string $namespace;
    protected bool $continueOnError = false;

    public function __construct(
        ConnectionInterface $db,
        MigrationRepository $repository,
        string $path,
        string $namespace = 'Database\\Migrations',
        ?LoggerInterface $logger = null
    ) {
        $this->db = $db;
        $this->repository = $repository;
        $this->path = rtrim($path, '/\\'); // Ensure no trailing slash
        $this->namespace = $namespace;
        $this->logger = $logger;
    }


    /**
     * Run all pending migrations
     *
     * @param bool $force Force run migrations even if already executed
     * @return array<int, string> List of executed migrations
     */
    public function run(bool $force = false): array
    {
        // Ensure migrations table exists BEFORE querying it
        $this->repository->createRepository();

        // Now it's safe to get migrated files
        $files = $this->getMigrationFiles();
        $executedFiles = $this->repository->getMigratedFiles();

        // If force flag is used, run all migrations regardless
        if ($force) {
            $pendingFiles = $files;
        } else {
            // Otherwise only run pending migrations
            $pendingFiles = array_filter($files, function ($file) use ($executedFiles) {
                $name = $this->getFilenameWithoutExtension($file);
                return !in_array($name, $executedFiles);
            });
        }


        echo "DEBUG - Total files: " . count($files) . "\n";
        echo "DEBUG - Already executed: " . count($executedFiles) . "\n";
        echo "DEBUG - Pending: " . count($pendingFiles) . "\n";

        if (empty($pendingFiles)) {
            $this->log("No pending migrations to run.", 'info');
            return [];
        }

        $batch = $this->repository->getLastBatchNumber() + 1;

        $migrated = [];

        foreach ($pendingFiles as $file) {
            echo "DEBUG - Trying to run migration: $file\n";
            try {
                $name = $this->runMigration($file, $batch);
                if ($name) {
                    $migrated[] = $name;
                }
            } catch (\Exception $e) {
                echo "DEBUG - FAILED: " . $e->getMessage() . "\n";
                $this->log("Error: " . $e->getMessage(), 'error');

                // Stop running migrations if one fails
                if (!$this->continueOnError) {
                    throw $e;
                }
            }
        }

        return $migrated;
    }


    public function rollback(int $steps = 1): array
    {
        // Get last batch
        $lastBatch = $this->repository->getLastBatchNumber();
        $migrations = [];

        // Roll back specified number of batches
        for ($i = 0; $i < $steps && $lastBatch - $i > 0; $i++) {
            $batch = $lastBatch - $i;
            $batchMigrations = $this->repository->getMigrationsByBatch($batch);

            // Roll back each migration in reverse order
            foreach (array_reverse($batchMigrations) as $migration) {
                $file = $migration['migration'];
                $this->rollbackMigration($file);
                $migrations[] = $file;
            }
        }

        return $migrations;
    }

    /**
     * Get list of pending migrations
     *
     * @return array List of migration file names that need to be run
     */
    public function getPendingMigrations(): array
    {
        // Get all migration files from the directory
        $allMigrations = $this->getMigrationFiles();

        // Get list of migrations that have already been run
        $executedMigrations = $this->repository->getMigratedFiles();

        // Return migrations that haven't been run yet
        return array_diff($allMigrations, $executedMigrations);
    }

    /**
     * Get all migration files from directory, sorted by their prefix.
     *
     * @return array<int, string> List of all migration file names
     */
    protected function getMigrationFiles(): array
    {
        if (!is_dir($this->path)) {
            $this->log("Migration path does not exist: {$this->path}", 'error'); // Added logging
            return [];
        }

        $files = array_filter(scandir($this->path), function ($file) {
            return !in_array($file, ['.', '..']) && pathinfo($file, PATHINFO_EXTENSION) === 'php';
        });

        // --- START CHANGE 1: Custom sorting for migration files ---
        // Sort files based on their numeric or timestamp prefix
        usort($files, function (string $a, string $b): int {
            // Extract the prefix (e.g., "005" or "20251102_084221")
            // This regex captures the leading digits until an underscore or end of string
            preg_match('/^(\d+)(_|$)/', $a, $matchesA);
            $prefixA = $matchesA[1] ?? '';

            preg_match('/^(\d+)(_|$)/', $b, $matchesB);
            $prefixB = $matchesB[1] ?? '';

            // Compare prefixes as integers to ensure correct numerical and chronological order
            // This handles both NNN_ and YYYYMMDD_HHMMSS_ prefixes correctly as numbers
            return (int)$prefixA <=> (int)$prefixB;
        });
        // --- END CHANGE 1 ---

        return array_values($files); // Re-index the array
    }

    protected function runMigration(string $file, int $batch): ?string
    {
        $name = $this->getFilenameWithoutExtension($file);
        $class = $this->getMigrationClass($name);

        $filePath = $this->path . DIRECTORY_SEPARATOR . $file;

        $this->log("Migrating: {$name}");
        echo "DEBUG - Looking for class: $class\n";
        echo "DEBUG - File path: {$filePath}\n";

        try {
            // Include the file explicitly before checking the class
            require_once $filePath;

            if (!class_exists($class)) {
                echo "DEBUG - Class not found even after including file: $class\n";
                return null;
            }

            $instance = new $class($this->db);

            // Run migration directly instead of using transaction
            $instance->up();

            // Log successful migration
            $this->repository->log($name, $batch);
            //$this->repository->logMigration($name, $batch);

            $this->log("Migrated: {$name}");

            return $name;
        } catch (\Throwable $e) {
            echo "DEBUG - Migration error: " . $e->getMessage() . "\n";
            $this->log("Migration failed: {$name} - {$e->getMessage()}", 'error');
            throw $e; // Re-throw so it's caught by the run() method
        }
    }


     /**
     * Run a single migration by its identifier (filename without .php extension).
     *
     * @param string $migrationIdentifier The unique identifier of the migration (e.g., "20251101_135245_CreateTestyTable").
     * @param bool $force Whether to force the migration to run even if already migrated.
     * @return bool True if the migration was executed, false if not found or already executed.
     * @throws \Exception If the migration file is not found or execution fails.
     */
    public function runSingleMigration(string $migrationIdentifier, bool $force = false): bool // MODIFIED
    {
        $this->repository->createRepository(); // Ensure migrations table exists

        //$filePath = $this->path . '/' . $migrationIdentifier . '.php';
        $filePath = $this->path . DIRECTORY_SEPARATOR . $migrationIdentifier . '.php';

        if (!file_exists($filePath)) {
            throw new \Exception("Migration file '{$migrationIdentifier}.php' not found at '{$this->path}'.");
        }

        // Get the fully qualified class name using the existing helper
        // This correctly strips the timestamp and adds the namespace.
        $fqcn = $this->getMigrationClass($migrationIdentifier); // MODIFIED: Use migrationIdentifier directly

        // Check if already executed
        $executed = $this->repository->getMigratedFiles();
        if (!$force && in_array($migrationIdentifier, $executed, true)) {
            $this->log("Migration '{$migrationIdentifier}' already executed. Skipping.", 'info');
            return false;
        }

        // Include the file explicitly to make the class available
        require_once $filePath;

        if (!class_exists($fqcn)) {
            throw new \Exception("Migration class '{$fqcn}' not found in file '{$filePath}'. Check namespace and class name.");
        }

        /** @var \Core\Database\Migrations\Migration $migration */
        $migration = new $fqcn($this->db);

        // If forcing, attempt to run down() first if it was previously executed
        if ($force && in_array($migrationIdentifier, $executed, true)) {
            $this->log("Forcing re-run of '{$migrationIdentifier}'. Rolling back first.", 'info');
            try {
                $migration->down();
                $this->repository->delete($migrationIdentifier); // Remove old log entry
            } catch (Throwable $e) {
                $this->log("Forced rollback of '{$migrationIdentifier}' failed: " . $e->getMessage(), 'error');
                throw new \Exception("Forced rollback failed for '{$migrationIdentifier}': " . $e->getMessage(), 0, $e);
            }
        }

        $this->log("Running single migration: {$migrationIdentifier}");
        try {
            $migration->up();
        } catch (Throwable $e) {
            $this->log("Migration '{$migrationIdentifier}' failed: " . $e->getMessage(), 'error');
            throw new \Exception("Migration '{$migrationIdentifier}' failed: " . $e->getMessage(), 0, $e);
        }

        // Log the migration
        $batch = $this->repository->getLastBatchNumber() + 1;
        $this->repository->log($migrationIdentifier, $batch);

        $this->log("Single migration '{$migrationIdentifier}' executed successfully.");

        return true;
    }

    protected function rollbackMigration(string $name): bool
    {
        $class = $this->getMigrationClass($name);
        $filePath = $this->path . DIRECTORY_SEPARATOR . $name . '.php'; // ADDED: Construct file path

        $this->log("Rolling back: {$name}");

        try {
            if (!class_exists($class, false)) {
                require_once $filePath;
            }

            if (!class_exists($class)) {
                $this->log("Class {$class} not found for rollback even after including file: {$filePath}", 'error'); // ADDED: Log message
                throw new \RuntimeException("Migration class {$class} not found for rollback.");
            }

            // Create migration instance
            $instance = new $class($this->db);

            // Run rollback inside transaction
            $this->db->transaction(function () use ($instance) {
                $instance->down();
            });

            // Delete from migration repository
            $this->repository->delete($name);
            $this->log("Rolled back: {$name}");

            return true;
        } catch (\Throwable $e) {
            $this->log("Rollback failed: {$name} - {$e->getMessage()}", 'error');
            // CHANGED: Re-throw if continueOnError is false, or if it's a critical error
            if (!$this->continueOnError) {
                throw $e;
            }
            return false;
        }
    }

    protected function getFilenameWithoutExtension(string $file): string
    {
        return pathinfo($file, PATHINFO_FILENAME);
    }

    protected function getMigrationClass(string $name): string
    {
        // Strip timestamp prefix if present (format: YYYYMMDDHHMMSS_ClassName)
        // if (preg_match('/^\d+_(.+)$/', $name, $matches)) {
        //if (preg_match('/^\d{8}_\d{6}_(.+)$/', $name, $matches)) {
        if (preg_match('/^(\d{8}_\d{6}_|\d+_)(.+)$/', $name, $matches)) { // Line 233


            $name = $matches[2];
        }

        return $this->namespace . '\\' . $name;
    }

    protected function log(string $message, string $level = 'info'): void
    {
        if ($this->logger) {
            $this->logger->{$level}($message);
        }
    }
}
