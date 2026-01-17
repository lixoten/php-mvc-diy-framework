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
        $entityArgument = $input->getArgument('entity');

        $featureName = '';
        $entityName = '';

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

        $io->title("Generating Entity for Feature: {$featureName}, Table: {$tableName}");

        try {
            // Load the schema for the given entity and feature
            $schema = $this->schemaLoaderService->load($featureName, $tableName);

            // Pass the loaded schema (array) to the generator
            $filePath = $this->entityGenerator->generate($schema, $featureName, $entityName);
            $io->success("Entity for '{$featureName}:{$entityName}' generated successfully at: {$filePath}");
            return Command::SUCCESS;
        } catch (SchemaDefinitionException $e) {
            $io->error("Schema error for '{$entityName}': " . $e->getMessage());
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $io->error("Error generating entity for '{$entityName}': " . $e->getMessage());
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
    private function sanitizeName(string $name): string 
    {
        // Replace non-alphanumeric characters (including spaces, hyphens, underscores) with a space
        // Then capitalize the first letter of each word and remove spaces
        return str_replace(' ', '', ucwords(preg_replace('/[^a-zA-Z0-9\s]/', ' ', str_replace(['-', '_'], ' ', $name))));
    }
}
