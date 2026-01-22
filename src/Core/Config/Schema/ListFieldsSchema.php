<?php
// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\Core\Config\Schema\ListFieldsSchema.php

declare(strict_types=1);

namespace Core\Config\Schema;

use Psr\Log\LoggerInterface;

/**
 * Schema Validator for List Field Config Files
 *
 * Validates the structure and business rules of list field configurations.
 *
 * Expected Structure (*_list_fields.php or *_fields.php):
 * ```php
 * return [
 *     'field_name' => [
 *         'label' => 'Field Label', // Optional (string)
 *         'list' => [
 *             'sortable' => true, // Optional (bool, default: false)
 *             'formatters' => [ // Optional (array of formatter configs)
 *                 [
 *                     'name' => 'badge', // Required (string)
 *                     'options_provider' => [TestyStatus::class, 'getFormatterOptions'], // Optional (array [class, method])
 *                     'options' => fn($value) => ['label' => $value], // Optional (arrow function or array)
 *                 ],
 *             ],
 *         ],
 *     ],
 * ];
 * ```
 *
 * Validation Rules:
 * 1. ✅ Config must be an array (not empty)
 * 2. ✅ Each field config must be an array
 * 3. ✅ 'label' must be a string (if provided)
 * 4. ✅ 'list' must be an array (if provided)
 * 5. ✅ 'list.sortable' must be a bool (if provided)
 * 6. ✅ 'list.formatters' must be an array (if provided)
 * 7. ✅ Each formatter must have a 'name' key (string)
 * 8. ✅ 'list.formatter' (closure) is FORBIDDEN per coding instructions
 * 9. ✅ 'options_provider' must be [class, method] array (if provided)
 * 10. ✅ 'options' must be a callable or array (if provided)
 *
 * Responsibilities (SRP):
 * - Validate list field config structure
 * - Enforce closure-free configuration (per coding instructions)
 * - Check formatter configuration validity
 * - Detect unknown keys/typos
 *
 * This class does NOT:
 * - Validate database schema (SchemaLoaderService's job)
 * - Check if formatter classes exist (runtime validation)
 * - Load config files (ConfigService's job)
 * - Render fields (FieldRegistryService/FormatterService's job)
 * - Validate form fields (FormFieldsSchema's job)
 *
 * @package Core\Config\Schema
 */
class ListFieldsSchema
{
    /**
     * Constructor
     *
     * @param LoggerInterface $logger For logging validation warnings/errors
     * @param UnknownKeyDetectorService $unknownKeyDetector For detecting unknown keys/typos
     */
    public function __construct(
        private LoggerInterface $logger,
        private UnknownKeyDetectorService $unknownKeyDetector
    ) {
    }

    /**
     * Validate a list fields config array
     *
     * @param array<string, mixed> $config Config array loaded from *_fields.php
     * @param string $featureName Feature name (e.g., 'Testy', 'Post')
     * @param string $configFilePath Absolute path to config file (for error messages)
     * @return array<string> Array of validation error messages (empty if valid)
     */
    public function validate(array $config, string $featureName, string $configFilePath): array
    {
        $errors = [];

        // ✅ Rule 1: Config must not be empty
        if (empty($config)) {
            $errors[] = "Config file is empty in {$configFilePath}";
            return $errors;
        }

        // ✅ Validate each field definition
        foreach ($config as $fieldName => $fieldConfig) {
            $fieldErrors = $this->validateField(
                $fieldName,
                $fieldConfig,
                $configFilePath
            );
            $errors = array_merge($errors, $fieldErrors);
        }

        return $errors;
    }

    /**
     * Validate a single field configuration
     *
     * @param string $fieldName Field name (e.g., 'title', 'status')
     * @param mixed $fieldConfig Field configuration (should be array)
     * @param string $configFilePath Config file path for error messages
     * @return array<string> Array of validation error messages
     */
    private function validateField(
        string $fieldName,
        mixed $fieldConfig,
        string $configFilePath
    ): array {
        $errors = [];

        // ✅ Rule 2: Field config must be an array
        if (!is_array($fieldConfig)) {
            $errors[] = "Field '{$fieldName}' config must be an array in {$configFilePath}";
            return $errors;
        }

        // ✅ Rule 3: 'label' must be a string (if provided)
        if (isset($fieldConfig['label']) && !is_string($fieldConfig['label'])) {
            $errors[] = "Field '{$fieldName}': 'label' must be a string in {$configFilePath}";
        }

        // ✅ Rule 4-10: Validate 'list' context
        if (isset($fieldConfig['list'])) {
            $listErrors = $this->validateListContext(
                $fieldName,
                $fieldConfig['list'],
                $configFilePath
            );
            $errors = array_merge($errors, $listErrors);
        }

        return $errors;
    }

    /**
     * Validate 'list' context configuration
     *
     * @param string $fieldName Field name for error messages
     * @param mixed $listConfig List context configuration (should be array)
     * @param string $configFilePath Config file path for error messages
     * @return array<string> Array of validation error messages
     */
    private function validateListContext(
        string $fieldName,
        mixed $listConfig,
        string $configFilePath
    ): array {
        $errors = [];
        $context = "Field '{$fieldName}.list'";

        // ✅ Rule 4: 'list' must be an array
        if (!is_array($listConfig)) {
            $errors[] = "{$context} must be an array in {$configFilePath}";
            return $errors;
        }

        // ✅ Define allowed list keys (whitelist)
        $allowedListKeys = [
            'sortable',
            'formatters',
        ];

        // ✅ Delegate to UnknownKeyDetectorService (CATCH TYPOS!)
        $unknownKeyErrors = $this->unknownKeyDetector->detectUnknownKeys(
            $listConfig,
            $allowedListKeys,
            $context,
            $configFilePath
        );
        $errors = array_merge($errors, $unknownKeyErrors);

        // ✅ Rule 5: 'sortable' must be a bool (if provided)
        if (isset($listConfig['sortable']) && !is_bool($listConfig['sortable'])) {
            $errors[] = "{$context}.sortable must be a boolean in {$configFilePath}";
        }

        // ✅ Rule 8: FORBIDDEN - Closure formatter (per coding instructions)
        if (isset($listConfig['formatter']) && $listConfig['formatter'] instanceof \Closure) {
            $errors[] = "{$context}.formatter: Closures are FORBIDDEN in config files per coding instructions. " .
                        "Use 'formatters' array with 'options_provider' instead. " .
                        "File: {$configFilePath}";
        }

        // ✅ Rule 6-10: Validate 'formatters' array
        if (isset($listConfig['formatters'])) {
            $formatterErrors = $this->validateFormatters(
                $fieldName,
                $listConfig['formatters'],
                $configFilePath
            );
            $errors = array_merge($errors, $formatterErrors);
        }

        return $errors;
    }

    /**
     * Validate 'formatters' array configuration
     *
     * @param string $fieldName Field name for error messages
     * @param mixed $formatters Formatters configuration (should be array)
     * @param string $configFilePath Config file path for error messages
     * @return array<string> Array of validation error messages
     */
    private function validateFormatters(
        string $fieldName,
        mixed $formatters,
        string $configFilePath
    ): array {
        $errors = [];
        $context = "Field '{$fieldName}.list.formatters'";

        // ✅ Rule 6: 'formatters' must be an array
        if (!is_array($formatters)) {
            $errors[] = "{$context} must be an array in {$configFilePath}";
            return $errors;
        }

        // ✅ Validate each formatter config
        foreach ($formatters as $index => $formatterConfig) {
            $formatterErrors = $this->validateFormatter(
                $fieldName,
                $index,
                $formatterConfig,
                $configFilePath
            );
            $errors = array_merge($errors, $formatterErrors);
        }

        return $errors;
    }

    /**
     * Validate a single formatter configuration
     *
     * @param string $fieldName Field name for error messages
     * @param string|int $index Formatter index/key in array
     * @param mixed $formatterConfig Formatter configuration (should be array)
     * @param string $configFilePath Config file path for error messages
     * @return array<string> Array of validation error messages
     */
    private function validateFormatter(
        string $fieldName,
        string|int $index,
        mixed $formatterConfig,
        string $configFilePath
    ): array {
        $errors = [];
        $formatterName = is_string($index) ? $index : $index;
        $context = "Field '{$fieldName}.list.formatters[{$formatterName}]'";

        // ✅ Formatter config must be an array
        if (!is_array($formatterConfig)) {
            $errors[] = "{$context} must be an array in {$configFilePath}";
            return $errors;
        }

        // ✅ Rule 7: 'name' is required and must be a string (only for numeric indices)
        if (is_int($index)) {
            if (!isset($formatterConfig['name'])) {
                $errors[] = "{$context}: Missing required key 'name' in {$configFilePath}";
            } elseif (!is_string($formatterConfig['name'])) {
                $errors[] = "{$context}.name must be a string in {$configFilePath}";
            }
        }

        // ✅ Rule 9: 'options_provider' must be [class, method] array (if provided)
        if (isset($formatterConfig['options_provider'])) {
            if (!is_array($formatterConfig['options_provider'])) {
                $errors[] = "{$context}.options_provider must be an array [ClassName::class, 'methodName'] in {$configFilePath}";
            } elseif (count($formatterConfig['options_provider']) !== 2) {
                $errors[] = "{$context}.options_provider must be exactly [ClassName::class, 'methodName'] (2 elements) in {$configFilePath}";
            } elseif (!is_string($formatterConfig['options_provider'][0]) || !is_string($formatterConfig['options_provider'][1])) {
                $errors[] = "{$context}.options_provider must be [string, string] in {$configFilePath}";
            }
        }

        // ✅ Rule 10: 'options' must be callable or array (if provided)
        if (isset($formatterConfig['options'])) {
            $options = $formatterConfig['options'];

            if ($options instanceof \Closure) {
                $reflection = new \ReflectionFunction($options);
                if ($reflection->getEndLine() - $reflection->getStartLine() > 0) {
                    $this->logger->warning(
                        "Multi-line closure found in config file. Use arrow function or static method instead.",
                        [
                            'field' => $fieldName,
                            'file' => $configFilePath,
                            'context' => $context,
                        ]
                    );
                }
            } elseif (!is_array($options) && !is_callable($options)) {
                $errors[] = "{$context}.options must be an arrow function, callable, or array in {$configFilePath}";
            }
        }

        return $errors;
    }
}
