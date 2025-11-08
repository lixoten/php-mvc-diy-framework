<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Core\Console\Generators\EntityGenerator;
use Core\Exceptions\SchemaDefinitionException;
use Core\Services\SchemaLoaderService;

/**
 * Console command to generate an entity class for a given entity.
 *
 * This command uses the EntityGenerator to create the entity file
 * based on the entity's schema definition.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class MakeEntityCommand extends Command
{
    protected static $defaultName = 'make:entity';
    protected static $defaultDescription = 'Generates an entity class for an entity.';

    private EntityGenerator $entityGenerator;
    private SchemaLoaderService $schemaLoaderService;

    /**
     * @param EntityGenerator $entityGenerator The service for generating entity files.
     * @param SchemaLoaderService $schemaLoaderService The service for loading schema definitions.
     */
    public function __construct(
        EntityGenerator $entityGenerator,
        SchemaLoaderService $schemaLoaderService
    ) {
        parent::__construct();
        $this->entityGenerator = $entityGenerator;
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

        $io->title("Generating Entity for '{$entityName}'");

        try {
            // Load the schema for the given entity
            $schema = $this->schemaLoaderService->load($entityName);

            // Pass the loaded schema (array) to the generator
            $filePath = $this->entityGenerator->generate($schema);
            $io->success("Entity '{$entityName}' generated successfully at: {$filePath}");
            return Command::SUCCESS;
        } catch (SchemaDefinitionException $e) {
            $io->error("Schema error for '{$entityName}': " . $e->getMessage());
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $io->error("Error generating entity for '{$entityName}': " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
