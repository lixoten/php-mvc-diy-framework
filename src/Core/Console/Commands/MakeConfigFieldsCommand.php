<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Console\Generators\ConfigFieldsGenerator;
use Core\Exceptions\SchemaDefinitionException;
use Core\Services\SchemaLoaderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to generate a feature-specific fields configuration file.
 *
 * This command uses the ConfigFieldsGenerator to create a `{entityName}_fields_{configType}.php`
 * file based on the entity's schema definition, placing it in the feature's Config directory.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class MakeConfigFieldsCommand extends Command
{
    protected static $defaultName = 'make:config-fields';
    protected static $defaultDescription = 'Generates a feature-specific fields configuration file from schema.';

    /**
     * @param ConfigFieldsGenerator $configFieldsGenerator The service for generating field config files.
     * @param SchemaLoaderService $schemaLoaderService The service for loading schema definitions.
     */
    public function __construct(
        private ConfigFieldsGenerator $configFieldsGenerator,
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
            ->addArgument(
                'configType',
                InputArgument::OPTIONAL,
                'An optional type for the config (e.g., "list", "edit").',
                'root'
            );
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
        $configType = strtolower($input->getArgument('configType'));

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


        // --- VALIDATION ---
        $allowedConfigTypes = ['list', 'edit', 'root', 'base']; // Defined allowed types

        if (!in_array($configType, $allowedConfigTypes, true)) {
            $io->error("Invalid configType '{$configType}'. Allowed types are: " . implode(', ', $allowedConfigTypes));
            return Command::FAILURE;
        }
        // --- END VALIDATION ---

        $io->title("Generating Field Configuration for '{$entityName}'");

        try {
            // Load the schema for the given entity
            $schema = $this->schemaLoaderService->load($featureName, $tableName);

            // Pass the loaded schema (array) to the generator
            $filePath = $this->configFieldsGenerator->generate($schema, $featureName, $configType);
            $io->success("Field configuration for '{$entityName}' - '{$configType}'generated successfully at: {$filePath}");
            return Command::SUCCESS;
        } catch (SchemaDefinitionException $e) {
            $io->error("Schema error for '{$entityName}': " . $e->getMessage());
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $io->error("Error generating field configuration for '{$entityName}': " . $e->getMessage());
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
