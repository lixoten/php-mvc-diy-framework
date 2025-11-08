<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Generators\FieldConfigGenerator;
use Core\Exceptions\SchemaDefinitionException;
use Core\Services\SchemaLoaderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to generate a feature-specific field configuration file.
 *
 * This command uses the FieldConfigGenerator to create a `field_{entityName}.php`
 * file based on the entity's schema definition, placing it in the feature's Config directory.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class MakeFieldConfigCommand extends Command
{
    protected static $defaultName = 'make:field-config';
    protected static $defaultDescription = 'Generates a feature-specific field configuration file from schema.';

    private FieldConfigGenerator $fieldConfigGenerator;
    private SchemaLoaderService $schemaLoaderService;

    /**
     * @param FieldConfigGenerator $fieldConfigGenerator The service for generating field config files.
     * @param SchemaLoaderService $schemaLoaderService The service for loading schema definitions.
     */
    public function __construct(
        FieldConfigGenerator $fieldConfigGenerator,
        SchemaLoaderService $schemaLoaderService
    ) {
        parent::__construct();
        $this->fieldConfigGenerator = $fieldConfigGenerator;
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
            ->addArgument('entity', InputArgument::REQUIRED, 'The name of the entity (e.g., "Testy").');
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

        $io->title("Generating Field Configuration for '{$entityName}'");

        try {
            // Load the schema for the given entity
            $schema = $this->schemaLoaderService->load($entityName); // <-- MODIFIED: Load schema

            // Pass the loaded schema (array) to the generator
            $filePath = $this->fieldConfigGenerator->generate($schema); // <-- MODIFIED: Pass schema array
            $io->success("Field configuration for '{$entityName}' generated successfully at: {$filePath}");
            return Command::SUCCESS;
        } catch (SchemaDefinitionException $e) {
            $io->error("Schema error for '{$entityName}': " . $e->getMessage());
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $io->error("Error generating field configuration for '{$entityName}': " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
