<?php

declare(strict_types=1);

namespace Core\Database\Migrations;

echo "Including migration file: $migrationFile\n";

use Core\Database\ConnectionInterface;
use Psr\Log\LoggerInterface;

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
        $this->path = $path;
        $this->namespace = $namespace;
        $this->logger = $logger;
    }


    /**
     * Run all pending migrations
     *
     * @param bool $force Force run migrations even if already executed
     * @return array List of executed migrations
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
     * Get all migration files from directory
     *
     * @return array List of all migration file names
     */
    protected function getMigrationFiles(): array
    {
        if (!is_dir($this->path)) {
            return [];
        }

        $files = scandir($this->path);
        if ($files === false) {
            return [];
        }

        $files = array_filter($files, function ($file) {
            return !in_array($file, ['.', '..']) && pathinfo($file, PATHINFO_EXTENSION) === 'php';
        });

        sort($files);
        return $files;
    }

    protected function runMigration(string $file, int $batch): ?string
    {
        $name = $this->getFilenameWithoutExtension($file);
        $class = $this->getMigrationClass($name);

        $this->log("Migrating: {$name}");
        echo "DEBUG - Looking for class: $class\n";
        echo "DEBUG - File path: " . $this->path . '/' . $file . "\n";

        try {
            // Include the file explicitly before checking the class
            require_once $this->path . '/' . $file;

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
     * Run a single migration by class name.
     *
     * @param string $migrationClass
     * @param bool $force
     * @return bool True if migration executed, false if not found or already executed
     * @throws \Core\Exceptions\MigrationException
     */
   /**
     * Run a single migration by class name.
     *
     * @param string $migrationClass
     * @param bool $force
     * @return bool True if migration executed, false if not found or already executed
     * @throws \Exception
     */
    public function runSingleMigration(string $migrationClass, bool $force = false): bool
    {
        $migrationFile = $this->findMigrationFile($migrationClass);

        if ($migrationFile === null) {
            throw new \Exception("Migration class {$migrationClass} not found.");
        }

        // Use the filename as the canonical migration name (matches run() behavior)
        $name = $this->getFilenameWithoutExtension(basename($migrationFile));

        // Ensure repository exists and get executed list
        $this->repository->createRepository();
        $executed = $this->repository->getMigratedFiles();

        if (!$force && in_array($name, $executed, true)) {
            // Already executed
            return false;
        }

        // Capture declared classes before include to detect the class declared by the file
        $before = get_declared_classes();
        require_once $migrationFile;
        $after = get_declared_classes();
        $newClasses = array_diff($after, $before);

        // Determine short class name and try to resolve FQCN
        $parts = explode('\\', $migrationClass);
        $shortClass = end($parts);
        $fqcn = (strpos($migrationClass, '\\') === false)
            ? $this->namespace . '\\' . $shortClass
            : $migrationClass;

        if (!class_exists($fqcn)) {
            foreach ($newClasses as $declared) {
                $declParts = explode('\\', $declared);
                $declShort = end($declParts);
                if ($declShort === $shortClass) {
                    $fqcn = $declared;
                    break;
                }
            }
        }

        if (!class_exists($fqcn)) {
            throw new \Exception("Migration class {$migrationClass} not loaded.");
        }

        /** @var \Core\Database\Migrations\Migration $migration */
        $migration = new $fqcn($this->db);

        // If forcing, attempt to run down() first if it was previously executed
        if ($force && in_array($name, $executed, true)) {
            $migration->down();
            $this->repository->delete($name);
        }

        $migration->up();

        // Log using the filename-based name for consistency with run()
        $batch = $this->repository->getLastBatchNumber() + 1;
        $this->repository->log($name, $batch);

        $this->log("Single migration executed: {$name}");

        return true;
    }


    public function xxxxxrunSingleMigration(string $migrationClass, bool $force = false): bool
    {
        $migrationFile = $this->findMigrationFile($migrationClass);

        if ($migrationFile === null) {
            throw new \Exception("Migration class {$migrationClass} not found.");
        }

        require_once $migrationFile;

        if (!class_exists($migrationClass)) {
            throw new \Exception("Migration class {$migrationClass} not loaded.");
        }

        // Check if already migrated unless --force
        if (!$force && $this->repository->getMigratedFiles() && in_array($migrationClass, $this->repository->getMigratedFiles(), true)) {
            return false;
        }

        /** @var \Core\Database\Migrations\Migration $migration */
        $migration = new $migrationClass($this->db);

        if ($force) {
            $migration->down();
        }

        $migration->up();

        // Log migration with next batch number
        $batch = $this->repository->getLastBatchNumber() + 1;
        $this->repository->log($migrationClass, $batch);

        $this->log("Single migration executed: {$migrationClass}");

        return true;
    }

    /**
     * Find the migration file for a given class name.
     *
     * @param string $migrationClass
     * @return string|null
     */
    private function findMigrationFile(string $migrationClass): ?string
    {
        $files = glob($this->path . '/*.php');

        foreach ($files as $file) {
            $contents = file_get_contents($file);

            // Look for "class ClassName"
            // if (preg_match('/class\s+' . preg_quote($migrationClass, '/') . '\b/', $contents)) {
                // return $file;
            // }
            $parts = explode('\\', $migrationClass);
            $shortClass = end($parts);

            if (preg_match('/class\s+' . preg_quote($shortClass, '/') . '\b/', $contents)) {
                return $file;
            }
        }

        return null;
    }


    protected function rollbackMigration(string $name): bool
    {
        $class = $this->getMigrationClass($name);

        $this->log("Rolling back: {$name}");

        try {
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
        if (preg_match('/^\d+_(.+)$/', $name, $matches)) {
            $name = $matches[1];
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
