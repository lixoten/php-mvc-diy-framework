<?php
// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\Core\Config\Schema\ConfigSchemaValidatorInterface.php

declare(strict_types=1);

namespace Core\Config\Schema;

/**
 * Interface for Config Schema Validators
 *
 * All config validators (FieldsSchema, ModelBindingsSchema, ViewSchema, etc.)
 * must implement this interface to be registered with ConfigService.
 *
 * Responsibilities:
 * - Validate a config array against a schema
 * - Return array of error messages (empty if valid)
 * - Define which file patterns this validator handles
 *
 * Does NOT:
 * - Load config files (ConfigService's job)
 * - Cache validation results (ConfigService's job)
 * - Throw exceptions (returns errors instead)
 *
 * @package Core\Config\Schema
 */
interface ConfigSchemaValidatorInterface
{
    /**
     * Validate a config array
     *
     * @param array<string, mixed> $config Config array to validate
     * @param string $featureName Feature name (e.g., 'Testy')
     * @param string $configFilePath Absolute path to config file (for error messages)
     * @return array<string> Array of validation error messages (empty if valid)
     */
    public function validate(array $config, string $featureName, string $configFilePath): array;

    /**
     * Check if this validator can handle a specific config file
     *
     * @param string $filePath Absolute path to config file
     * @return bool True if this validator should validate this file
     */
    public function canValidate(string $filePath): bool;

    /**
     * Get validator name (for logging/debugging)
     *
     * @return string Validator name (e.g., 'FieldsSchema', 'ModelBindingsSchema')
     */
    public function getName(): string;
}