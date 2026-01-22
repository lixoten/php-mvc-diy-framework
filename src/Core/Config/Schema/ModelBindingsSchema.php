<?php

declare(strict_types=1);

namespace Core\Config\Schema;

use Core\Exceptions\ConfigurationException;
use Core\Services\FieldRegistryService;
use Psr\Log\LoggerInterface;

// TODO unittest
/**
 * Schema Validator for model_bindings.php Config Files
 *
 * Validates the structure and business rules of Model Binding configurations.
 *
 * Expected Structure (model_bindings.php):
 * ```php
 * return [
 *     'actions' => [
 *         'editAction' => [
 *             'testy' => [
 *                 'repository' => TestyRepositoryInterface::class,
 *                 'method' => 'findById', // Optional (default: 'findById')
 *                 'parameter_name' => 'id', // Optional (default: 'id')
 *                 'fields' => ['id', 'user_id', 'store_id'], // Optional
 *                 'authorization' => [
 *                     'check' => true, // Optional (default: false)
 *                     'owner_field' => 'user_id', // Optional (default: 'user_id')
 *                     'store_field' => 'store_id', // Optional (default: 'store_id')
 *                     'allowed_roles' => ['admin', 'store_owner'], // Optional
 *                 ],
 *             ],
 *         ],
 *     ],
 * ];
 * ```
 *
 * Validation Rules:
 * 1. ✅ 'actions' key must exist and be an array
 * 2. ✅ Each action must be an array (e.g., 'editAction', 'viewAction')
 * 3. ✅ Each parameter name must match the feature name (case-insensitive)
 * 4. ✅ 'repository' must be a valid class that exists
 * 5. ✅ 'method' must be a string (if provided)
 * 6. ✅ 'fields' must be an array of strings (if provided)
 * 7. ✅ 'authorization' must be an array (if provided)
 * 8. ✅ 'authorization.check' must be a bool (if provided)
 * 9. ✅ 'authorization.owner_field' must be a string (if provided)
 * 10. ✅ 'authorization.store_field' must be a string (if provided)
 * 11. ✅ 'authorization.allowed_roles' must be an array of strings (if provided)
 *
 * Responsibilities (SRP):
 * - Validate model_bindings.php structure
 * - Check repository class existence
 * - Enforce parameter naming conventions
 * - Validate authorization configuration
 *
 * This class does NOT:
 * - Validate database schema (that's SchemaLoaderService's job)
 * - Check if repository methods exist (that's runtime reflection)
 * - Load config files (that's ConfigService's job)
 *
 * @package Core\Config\Schema
 */
class ModelBindingsSchema implements ConfigSchemaValidatorInterface
{
    /**
     * Constructor
     *
     * @param LoggerInterface $logger For logging validation warnings/errors
     */
    public function __construct(
        private LoggerInterface $logger,
        private UnknownKeyDetectorService $unknownKeyDetector,
        private ?FieldRegistryService $fieldRegistryService = null
    ) {
    }



    /**
     * ✅ Check if this validator can handle a specific config file
     *
     * Handles files matching:
     * - model_bindings.php
     */
    public function canValidate(string $filePath): bool
    {
        return str_contains($filePath, 'model_bindings.php');
    }

    /**
     * ✅ Get validator name (for logging)
     */
    public function getName(): string
    {
        return 'ModelBindingsSchema';
    }


    /**
     * Validate a model_bindings.php config array
     *
     * @param array<string, mixed> $config Config array loaded from model_bindings.php
     * @param string $featureName Feature name (e.g., 'Testy', 'Post')
     * @param string $configFilePath Absolute path to config file (for error messages)
     * @return array<string> Array of validation error messages (empty if valid)
     */
    public function validate(array $config, string $featureName, string $configFilePath): array
    {
        $errors = [];

        // ✅ Rule 1: 'actions' key must exist
        if (!isset($config['actions'])) {
            $errors[] = "Missing required key 'actions' in {$configFilePath}";
            return $errors; // Cannot continue without 'actions'
        }

        // ✅ Rule 2: 'actions' must be an array
        if (!is_array($config['actions'])) {
            $errors[] = "'actions' must be an array in {$configFilePath}";
            return $errors; // Cannot continue with invalid 'actions'
        }

        // ✅ Validate each action
        foreach ($config['actions'] as $actionName => $actionConfig) {
            $actionErrors = $this->validateAction(
                $actionName,
                $actionConfig,
                $featureName,
                $configFilePath
            );
            $errors = array_merge($errors, $actionErrors);
        }

        return $errors;
    }

    /**
     * Validate a single action configuration (e.g., 'editAction')
     *
     * @param string $actionName Action name (e.g., 'editAction', 'viewAction')
     * @param mixed $actionConfig Action configuration (should be array)
     * @param string $featureName Feature name for parameter name validation
     * @param string $configFilePath Config file path for error messages
     * @return array<string> Array of validation error messages
     */
    private function validateAction(
        string $actionName,
        mixed $actionConfig,
        string $featureName,
        string $configFilePath
    ): array {
        $errors = [];

        // ✅ Action config must be an array
        if (!is_array($actionConfig)) {
            $errors[] = "Action '{$actionName}' must be an array in {$configFilePath}";
            return $errors; // Cannot continue with invalid action config
        }

        // ✅ Validate each parameter binding (e.g., 'testy', 'post')
        foreach ($actionConfig as $parameterName => $bindingConfig) {
            $bindingErrors = $this->validateBinding(
                $actionName,
                $parameterName,
                $bindingConfig,
                $featureName,
                $configFilePath
            );
            $errors = array_merge($errors, $bindingErrors);
        }

        return $errors;
    }

    /**
     * Validate a single model binding configuration
     *
     * @param string $actionName Action name (e.g., 'editAction')
     * @param string $parameterName Parameter name (e.g., 'testy')
     * @param mixed $bindingConfig Binding configuration (should be array)
     * @param string $featureName Feature name for parameter name validation
     * @param string $configFilePath Config file path for error messages
     * @return array<string> Array of validation error messages
     */
    private function validateBinding(
        string $actionName,
        string $parameterName,
        mixed $bindingConfig,
        string $featureName,
        string $configFilePath
    ): array {
        $errors = [];

        // ✅ Binding config must be an array
        if (!is_array($bindingConfig)) {
            $errors[] = "Binding config for '{$actionName}.{$parameterName}' must be an array in {$configFilePath}";
            return $errors; // Cannot continue with invalid binding config
        }


        // ✅ NEW: Define allowed keys (whitelist)
        $allowedKeys = [
            'repository',
            'method',
            'parameter_name',
            'fields',
            'authorization',
        ];
        $unknownKeyErrors = $this->unknownKeyDetector->detectUnknownKeys(
            $bindingConfig,
            $allowedKeys,
            "Binding '{$actionName}.{$parameterName}'",
            $configFilePath
        );
        $errors = array_merge($errors, $unknownKeyErrors);

        // ✅ Rule 3: Parameter name should match feature name (case-insensitive)
        $expectedParamName = strtolower($featureName);
        if (strtolower($parameterName) !== $expectedParamName) {
            $errors[] = sprintf(
                "Parameter name mismatch in '%s': Config key '%s' does not match expected key '%s' in %s. " .
                "Fix: Change '%s' to '%s'",
                $actionName,
                $parameterName,
                $expectedParamName,
                $configFilePath,
                $parameterName,
                $expectedParamName
            );
        }

        // ✅ Rule 4: 'repository' key must exist and be a string
        if (!isset($bindingConfig['repository'])) {
            $errors[] = "Missing required key 'repository' in '{$actionName}.{$parameterName}' in {$configFilePath}";
        } elseif (!is_string($bindingConfig['repository'])) {
            $errors[] = "'repository' must be a string (class name) in '{$actionName}.{$parameterName}' in {$configFilePath}";
        } else {
            // ✅ Rule 4b: Repository class must exist
            $repositoryClass = $bindingConfig['repository'];
            if (!class_exists($repositoryClass) && !interface_exists($repositoryClass)) {
                $errors[] = sprintf(
                    "Repository class/interface '%s' not found in '{$actionName}.{$parameterName}' in %s",
                    $repositoryClass,
                    $configFilePath
                );
            }
        }

        // ✅ Rule 5: 'method' must be a string (if provided)
        if (isset($bindingConfig['method']) && !is_string($bindingConfig['method'])) {
            $errors[] = "'method' must be a string in '{$actionName}.{$parameterName}' in {$configFilePath}";
        }

        // ✅ Rule 6: 'parameter_name' must be a string (if provided)
        if (isset($bindingConfig['parameter_name']) && !is_string($bindingConfig['parameter_name'])) {
            $errors[] = "'parameter_name' must be a string in '{$actionName}.{$parameterName}' in {$configFilePath}";
        }

        // ✅ Rule 7: 'fields' must be an array of strings (if provided)
        if (isset($bindingConfig['fields'])) {
            if (!is_array($bindingConfig['fields'])) {
                $errors[] = "'fields' must be an array in '{$actionName}.{$parameterName}' in {$configFilePath}";
            } else {
                foreach ($bindingConfig['fields'] as $index => $field) {
                    if (!is_string($field)) {
                        $errors[] = "'fields[{$index}]' must be a string in '{$actionName}.{$parameterName}' in {$configFilePath}";
                    }
                    // ✅ NEW: Field name validation using existing FieldRegistryService
                    // if ($this->fieldRegistryService !== null) {
                    //     $fieldErrors = $this->validateFieldNames(
                    //         $bindingConfig['fields'],
                    //         $featureName,
                    //         $actionName,
                    //         $parameterName,
                    //         $configFilePath
                    //     );
                    //     $errors = array_merge($errors, $fieldErrors);
                    // }

                    // ✅ Field name validation (one field at a time)
                    if ($this->fieldRegistryService !== null) {
                        $fieldError = $this->validateSingleFieldName(
                            $field,
                            $index,
                            $featureName,
                            $actionName,
                            $parameterName,
                            $configFilePath
                        );
                        if ($fieldError !== null) {
                            $errors[] = $fieldError;
                        }
                    }
                }
            }
        }

        // ✅ Rule 8-11: Validate 'authorization' config (if provided)
        if (isset($bindingConfig['authorization'])) {
            $authErrors = $this->validateAuthorization(
                $actionName,
                $parameterName,
                $bindingConfig['authorization'],
                $configFilePath
            );
            $errors = array_merge($errors, $authErrors);
        }

        return $errors;
    }



    /**
     * Validate a single field name (one at a time)
     *
     * @param string $fieldName Field name to validate (e.g., 'title')
     * @param int $index Field index in array (for error messages)
     * @param string $featureName Feature name (e.g., 'Testy')
     * @param string $actionName Action name for error messages
     * @param string $parameterName Parameter name for error messages
     * @param string $configFilePath Config file path for error messages
     * @return string|null Error message if field is invalid, null if valid
     */
    private function validateSingleFieldName(
        string $fieldName,
        int $index,
        string $featureName,
        string $actionName,
        string $parameterName,
        string $configFilePath
    ): ?string {
        $entityName = strtolower($featureName);

        // ✅ Check if field exists in FieldRegistry
        $fieldDefinition = $this->fieldRegistryService->getFieldWithFallbacks(
            $fieldName,
            $entityName . '_root', // pageKey (e.g., 'testy_root')
            $entityName // entityName (e.g., 'testy')
        );

        if ($fieldDefinition === null) {
            // ❌ Field not found in registry
            return sprintf(
                "Unknown field '%s' (fields[%d]) in '%s.%s.fields' in %s. " .
                "Field definition not found in %s_fields_root.php or base_fields.php.",
                $fieldName,
                $index,
                $actionName,
                $parameterName,
                $configFilePath,
                $entityName
            );
        }

        return null; // ✅ Field is valid
    }


    // /**
    //  * Validate that field names exist in FieldRegistry
    //  *
    //  * @param array<string> $fieldNames Field names from config
    //  * @param string $featureName Feature name (e.g., 'Testy')
    //  * @param string $actionName Action name for error messages
    //  * @param string $parameterName Parameter name for error messages
    //  * @param string $configFilePath Config file path for error messages
    //  * @return array<string> Array of validation error messages
    //  */
    // private function validateFieldNames(
    //     array $fieldNames,
    //     string $featureName,
    //     string $actionName,
    //     string $parameterName,
    //     string $configFilePath
    // ): array {
    //     $errors = [];
    //     $entityName = strtolower($featureName);

    //     // ✅ We don't have a pageKey in model_bindings.php, so we'll use 'root'
    //     // This will check:
    //     // 1. testy_fields_root.php (entity-level)
    //     // 2. base_fields.php (global fallback)

    //     foreach ($fieldNames as $fieldName) {
    //         if (!is_string($fieldName)) {
    //             continue; // Already validated by type check
    //         }

    //         // ✅ Use existing FieldRegistryService method
    //         $fieldDefinition = $this->fieldRegistryService->getFieldWithFallbacks(
    //             $fieldName,
    //             $entityName . '_root', // pageKey (e.g., 'testy_root')
    //             $entityName // entityName (e.g., 'testy')
    //         );

    //         if ($fieldDefinition === null) {
    //             // ❌ Field not found in registry
    //             $errors[] = sprintf(
    //                 "Unknown field '%s' in '%s.%s.fields' in %s. " .
    //                 "Field definition not found in %s_fields_root.php or base_fields.php.",
    //                 $fieldName,
    //                 $actionName,
    //                 $parameterName,
    //                 $configFilePath,
    //                 $entityName
    //             );
    //         }
    //     }

    //     return $errors;
    // }



    /**
     * Validate authorization configuration (STRICT MODE)
     *
     * @param string $actionName Action name for error messages
     * @param string $parameterName Parameter name for error messages
     * @param mixed $authConfig Authorization configuration (should be array)
     * @param string $configFilePath Config file path for error messages
     * @return array<string> Array of validation error messages
     */
    private function validateAuthorization(
        string $actionName,
        string $parameterName,
        mixed $authConfig,
        string $configFilePath
    ): array {
        $errors = [];
        $context = "'{$actionName}.{$parameterName}.authorization'";

        // ✅ Authorization must be an array
        if (!is_array($authConfig)) {
            $errors[] = "{$context} must be an array in {$configFilePath}";
            return $errors;
        }

        // ✅ NEW: Define allowed authorization keys (whitelist)
        $allowedAuthKeys = [
            'check',
            'owner_field',
            'store_field',
            'allowed_roles',
        ];


        // ✅ Delegate to UnknownKeyDetectorService (SINGLE SOURCE OF TRUTH)
        $unknownAuthKeyErrors = $this->unknownKeyDetector->detectUnknownKeys(
            $authConfig,
            $allowedAuthKeys,
            "{$context}",
            $configFilePath
        );
        $errors = array_merge($errors, $unknownAuthKeyErrors);


        // ✅ Rule 8: 'check' must be a bool (if provided)
        if (isset($authConfig['check']) && !is_bool($authConfig['check'])) {
            $errors[] = "{$context}.check must be a boolean in {$configFilePath}";
        }

        // ✅ Rule 9: 'owner_field' must be a string (if provided)
        if (isset($authConfig['owner_field']) && !is_string($authConfig['owner_field'])) {
            $errors[] = "{$context}.owner_field must be a string in {$configFilePath}";
        }

        // ✅ Rule 10: 'store_field' must be a string (if provided)
        if (isset($authConfig['store_field']) && !is_string($authConfig['store_field'])) {
            $errors[] = "{$context}.store_field must be a string in {$configFilePath}";
        }

        // ✅ Rule 11: 'allowed_roles' must be an array of strings (if provided)
        if (isset($authConfig['allowed_roles'])) {
            if (!is_array($authConfig['allowed_roles'])) {
                $errors[] = "{$context}.allowed_roles must be an array in {$configFilePath}";
            } else {
                foreach ($authConfig['allowed_roles'] as $index => $role) {
                    if (!is_string($role)) {
                        $errors[] = "{$context}.allowed_roles[{$index}] must be a string in {$configFilePath}";
                    }
                }
            }
        }

        return $errors;
    }
}