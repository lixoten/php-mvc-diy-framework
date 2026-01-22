<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Services\ConfigValidatorService;
use Core\Services\ValidationResult;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// TODO unittest
/**
 * CLI Command: Validate Configuration Files
 *
 * Validates configuration files for structural integrity and business rules.
 *
 * Responsibilities (SRP):
 * - Parse CLI arguments (feature, type, verbose)
 * - Delegate validation to ConfigValidatorService
 * - Format and display validation results
 * - Return appropriate exit codes for CI/CD
 *
 * This command does NOT:
 * - Perform validation logic (ConfigValidatorService's job)
 * - Load config files (ConfigService's job)
 * - Define validation rules (schema classes' job)
 *
 * @package Core\Console\Commands
 */
class ValidateConfigCommand extends Command
{
    /**
     * Constructor
     *
     * @param ConfigValidatorService $configValidatorService Validation orchestrator
     * @param LoggerInterface $logger For logging validation operations
     */
    public function __construct(
        private ConfigValidatorService $configValidatorService,
        private LoggerInterface $logger
    ) {
        // ✅ CRITICAL: Call parent constructor BEFORE setName()
        parent::__construct();
    }

    /**
     * Configure command name, description, options, and help text
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            // ✅ EXPLICIT: Set command name here (more reliable than static property)
            ->setName('config:validate')
            ->setDescription('Validate configuration files for structural integrity and business rules')
            ->setHelp(
                <<<'HELP'
The <info>config:validate</info> command validates configuration files for:
- Structural integrity (required keys, correct types)
- Business rules (parameter naming conventions, repository class existence)
- Authorization config validity (owner_field, allowed_roles)

<comment>Examples:</comment>
  <info>php bin/console.php config:validate</info>
    Validate all features, all config types

  <info>php bin/console.php config:validate --feature=Testy</info>
    Validate only Testy feature (all config types)

  <info>php bin/console.php config:validate --type=model_bindings</info>
    Validate only model_bindings.php files (all features)

  <info>php bin/console.php config:validate --feature=Testy --type=model_bindings</info>
    Validate Testy/model_bindings.php only

  <info>php bin/console.php config:validate --verbose</info>
    Show all files (including valid ones)

<comment>Supported Config Types:</comment>
  - model_bindings (src/App/Features/*/Config/model_bindings.php)
  - all_fields (src/App/Features/*/Config/*_fields.php)

<comment>Exit Codes:</comment>
  0 = All config files valid
  1 = One or more validation errors found
HELP
            )
            ->addOption(
                'feature',
                'f',
                InputOption::VALUE_REQUIRED,
                'Validate only this feature (e.g., Testy, Post)'
            )
            ->addOption(
                'type',
                't',
                InputOption::VALUE_REQUIRED,
                'Validate only this config type (model_bindings, all_fields)'
            );
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input CLI input (options, arguments)
     * @param OutputInterface $output CLI output stream
     * @return int Exit code (0 = success, 1 = errors)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // ✅ Parse options
        $featureFilter = $input->getOption('feature');
        $typeFilter = $input->getOption('type');
        $verbose = $output->isVerbose();

        // ✅ Build config types array
        $configTypes = [];
        if ($typeFilter) {
            $validTypes = ['model_bindings', 'all_fields'];
            if (!in_array($typeFilter, $validTypes, true)) {
                $io->error("Invalid config type: {$typeFilter}. Valid types: " . implode(', ', $validTypes));
                return Command::FAILURE;
            }
            $configTypes = [$typeFilter];
        }

        // ✅ Display header
        $io->title('Config Validation');
        $io->text([
            'Validating configuration files...',
            $featureFilter ? "Feature: <info>{$featureFilter}</info>" : 'Features: <info>All</info>',
            $typeFilter ? "Config Type: <info>{$typeFilter}</info>" : 'Config Types: <info>All</info>',
        ]);
        $io->newLine();

        // ✅ Run validation
        $this->logger->info('Starting config validation', [
            'feature_filter' => $featureFilter,
            'type_filter' => $typeFilter,
        ]);

        $results = $this->getValidationResults($featureFilter, $configTypes);

        // ✅ Filter out null results (config files that don't exist)
        $results = array_filter($results, fn($result) => $result !== null);

        if (empty($results)) {
            $io->warning('No config files found to validate.');
            return Command::SUCCESS;
        }

        // ✅ Display results
        $totalFiles = count($results);
        $invalidFiles = array_filter($results, fn($result) => !$result->isValid());
        $invalidCount = count($invalidFiles);
        $validCount = $totalFiles - $invalidCount;

        // ✅ Show summary at top
        $io->section('Summary');
        $io->text([
            "Total files checked: <info>{$totalFiles}</info>",
            "Valid: <fg=green>{$validCount}</>",
            "Invalid: " . ($invalidCount > 0 ? "<fg=red>{$invalidCount}</>" : "<fg=green>0</>"),
        ]);
        $io->newLine();

        // ✅ Display detailed results
        $io->section('Detailed Results');

        foreach ($results as $result) {
            if ($result->isValid()) {
                if ($verbose) {
                    $io->success("✅ {$result->getConfigFilePath()}");
                }
            } else {
                $this->displayInvalidResult($io, $result);
            }
        }

        // ✅ Final status
        if ($invalidCount === 0) {
            $io->success("All {$totalFiles} config files are valid! 🎉");
            return Command::SUCCESS;
        }

        $io->error("{$invalidCount} config file(s) have validation errors. Please fix them before deploying.");
        return Command::FAILURE;
    }

    /**
     * Get validation results (handles single feature or all features)
     *
     * @param string|null $featureFilter Feature name filter
     * @param array<string> $configTypes Config types to validate
     * @return array<ValidationResult|null> Array of validation results
     */
    private function getValidationResults(?string $featureFilter, array $configTypes): array
    {
        if ($featureFilter) {
            // ✅ Validate single feature
            $results = [];
            $typesToValidate = empty($configTypes) ? ['model_bindings', 'all_fields'] : $configTypes;

            foreach ($typesToValidate as $configType) {
                $result = $this->configValidatorService->validateFeatureConfig($featureFilter, $configType);
                if ($result !== null) {
                    $results[] = $result;
                }
            }

            return $results;
        }

        // ✅ Validate all features
        return $this->configValidatorService->validateAllFeatures($configTypes);
    }

    /**
     * Display detailed error information for invalid config file
     *
     * @param SymfonyStyle $io Symfony console style helper
     * @param ValidationResult $result Validation result object
     * @return void
     */
    private function displayInvalidResult(SymfonyStyle $io, ValidationResult $result): void
    {
        $io->error([
            "❌ {$result->getConfigFilePath()}",
            "Feature: {$result->getFeatureName()}",
            "Type: {$result->getConfigType()}",
            "Errors: {$result->getErrorCount()}",
        ]);

        // ✅ Display each error with indentation
        $io->listing($result->getErrors());

        $io->newLine();
    }
}
