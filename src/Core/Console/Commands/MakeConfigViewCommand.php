<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Generators\ConfigViewGenerator;
use Core\Exceptions\SchemaDefinitionException;
use Core\Services\SchemaLoaderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to generate a feature-specific view configuration file.
 *
 * This command uses the ConfigViewGenerator to create a `{entityName}_view_{configType}.php`
 * file based on the entity's schema definition, placing it in the feature's Config directory.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class MakeConfigViewCommand extends Command
{
    protected static $defaultName = 'make:config-view';
    protected static $defaultDescription = 'Generates a feature-specific view configuration file from schema.';

    /**
     * @param ConfigViewGenerator $configViewGenerator The service for generating view config files.
     * @param SchemaLoaderService $schemaLoaderService The service for loading schema definitions.
     */
    public function __construct(
        private ConfigViewGenerator $configViewGenerator,
        private SchemaLoaderService $schemaLoaderService
    ) {
        parent::__construct();
    }

    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        $this
            ->setName(static::$defaultName)
            ->setDescription(static::$defaultDescription)
            ->addArgument('entity', InputArgument::REQUIRED, 'The name of the entity (e.g., "Testy").')
            ->addArgument('configType', InputArgument::OPTIONAL, 'An optional type for the config (e.g., "list", "edit").', 'list');
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
        $configType = strtolower($input->getArgument('configType'));

        // --- VALIDATION ---
        $allowedConfigTypes = ['list', 'edit']; // Defined allowed types

        if (!in_array($configType, $allowedConfigTypes, true)) {
            $io->error("Invalid configType '{$configType}'. Allowed types are: " . implode(', ', $allowedConfigTypes));
            return Command::FAILURE;
        }
        // --- END VALIDATION ---

        $io->title("Generating View Configuration for '{$entityName}'");

        try {
            // Load the schema for the given entity
            $schema = $this->schemaLoaderService->load($entityName);

            // Pass the loaded schema (array) to the generator
            $filePath = $this->configViewGenerator->generate($schema, $configType);
            $io->success("View configuration for '{$entityName}' - '{$configType}'generated successfully at: {$filePath}");
            return Command::SUCCESS;
        } catch (SchemaDefinitionException $e) {
            $io->error("Schema error for '{$entityName}': " . $e->getMessage());
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $io->error("Error generating view configuration for '{$entityName}': " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
