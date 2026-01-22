<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Config\Schema\ModelBindingsSchema;
use Core\Config\Schema\FieldsSchema;
use Core\Config\Schema\ViewSchema;
use Core\Interfaces\ConfigInterface;
use Psr\Log\LoggerInterface;

// TODO unittest
/**
 * Config Validator Service
 *
 * Orchestrates validation of configuration files using schema validators.
 *
 * Responsibilities (SRP):
 * - Load config files via ConfigService
 * - Select appropriate schema validator based on config type
 * - Aggregate validation errors from schema validators
 * - Return structured validation results
 *
 * This service does NOT:
 * - Define validation rules (schema classes handle this)
 * - Load config files directly (delegates to ConfigService)
 * - Throw exceptions (returns ValidationResult objects instead)
 * - Modify config data (read-only validation)
 *
 * Supported Config Types:
 * - 'model_bindings' → ModelBindingsSchema
 * - 'all_fields' → FieldsSchema
 *
 * Usage Example (CLI Command):
 * ```php
 * $validator = $container->get(ConfigValidatorService::class);
 *
 * // Validate all features
 * $results = $validator->validateAllFeatures();
 * foreach ($results as $result) {
 *     if (!$result->isValid()) {
 *         echo "❌ {$result->getConfigFilePath()}\n";
 *         foreach ($result->getErrors() as $error) {
 *             echo "   - {$error}\n";
 *         }
 *     }
 * }
 *
 * // Validate single feature
 * $result = $validator->validateFeatureConfig('Testy', 'model_bindings');
 * if ($result->isValid()) {
 *     echo "✅ Valid\n";
 * }
 * ```
 *
 * @package Core\Services
 */
class ConfigValidatorService
{
    /**
     * Base path for feature configs
     */
    private const FEATURES_BASE_PATH = __DIR__ . '/../../App/Features';

    /**
     * Constructor
     *
     * @param ConfigInterface $configService For loading config files
     * @param ModelBindingsSchema $modelBindingsSchema Validator for model_bindings.php
     * @param FieldsSchema $fieldsSchema Validator for form fields config
     * @param ViewSchema $viewSchema Validator for form view config
     * @param LoggerInterface $logger For logging validation operations
     */
    public function __construct(
        private ConfigInterface $configService,
        private ModelBindingsSchema $modelBindingsSchema,
        private FieldsSchema $fieldsSchema,
        private ViewSchema $viewSchema,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Validate all config files for all features
     *
     * @param array<string> $configTypes Config types to validate (default: all)
     *                                    Options: 'model_bindings', 'all_fields'
     * @return array<ValidationResult> Array of validation results
     */
    public function validateAllFeatures(array $configTypes = []): array
    {
        $results = [];
        $defaultConfigTypes = ['model_bindings', 'all_fields'];
        $typesToValidate = empty($configTypes) ? $defaultConfigTypes : $configTypes;

        $this->logger->info('Starting config validation for all features', [
            'config_types' => $typesToValidate,
        ]);

        // ✅ Scan Features directory for all features
        $featuresPath = self::FEATURES_BASE_PATH;
        if (!is_dir($featuresPath)) {
            $this->logger->error('Features directory not found', ['path' => $featuresPath]);
            return $results;
        }

        $featureDirs = array_filter(
            scandir($featuresPath) ?: [],
            fn($dir) => $dir !== '.' && $dir !== '..' && is_dir($featuresPath . '/' . $dir)
        );

        // ✅ Validate each feature
        foreach ($featureDirs as $featureName) {
            foreach ($typesToValidate as $configType) {
                $result = $this->validateFeatureConfig($featureName, $configType);
                if ($result !== null) {
                    $results[] = $result;
                }
            }
        }

        $this->logger->info('Config validation completed', [
            'total_files_checked' => count($results),
            'invalid_files' => count(array_filter($results, fn($r) => !$r->isValid())),
        ]);

        return $results;
    }

    /**
     * Validate a specific config file for a feature
     *
     * @param string $featureName Feature name (e.g., 'Testy', 'Post')
     * @param string $configType Config type ('model_bindings', 'all_fields')
     * @return ValidationResult|null Validation result, or null if config file doesn't exist
     */
    public function validateFeatureConfig(string $featureName, string $configType): ?ValidationResult
    {
        $this->logger->debug('Validating feature config', [
            'feature' => $featureName,
            'config_type' => $configType,
        ]);

        // ✅ Build config file path
        $configFilePath = $this->getConfigFilePath($featureName, $configType);

        if (!file_exists($configFilePath)) {
            $this->logger->debug('Config file not found (skipping)', [
                'feature' => $featureName,
                'config_type' => $configType,
                'path' => $configFilePath,
            ]);
            return null; // Config file doesn't exist (not an error, just skip)
        }

        // ✅ Load config array
        $config = $this->loadConfigArray($configFilePath);

        // ✅ Select appropriate schema validator
        $errors = $this->validateWithSchema($config, $featureName, $configType, $configFilePath);

        return new ValidationResult(
            configFilePath: $configFilePath,
            featureName: $featureName,
            configType: $configType,
            errors: $errors
        );
    }

    /**
     * Get absolute path to config file
     *
     * @param string $featureName Feature name
     * @param string $configType Config type
     * @return string Absolute file path
     */
    private function getConfigFilePath(string $featureName, string $configType): string
    {
        $entityName = strtolower($featureName);

        return match ($configType) {
            'model_bindings' => self::FEATURES_BASE_PATH . "/{$featureName}/Config/model_bindings.php",
            'all_fields' => self::FEATURES_BASE_PATH . "/{$featureName}/Config/{$entityName}_fields_root.php",
            default => throw new \InvalidArgumentException("Unknown config type: {$configType}"),
        };
    }

    /**
     * Load config file and return array
     *
     * @param string $configFilePath Absolute path to config file
     * @return array<string, mixed> Config array
     */
    private function loadConfigArray(string $configFilePath): array
    {
        // ✅ Use require to load config file (same as ConfigService does)
        $config = require $configFilePath;

        if (!is_array($config)) {
            $this->logger->warning('Config file did not return an array', [
                'file' => $configFilePath,
                'type' => gettype($config),
            ]);
            return [];
        }

        return $config;
    }

    /**
     * Validate config array using appropriate schema
     *
     * @param array<string, mixed> $config Config array
     * @param string $featureName Feature name
     * @param string $configType Config type
     * @param string $configFilePath Config file path (for error messages)
     * @return array<string> Array of validation error messages
     */
    private function validateWithSchema(
        array $config,
        string $featureName,
        string $configType,
        string $configFilePath
    ): array {
        return match ($configType) {
            'model_bindings' => $this->modelBindingsSchema->validate($config, $featureName, $configFilePath),
            'all_fields' => $this->fieldsSchema->validate($config, $featureName, $configFilePath),
            'view_schema' => $this->viewSchema->validate($config, $featureName, $configFilePath),
            default => ["Unknown config type: {$configType}"],
        };
    }
}

/**
 * Validation Result Value Object
 *
 * Immutable data structure representing the result of a config file validation.
 *
 * Responsibilities:
 * - Store validation result (errors, file path, feature name)
 * - Provide helper methods (isValid(), getErrors())
 *
 * Does NOT:
 * - Perform validation (ConfigValidatorService's job)
 * - Throw exceptions (read-only data container)
 */
class ValidationResult
{
    /**
     * @param string $configFilePath Absolute path to validated config file
     * @param string $featureName Feature name (e.g., 'Testy')
     * @param string $configType Config type (e.g., 'model_bindings')
     * @param array<string> $errors Array of validation error messages (empty if valid)
     */
    public function __construct(
        private string $configFilePath,
        private string $featureName,
        private string $configType,
        private array $errors
    ) {
    }

    /**
     * Check if config is valid (no errors)
     *
     * @return bool True if valid, false if errors exist
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * Get validation errors
     *
     * @return array<string> Array of error messages
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get config file path
     *
     * @return string Absolute path to config file
     */
    public function getConfigFilePath(): string
    {
        return $this->configFilePath;
    }

    /**
     * Get feature name
     *
     * @return string Feature name (e.g., 'Testy')
     */
    public function getFeatureName(): string
    {
        return $this->featureName;
    }

    /**
     * Get config type
     *
     * @return string Config type (e.g., 'model_bindings')
     */
    public function getConfigType(): string
    {
        return $this->configType;
    }

    /**
     * Get error count
     *
     * @return int Number of validation errors
     */
    public function getErrorCount(): int
    {
        return count($this->errors);
    }
}