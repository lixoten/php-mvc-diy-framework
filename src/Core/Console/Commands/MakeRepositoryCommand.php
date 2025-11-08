<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Core\Console\Generators\RepositoryGenerator;
use Core\Exceptions\SchemaDefinitionException;
use Core\Services\SchemaLoaderService;

/**
 * Console command to generate a repository and its interface for a given entity.
 *
 * This command uses the RepositoryGenerator to create the necessary files
 * based on the entity's schema definition.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class MakeRepositoryCommand extends Command
{
    protected static $defaultName = 'make:repository';
    protected static $defaultDescription = 'Generates a repository and its interface for an entity.';

    private RepositoryGenerator $repositoryGenerator;
    private SchemaLoaderService $schemaLoaderService;

    /**
     * @param RepositoryGenerator $repositoryGenerator The service for generating repositories.
     * @param SchemaLoaderService $schemaLoaderService The service for loading schema definitions.
     */
    public function __construct(
        RepositoryGenerator $repositoryGenerator,
        SchemaLoaderService $schemaLoaderService
    ) {
        parent::__construct();
        $this->repositoryGenerator = $repositoryGenerator;
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
            ->addArgument('entity', InputArgument::REQUIRED, 'The name of the entity (e.g., "Post" or "Testy").');
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

        $io->title("Generating Repository for '{$entityName}'");

        try {
            // Load the schema for the given entity
            $schema = $this->schemaLoaderService->load($entityName);

            // Pass the loaded schema (array) to the generator
            $generatedFilePaths = $this->repositoryGenerator->generate($schema);

            $filePaths = '';
            foreach ($generatedFilePaths as $path) {
                $filePaths .= "\n - {$path}";
            }

            $io->success("Repository and interface for '{$entityName}' generated successfully at: $filePaths");
            return Command::SUCCESS;
        } catch (SchemaDefinitionException $e) {
            $io->error("Schema error for '{$entityName}': " . $e->getMessage());
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $io->error("Error generating repository for '{$entityName}': " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
