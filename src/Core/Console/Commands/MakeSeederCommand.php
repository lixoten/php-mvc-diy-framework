<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Generators\SeederGenerator;
use Core\Exceptions\SchemaDefinitionException;
use Core\Services\SchemaLoaderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to generate a new seeder class.
 *
 * This command uses the SeederGenerator to create a new seeder file
 * in the database/seeders directory.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class MakeSeederCommand extends Command
{
    protected static string $defaultName = 'make:seeder';
    protected static string $defaultDescription = 'Generates a new seeder class.';

    private SeederGenerator $seederGenerator;
    private SchemaLoaderService $schemaLoaderService;

    /**
     * @param SeederGenerator $seederGenerator The service for generating seeder files.
     * @param SchemaLoaderService $schemaLoaderService The service for loading schema definitions.
     */
    public function __construct(
        SeederGenerator $seederGenerator,
        SchemaLoaderService $schemaLoaderService
    ) {
        parent::__construct();
        $this->seederGenerator = $seederGenerator;
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
            ->addArgument('entity', InputArgument::REQUIRED, 'The name of the seeder class (e.g., "UsersSeeder").');
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

        $io->title("Generating Seeder for Entity: {$entityName}");

        try {
            // Load the schema for the given entity
            $schema = $this->schemaLoaderService->load($entityName);

            // Pass the schema to the SeederGenerator
            $filePath = $this->seederGenerator->generate($schema);
            $io->success("Seeder for '{$entityName}' generated successfully at: {$filePath}");
            return Command::SUCCESS;
        } catch (SchemaDefinitionException $e) { // filepath: d:\xampp\htdocs\my_projects\mvclixo\src\Core\Console\Commands\MakeSeederCommand.php
            $io->error("Schema error for '{$entityName}': " . $e->getMessage());
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $io->error("Error generating seeder for '{$entityName}': " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
