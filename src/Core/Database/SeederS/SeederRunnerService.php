<?php

declare(strict_types=1);

namespace Core\Database\Seeders;

use Core\Database\ConnectionInterface;
use Core\Services\PathResolverService;
use Symfony\Component\Console\Style\SymfonyStyle;
use RuntimeException;

/**
 * Service responsible for running database seeders.
 *
 * This service handles locating, loading, and executing seeder classes.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class SeederRunnerService
{
    private ConnectionInterface $db;
    private PathResolverService $pathResolverService;
    private string $seederNamespace = 'Database\\Seeders'; // Define the namespace

    /**
     * @param ConnectionInterface $db The database connection.
     * @param PathResolverService $pathResolverService The path resolver service.
     */
    public function __construct(ConnectionInterface $db, PathResolverService $pathResolverService)
    {
        $this->db = $db;
        $this->pathResolverService = $pathResolverService;
    }

    /**
     * Get a list of available seeder identifiers (lowercase entity names).
     *
     * @return array<string>
     */
    public function getAvailableSeeders(): array
    {
        $seederPath = $this->pathResolverService->getDatabaseSeedersPath();
        $files = scandir($seederPath);
        $seeders = [];
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $filenameWithoutExt = pathinfo($file, PATHINFO_FILENAME); // e.g., UserSeeder
                // filepath: d:\xampp\htdocs\my_projects\mvclixo\src\Core\Database\Seeders\SeederRunnerService.php
                // Assuming files are now named like UserSeeder.php, TestySeeder.php
                // We want to return 'user', 'testy'
                if (preg_match('/^(.+)Seeder$/', $filenameWithoutExt, $matches)) {
                    $seeders[] = strtolower($matches[1]); // e.g., user from UserSeeder
                }
            }
        }
        sort($seeders); // Sort them for consistent order
        return $seeders;
    }

    /**
     * Run a specific seeder by its entity name (e.g., 'user').
     *
     * @param string $entityName The entity name (e.g., 'user').
     * @param SymfonyStyle|null $io Optional SymfonyStyle for output.
     * @throws RuntimeException If the seeder class is not found or cannot be run.
     */
    public function run(string $entityName, ?SymfonyStyle $io = null): void // MODIFIED: Parameter name
    {
        $seederPath = rtrim($this->pathResolverService->getDatabaseSeedersPath(), '/\\');

        // filepath: d:\xampp\htdocs\my_projects\mvclixo\src\Core\Database\Seeders\SeederRunnerService.php
        // Derive the expected filename and class name from the entity name
        $shortSeederClassName = ucfirst($entityName) . 'Seeder'; // e.g., UserSeeder
        $filePath = $seederPath . DIRECTORY_SEPARATOR . $shortSeederClassName . '.php';
        $fqcn = $this->seederNamespace . '\\' . $shortSeederClassName; // e.g., Database\Seeders\UserSeeder

        if (!file_exists($filePath)) {
            throw new RuntimeException("Seeder file for entity '{$entityName}' not found at expected path: {$filePath}");
        }

        require_once $filePath; // Load the file to make the class available

        if (!class_exists($fqcn)) {
            throw new RuntimeException("Seeder class '{$fqcn}' not found in file '{$filePath}'. Check namespace and class name.");
        }

        $io?->text("  Running seeder for: <info>{$entityName}</info>");
        $seederInstance = new $fqcn($this->db);
        if (!$seederInstance instanceof SeederInterface) {
            throw new RuntimeException("Seeder class '{$fqcn}' must implement SeederInterface.");
        }
        $seederInstance->run();
        $io?->text("  <info>Completed seeder for: {$entityName}</info>");
    }

    /**
     * Run all available seeders.
     *
     * @param SymfonyStyle|null $io Optional SymfonyStyle for output.
     * @return array<string> List of executed seeder entity names.
     */
    public function runAll(?SymfonyStyle $io = null): array
    {
        $executedSeeders = [];
        $availableSeeders = $this->getAvailableSeeders(); // This returns lowercase entity names

        if (empty($availableSeeders)) {
            $io?->info('No seeders found to run.');
            return [];
        }

        foreach ($availableSeeders as $entityName) { // MODIFIED: Use $entityName
            try {
                $this->run($entityName, $io); // MODIFIED: Pass $entityName
                $executedSeeders[] = $entityName;
            } catch (\Throwable $e) {
                $io?->error("Failed to run seeder for '{$entityName}': " . $e->getMessage());
                // Decide if you want to stop on first error or continue
                // For now, we'll continue but log the error.
            }
        }
        return $executedSeeders;
    }
}
