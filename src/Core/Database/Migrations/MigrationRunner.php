<?php
// filepath: d:\xampp\htdocs\mvclixo\src\Core\Database\Migrations\MigrationRunner.php

declare(strict_types=1);

namespace Core\Database\Migrations;

use Core\Database\ConnectionInterface;
use Psr\Log\LoggerInterface;

class MigrationRunner
{
    protected ConnectionInterface $db;
    protected MigrationRepository $repository;
    protected ?LoggerInterface $logger;
    protected string $path;
    protected string $namespace;

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

    public function run(): array
    {
        // Initialize migration repository
        $this->repository->createRepository();

        // Get already migrated files
        $migrated = $this->repository->getMigratedFiles();

        // Get available migration files
        $files = $this->getMigrationFiles();

        // Filter out already migrated files
        $pending = [];
        foreach ($files as $file) {
            $name = $this->getFilenameWithoutExtension($file);
            if (!in_array($name, $migrated)) {
                $pending[] = $file;
            }
        }

        if (empty($pending)) {
            $this->log('No pending migrations found.');
            return [];
        }

        $batch = $this->repository->getLastBatchNumber() + 1;
        $migrations = [];

        // Run each pending migration
        foreach ($pending as $file) {
            $migration = $this->runMigration($file, $batch);
            if ($migration) {
                $migrations[] = $migration;
            }
        }

        return $migrations;
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

        try {
            // Create migration instance
            if (!class_exists($class)) {
                return null;
            }

            $instance = new $class($this->db);

            // Run migration inside transaction
            $this->db->transaction(function () use ($instance) {
                $instance->up();
            });

            // Log successful migration
            $this->repository->log($name, $batch);
            $this->log("Migrated: {$name}");

            return $name;
        } catch (\Throwable $e) {
            $this->log("Migration failed: {$name} - {$e->getMessage()}", 'error');
            return null;
        }
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

