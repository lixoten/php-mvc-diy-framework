<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Core\Console\Generators\MigrationGenerator;
use Core\Services\SchemaLoaderService;
use Core\Exceptions\SchemaDefinitionException;

/**
 * Console command to generate a new database migration file from an entity schema.
 *
 * This command uses the MigrationGenerator to create a new migration file
 * in the database/migrations directory, based on the provided entity's schema definition.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class MakeMigrationCommand extends Command
{
    protected static string $defaultName = 'make:migration';
    protected static string $defaultDescription = 'Generates a new migration file from an entity schema.';

    private MigrationGenerator $migrationGenerator;
    private SchemaLoaderService $schemaLoaderService;

    /**
     * @param MigrationGenerator $migrationGenerator The service for generating migration files.
     * @param SchemaLoaderService $schemaLoaderService The service for loading schema definitions.
     */
    public function __construct(MigrationGenerator $migrationGenerator, SchemaLoaderService $schemaLoaderService)
    {
        parent::__construct();
        $this->migrationGenerator = $migrationGenerator;
        $this->schemaLoaderService = $schemaLoaderService;
    }

    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        $this
            ->setName(static::$defaultName)
            ->setDescription(static::$defaultDescription)
            ->addArgument('entity', InputArgument::REQUIRED, 'The name of the entity to generate a migration for (e.g., "Post" or "Testy").');
    }

    /**
     * Executes the command.
     *
     * @param InputInterface $input The input interface.
     * @param OutputInterface $output The output interface.
     * @return int The command exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $entityArgument = $input->getArgument('entity');

        $featureName = '';
        $entityName  = '';
        $tableName   = '';

        // Parse "Feature:Entity"
        if (str_contains($entityArgument, ':')) {
            [$featureName, $entityName] = explode(':', $entityArgument, 2);
        } else {
            $io->error("❌ Error: Missing feature prefix. Please use the format 'FeatureName:EntityName' (e.g., 'Image:PendingImageUpload').");
            return Command::FAILURE;
        }

        // Sanitize names for consistency (e.g., ensuring PascalCase)
        $tableName   = $entityName;
        $featureName = $this->sanitizeName($featureName);
        $entityName  = $this->sanitizeName($entityName);

        if (empty($featureName) || empty($entityName)) {
            $io->error('Invalid feature or entity name provided.');
            return Command::FAILURE;
        }

        $io->title("Generating Migration for Feature: {$featureName}, Table: {$tableName}");

        try {
            // Load the schema for the given entity and feature
            $schema = $this->schemaLoaderService->load($featureName, $tableName);

            // Pass the schema, featureName, and entityName to the MigrationGenerator
            $filePath = $this->migrationGenerator->generate($schema, $featureName, $entityName);
            $io->success("Migration for '{$featureName}:{$entityName}' generated successfully at: {$filePath}");
            $io->info("Remember to run 'php bin/console.php feature:move {$featureName}' to finalize its placement.");
            return Command::SUCCESS;
        } catch (SchemaDefinitionException $e) {
            $io->error("Schema error for '{$featureName}:{$entityName}': " . $e->getMessage());
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $io->error("Error generating migration for '{$featureName}:{$entityName}': " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Sanitizes a string to ensure it's in PascalCase by removing non-alphanumeric
     * characters and capitalizing words.
     *
     * @param string $name The input string (e.g., 'pending_image_upload', 'image-processor').
     * @return string The sanitized string in PascalCase (e.g., 'PendingImageUpload', 'ImageProcessor').
     */
    private function sanitizeName(string $name): string // ✅ ADDED: Helper method for sanitization
    {
        // Replace non-alphanumeric characters (including spaces, hyphens, underscores) with a space
        // Then capitalize the first letter of each word and remove spaces
        return str_replace(' ', '', ucwords(preg_replace('/[^a-zA-Z0-9\s]/', ' ', str_replace(['-', '_'], ' ', $name))));
    }
}
