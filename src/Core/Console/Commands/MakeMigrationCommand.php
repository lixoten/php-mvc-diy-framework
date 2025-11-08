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
        $entityName = $input->getArgument('entity');

        $io->title("Generating Migration for Entity: {$entityName}");

        try {
            // Load the schema for the given entity
            $schema = $this->schemaLoaderService->load($entityName);

            // Pass the schema to the MigrationGenerator
            $filePath = $this->migrationGenerator->generate($schema);
            $io->success("Migration for '{$entityName}' generated successfully at: {$filePath}");
            return Command::SUCCESS;
        } catch (SchemaDefinitionException $e) {
            $io->error("Schema error for '{$entityName}': " . $e->getMessage());
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $io->error("Error generating migration for '{$entityName}': " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
