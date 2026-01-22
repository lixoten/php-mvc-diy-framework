<?php
// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\Core\Config\Schema\FieldsSchema.php

declare(strict_types=1);

namespace Core\Config\Schema;

use Core\Services\ConfigService;
use Core\Services\FieldRegistryService;
use Psr\Log\LoggerInterface;

// TODO unittest
/**
 * Unified Schema Validator for Field Config Files
 *
 * Validates BOTH 'list' and 'form' contexts in a single pass.
 *
 * Expected Structure (*_fields_root.php):
 * ```php
 * return [
 *     'field_name' => [
 *         'label' => 'Field Label', // Optional (string)
 *         'data_transformer' => 'json_array', // Optional (string)
 *         'list' => [
 *             'sortable' => true, // Optional (bool)
 *             'formatters' => [ // Optional (array)
 *                 [
 *                     'name' => 'badge',
 *                     'options_provider' => [TestyStatus::class, 'getFormatterOptions'],
 *                 ],
 *             ],
 *         ],
 *         'form' => [
 *             'type' => 'text', // Optional (string)
 *             'required' => true, // Optional (bool)
 *             'minlength' => 2, // Optional (int)
 *             'maxlength' => 100, // Optional (int)
 *             'validation' => [ // Optional (array)
 *                 'email' => true,
 *             ],
 *             'render' => [ // Optional (array)
 *                 'show_label' => true,
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
 * 4. ✅ 'data_transformer' must be a valid type (if provided)
 * 5. ✅ 'list' context validated (sortable, formatters)
 * 6. ✅ 'form' context validated (type, required, minlength, etc.)
 * 7. ✅ Unknown keys detected with suggestions
 * 8. ✅ Closures in config FORBIDDEN per coding instructions
 *
 * Responsibilities (SRP):
 * - Validate field-level config structure (label, data_transformer)
 * - Validate 'list' context (sortable, formatters)
 * - Validate 'form' context (type, required, validation, etc.)
 * - Detect unknown keys/typos across all contexts
 * - Enforce closure-free configuration
 *
 * This class does NOT:
 * - Execute validation rules (ValidationService's job)
 * - Render fields (FormRenderer/ListRenderer's job)
 * - Load config files (ConfigService's job)
 * - Check database constraints (SchemaLoaderService's job)
 *
 * @package Core\Config\Schema
 */
class FieldsSchema implements ConfigSchemaValidatorInterface
{
    /**
     * Valid HTML5 input types
     * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#input_types
     */
    private const VALID_INPUT_TYPES = [
        'text', 'email', 'password', 'number', 'tel', 'url', 'search',
        'date', 'datetime-local', 'time', 'month', 'week',
        'color', 'range', 'file', 'hidden',
        'checkbox', 'radio', 'textarea', 'select',
        'checkbox_group', 'radio_group',
    ];

    /**
     * Valid data transformer types
     */
    private const VALID_TRANSFORMERS = [
        'json_array', 'boolean', 'date', 'datetime', 'int', 'float',
    ];

    /**
     * ✅ Common HTML5 attributes allowed in 'form.attributes' config
     *
     * Standard HTML attributes that can be used on form inputs.
     * Custom 'data-*' attributes are allowed via wildcard matching in UnknownKeyDetectorService.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes
     */
    private const COMMON_HTML_ATTRIBUTES = [
        // ✅ Form validation attributes
        'required',
        'minlength',
        'maxlength',
        'min',
        'max',
        'pattern',
        'step',

        // ✅ Input behavior attributes
        'placeholder',
        'readonly',
        'disabled',
        'autocomplete',
        'autofocus',
        'multiple',

        // ✅ Styling and presentation
        'class',
        'id',
        'style',
        'title',

        // ✅ Accessibility (ARIA)
        'aria-label',
        'aria-describedby',
        'aria-required',
        'aria-invalid',

        // ✅ Data attributes (hint for UnknownKeyDetectorService to allow 'data-*')
        'data', // Wildcard: allows 'data-foo', 'data-bar', etc.

        // ✅ Event handlers (optional - remove if you want to enforce closure-free config)
        // 'onclick',
        // 'onchange',
        // 'oninput',



        // ✅ custom attribute mvclixo
        'data-char-counter',
        'data-live-validation',

        // ✅ ??????
        'accept',
    ];




    /**
     * Constructor
     *
     * @param LoggerInterface $logger For logging validation warnings/errors
     * @param UnknownKeyDetectorService $unknownKeyDetector For detecting typos
     */
    public function __construct(
        protected FieldRegistryService $fieldRegistryService,
        private LoggerInterface $logger,
        private UnknownKeyDetectorService $unknownKeyDetector,
        private ConfigService $configService
    ) {
    }


    /**
     * ✅ Check if this validator can handle a specific config file
     *
     * Handles files matching:
     * - *_fields_root.php
     * - *_fields.php
     * - field_*.php
     */
    public function canValidate(string $filePath): bool
    {
        return str_contains($filePath, '_fields') || str_contains($filePath, 'field_');
    }

    /**
     * ✅ Get validator name (for logging)
     */
    public function getName(): string
    {
        return 'FieldsSchema';
    }


    /**
     * Validate a unified fields config array
     *
     * @param array<string, mixed> $config Config array from *_fields_root.php
     * @param string $featureName Feature name (e.g., 'Testy')
     * @param string $configFilePath Absolute path to config file
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
     * Validate a single field configuration (field-level + contexts)
     *
     * @param string $fieldName Field name (e.g., 'title', 'email')
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


        ////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////

        // $formSchema = $this->configService->get('forms/schema');
        // if ($formSchema === null) {
            // $forFile    = $entityName . '_fields_' . 'root/edit/view.php';
            // $message    = "Form schema (forms/schema.php) not found. Cannot perform field definition validation.";
            // $suggestion = "Ensure 'src/Config/forms/schema.php' exists and is properly configured.";
            // $errorCode  = 'ERR-DEV-FD-001';

            // $this->reportValidationError(
            //     $message,
            //     $errorCode,
            //     $suggestion,
            //     [
            //         'field'  => $fieldName,
            //         'page'   => $pageKey,
            //         'entity' => $entityName,
            //     ]
            // );
        // }

        // $globalSchema = $formSchema['global'] ?? [];

        ////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////
        // ✅ Get the field definition from FieldRegistryService

            // $entityName = 'testy';

            // $fieldDefinition = $this->fieldRegistryService->getFieldWithFallbacks(
            //     $fieldName,
            //     $entityName . '_root', // pageKey (e.g., 'testy_root')
            //     $entityName // entityName (e.g., 'testy')
            // );


            //$fieldDefinition = $this->fieldRegistryService->getFieldWithFallbacks($fieldName, $pageKey, $entityName);
            // $rrr = 1;
            // if ($fieldDefinition === null) {
            //     // ❌ Field not found in FieldRegistry
            //     $message    = "Config '{$configIdentifier}': {$context}, field '{$fieldName}' at index {$fieldIndex} " .
            //                   "could not be found via FieldRegistryService.";
            //     $suggestion = "Suggestion: Fix or removed field '{$fieldName}' from '{$context}'.";
            //     $errorCode  = 'ERR-DEV-FN-032';
            //     $errors[]   = [
            //         'message'    => $message,
            //         'suggestion' => $suggestion,
            //         'dev_code' => $errorCode,
            //     ];
            //     continue; // Cannot validate schema if definition is missing
            // }

            // // ✅ Perform schema validation on the retrieved field definition
            // // This will throw FieldSchemaValidationException if invalid (fast fail)
            // try {
            //     $this->fieldDefinitionSchemaValidatorService->validateFieldDefinition(
            //         $fieldDefinition,
            //         $fieldName,
            //         $pageKey,
            //         $entityName,
            //         'form'
            //     );
            // } catch (\Core\Exceptions\FieldSchemaValidationException $e) {
            //     // ❌ Schema validation failed - custom exception with getSuggestion()
            //     $message    = $e->getMessage();
            //     $suggestion = $e->getSuggestion();
            //     $errorCode  = $e->getDevCode();
            //     $errors[]   = [
            //         'message'    => $message,
            //         'suggestion' => $suggestion,
            //         'dev_code'   => $errorCode,
            //     ];
            //     continue;
            // } catch (\Exception $e) {
            //     // ❌ Schema validation failed
            //     $message    = "Config '{$configIdentifier}': {$context}, field '{$fieldName}' at index {$fieldIndex} " .
            //                   "failed schema validation: {$e->getMessage()}";
            //     $suggestion = "Suggestion: Check '{$context}' field.";
            //     $errorCode  = 'ERR-DEV-FN-034';
            //     $errors[]   = [
            //         'message'    => $message,
            //         'suggestion' => $suggestion,
            //         'dev_code' => $errorCode,
            //     ];
            //     continue;
            // }
        ////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////
        // ✅ Rule 2: Field config must be an array
        if (!is_array($fieldConfig)) {
            $errors[] = "Field '{$fieldName}' config must be an array in {$configFilePath}";
            return $errors;
        }

        // ✅ Define allowed field-level keys (whitelist)
        $allowedFieldKeys = [
            'label',
            'data_transformer',
            'list',
            'form',
            'validators',
        ];

        // ✅ Detect unknown keys at field level
        $unknownKeyErrors = $this->unknownKeyDetector->detectUnknownKeys(
            $fieldConfig,
            $allowedFieldKeys,
            "Field '{$fieldName}'",
            $configFilePath
        );
        $errors = array_merge($errors, $unknownKeyErrors);

        // ✅ Rule 3: 'label' must be a string (if provided)
        if (isset($fieldConfig['label']) && !is_string($fieldConfig['label'])) {
            $errors[] = "Field '{$fieldName}': 'label' must be a string in {$configFilePath}";
        }

        // ✅ Rule 4: Validate 'data_transformer' (if provided)
        if (isset($fieldConfig['data_transformer'])) {
            $transformerErrors = $this->validateDataTransformer(
                $fieldName,
                $fieldConfig['data_transformer'],
                $configFilePath
            );
            $errors = array_merge($errors, $transformerErrors);
        }

        // ✅ Rule 5: Validate 'list' context (if provided)
        if (isset($fieldConfig['list'])) {
            $listErrors = $this->validateListContext(
                $fieldName,
                $fieldConfig['list'],
                $configFilePath
            );
            $errors = array_merge($errors, $listErrors);
        }

        // ✅ Rule 6: Validate 'form' context (if provided)
        if (isset($fieldConfig['form'])) {
            $formErrors = $this->validateFormContext(
                $fieldName,
                $fieldConfig['form'],
                $configFilePath
            );
            $errors = array_merge($errors, $formErrors);
        }

        return $errors;
    }

    /**
     * Validate data_transformer field
     *
     * @param string $fieldName Field name for error messages
     * @param mixed $transformer Transformer value (should be string)
     * @param string $configFilePath Config file path for error messages
     * @return array<string> Array of validation error messages
     */
    private function validateDataTransformer(
        string $fieldName,
        mixed $transformer,
        string $configFilePath
    ): array {
        $errors = [];

        if (!is_string($transformer)) {
            $errors[] = "Field '{$fieldName}': 'data_transformer' must be a string in {$configFilePath}";
            return $errors;
        }

        if (!in_array($transformer, self::VALID_TRANSFORMERS, true)) {
            $closestMatch = $this->unknownKeyDetector->findClosestMatch($transformer, self::VALID_TRANSFORMERS);
            $suggestion = $closestMatch ? " Did you mean '{$closestMatch}'?" : '';
            $errors[] = "Field '{$fieldName}': Unknown data_transformer '{$transformer}' in {$configFilePath}.{$suggestion}";
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

        if (!is_array($listConfig)) {
            $errors[] = "{$context} must be an array in {$configFilePath}";
            return $errors;
        }

        // ✅ Define allowed list keys
        $allowedListKeys = ['sortable', 'formatters'];

        // ✅ Detect unknown keys
        $unknownKeyErrors = $this->unknownKeyDetector->detectUnknownKeys(
            $listConfig,
            $allowedListKeys,
            $context,
            $configFilePath
        );
        $errors = array_merge($errors, $unknownKeyErrors);

        // ✅ Validate 'sortable' (if provided)
        if (isset($listConfig['sortable']) && !is_bool($listConfig['sortable'])) {
            $errors[] = "{$context}.sortable must be a boolean in {$configFilePath}";
        }

        // ✅ FORBIDDEN: Closure formatter (per coding instructions)
        if (isset($listConfig['formatter']) && $listConfig['formatter'] instanceof \Closure) {
            $errors[] = "{$context}.formatter: Closures are FORBIDDEN in config files per coding instructions. " .
                        "Use 'formatters' array with 'options_provider' instead. " .
                        "File: {$configFilePath}";
        }

        // ✅ Validate 'formatters' (if provided)
        if (isset($listConfig['formatters'])) {
            if (!is_array($listConfig['formatters'])) {
                $errors[] = "{$context}.formatters must be an array in {$configFilePath}";
            } else {
                foreach ($listConfig['formatters'] as $index => $formatterConfig) {
                    $formatterErrors = $this->validateFormatter(
                        $fieldName,
                        $index,
                        $formatterConfig,
                        $configFilePath
                    );
                    $errors = array_merge($errors, $formatterErrors);
                }
            }
        }

        return $errors;
    }

    /**
     * Validate a single formatter configuration
     *
     * @param string $fieldName Field name for error messages
     * @param string|int $index Formatter index/key
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
        $formatterName = is_string($index) ? $index : (string)$index;
        $context = "Field '{$fieldName}.list.formatters[{$formatterName}]'";

        if (!is_array($formatterConfig)) {
            $errors[] = "{$context} must be an array in {$configFilePath}";
            return $errors;
        }

        // ✅ 'name' required for numeric indices
        if (is_int($index)) {
            if (!isset($formatterConfig['name'])) {
                $errors[] = "{$context}: Missing required key 'name' in {$configFilePath}";
            } elseif (!is_string($formatterConfig['name'])) {
                $errors[] = "{$context}.name must be a string in {$configFilePath}";
            }
        }

        // ✅ 'options_provider' must be [class, method] array (if provided)
        if (isset($formatterConfig['options_provider'])) {
            if (!is_array($formatterConfig['options_provider'])) {
                $errors[] = "{$context}.options_provider must be an array [ClassName::class, 'methodName'] in {$configFilePath}";
            } elseif (count($formatterConfig['options_provider']) !== 2) {
                $errors[] = "{$context}.options_provider must be exactly [ClassName::class, 'methodName'] (2 elements) in {$configFilePath}";
            } elseif (!is_string($formatterConfig['options_provider'][0]) || !is_string($formatterConfig['options_provider'][1])) {
                $errors[] = "{$context}.options_provider must be [string, string] in {$configFilePath}";
            }
        }

        // ✅ 'options' must be callable or array (if provided)
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

    /**
     * Validate 'form' context configuration
     *
     * @param string $fieldName Field name for error messages
     * @param mixed $formConfig Form context configuration (should be array)
     * @param string $configFilePath Config file path for error messages
     * @return array<string> Array of validation error messages
     */
    private function validateFormContext(
        string $fieldName,
        mixed $formConfig,
        string $configFilePath
    ): array {
        $errors = [];
        $context = "Field '{$fieldName}.form'";

        if (!is_array($formConfig)) {
            $errors[] = "{$context} must be an array in {$configFilePath}";
            return $errors;
        }

        // ✅ Define allowed form keys
        $allowedFormKeys = [
            'type',
            // 'required',
            // 'minlength',
            // 'maxlength', 'min', 'max', 'pattern', 'placeholder',
            'attributes',
            // 'validation',
            'render',
            'upload',
            'formatters',
            'options_provider',
            'options_provider_params',
            'display_default_choice',
            //'inline', 'validators',
        ];

        // ✅ Detect unknown keys
        $unknownKeyErrors = $this->unknownKeyDetector->detectUnknownKeys(
            $formConfig,
            $allowedFormKeys,
            $context,
            $configFilePath
        );
        $errors = array_merge($errors, $unknownKeyErrors);

        // ✅ Validate 'type' (if provided)
        if (isset($formConfig['type'])) {
            if (!is_string($formConfig['type'])) {
                $errors[] = "{$context}.type must be a string in {$configFilePath}";
            } elseif (!in_array($formConfig['type'], self::VALID_INPUT_TYPES, true)) {
                $closestMatch = $this->unknownKeyDetector->findClosestMatch($formConfig['type'], self::VALID_INPUT_TYPES);
                $suggestion = $closestMatch ? " Did you mean '{$closestMatch}'?" : '';
                $errors[] = "{$context}.type: Unknown input type '{$formConfig['type']}' in {$configFilePath}.{$suggestion}";
            }
        }

        // // ✅ Validate 'required' (if provided)
        // if (isset($formConfig['required']) && !is_bool($formConfig['required'])) {
        //     $errors[] = "{$context}.required must be a boolean in {$configFilePath}";
        // }

        // // ✅ Validate 'minlength' (if provided)
        // if (isset($formConfig['minlength'])) {
        //     if (!is_int($formConfig['minlength'])) {
        //         $errors[] = "{$context}.minlength must be an integer in {$configFilePath}";
        //     } elseif ($formConfig['minlength'] < 0) {
        //         $errors[] = "{$context}.minlength must be a positive integer in {$configFilePath}";
        //     }
        // }

        // // ✅ Validate 'maxlength' (if provided)
        // if (isset($formConfig['maxlength'])) {
        //     if (!is_int($formConfig['maxlength'])) {
        //         $errors[] = "{$context}.maxlength must be an integer in {$configFilePath}";
        //     } elseif ($formConfig['maxlength'] < 0) {
        //         $errors[] = "{$context}.maxlength must be a positive integer in {$configFilePath}";
        //     }
        // }

        // // ✅ Constraint: minlength < maxlength
        // if (isset($formConfig['minlength']) && isset($formConfig['maxlength'])) {
        //     if (is_int($formConfig['minlength']) && is_int($formConfig['maxlength'])) {
        //         if ($formConfig['minlength'] >= $formConfig['maxlength']) {
        //             $errors[] = "{$context}: minlength ({$formConfig['minlength']}) must be less than maxlength ({$formConfig['maxlength']}) in {$configFilePath}";
        //         }
        //     }
        // }

        // // ✅ Validate 'min' (if provided)
        // if (isset($formConfig['min']) && !is_numeric($formConfig['min'])) {
        //     $errors[] = "{$context}.min must be a number (int or float) in {$configFilePath}";
        // }

        // // ✅ Validate 'max' (if provided)
        // if (isset($formConfig['max']) && !is_numeric($formConfig['max'])) {
        //     $errors[] = "{$context}.max must be a number (int or float) in {$configFilePath}";
        // }

        // // ✅ Constraint: min < max
        // if (isset($formConfig['min']) && isset($formConfig['max'])) {
        //     if (is_numeric($formConfig['min']) && is_numeric($formConfig['max'])) {
        //         if ($formConfig['min'] >= $formConfig['max']) {
        //             $errors[] = "{$context}: min ({$formConfig['min']}) must be less than max ({$formConfig['max']}) in {$configFilePath}";
        //         }
        //     }
        // }

        // // ✅ Validate other form keys
        // if (isset($formConfig['pattern']) && !is_string($formConfig['pattern'])) {
        //     $errors[] = "{$context}.pattern must be a string (regex pattern) in {$configFilePath}";
        // }

        // if (isset($formConfig['placeholder']) && !is_string($formConfig['placeholder'])) {
        //     $errors[] = "{$context}.placeholder must be a string in {$configFilePath}";
        // }

        // if (isset($formConfig['attributes']) && !is_array($formConfig['attributes'])) {
        //     $errors[] = "{$context}.attributes must be an array in {$configFilePath}";
        // }



        // ✅ Validate 'attributes' context (if provided)
        if (isset($formConfig['attributes'])) {
            $attributesErrors = $this->validateAttributesContext(
                $fieldName,
                $formConfig['attributes'],
                $configFilePath
            );
            $errors = array_merge($errors, $attributesErrors);
        }


        // ✅ Validate sub-contexts
        if (isset($formConfig['validation'])) {
            $validationErrors = $this->validateValidationRules($fieldName, $formConfig['validation'], $configFilePath);
            $errors = array_merge($errors, $validationErrors);
        }

        if (isset($formConfig['render'])) {
            $renderErrors = $this->validateRenderContext($fieldName, $formConfig['render'], $configFilePath);
            $errors = array_merge($errors, $renderErrors);
        }

        if (isset($formConfig['upload'])) {
            $uploadErrors = $this->validateUploadContext($fieldName, $formConfig['upload'], $configFilePath);
            $errors = array_merge($errors, $uploadErrors);
        }

        if (isset($formConfig['validators'])) {
            $validatorErrors = $this->validateValidatorsContext($fieldName, $formConfig['validators'], $configFilePath);
            $errors = array_merge($errors, $validatorErrors);
        }

        if (isset($formConfig['formatters'])) {
            $formatterErrors = $this->validateFormattersContext($fieldName, $formConfig['formatters'], $configFilePath);
            $errors = array_merge($errors, $formatterErrors);
        }

        return $errors;
    }


    /**
     * Validate 'attributes' context configuration (HTML attributes for input/element)
     *
     * @param string $fieldName Field name for error messages
     * @param mixed $attributesConfig Attributes configuration (should be array)
     * @param string $configFilePath Config file path for error messages
     * @return array<string> Array of validation error messages
     */
    private function validateAttributesContext(
        string $fieldName,
        mixed $attributesConfig,
        string $configFilePath
    ): array {
        $errors = [];
        $context = "Field '{$fieldName}.form.attributes'";

        if (!is_array($attributesConfig)) {
            $errors[] = "{$context} must be an array in {$configFilePath}";
            return $errors;
        }

        // ✅ Define allowed attributes keys. Allow 'data-*' as a wildcard.
        // The 'data' entry in COMMON_HTML_ATTRIBUTES acts as a hint for the UnknownKeyDetectorService
        // if it supports ignoring 'data-*'. Otherwise, it will flag 'data-foo' as unknown.
        // For strictness, if UnknownKeyDetectorService doesn't support wildcards,
        // it's best to explicitly list or configure it to ignore 'data-'.
        $allowedAttributeKeys = self::COMMON_HTML_ATTRIBUTES;

        // ✅ Detect unknown keys
        $unknownKeyErrors = $this->unknownKeyDetector->detectUnknownKeys(
            $attributesConfig,
            $allowedAttributeKeys,
            $context,
            $configFilePath
        );
        $errors = array_merge($errors, $unknownKeyErrors);

        // ✅ Validate 'required' (if provided)
        if (isset($attributesConfig['required']) && !is_bool($attributesConfig['required'])) {
            $errors[] = "{$context}.required must be a boolean in {$configFilePath}";
        }

        // ✅ Validate 'minlength' (if provided)
        if (isset($attributesConfig['minlength'])) {
            if (!is_int($attributesConfig['minlength'])) {
                $errors[] = "{$context}.minlength must be an integer in {$configFilePath}";
            } elseif ($attributesConfig['minlength'] < 0) {
                $errors[] = "{$context}.minlength must be a positive integer in {$configFilePath}";
            }
        }

        // ✅ Validate 'maxlength' (if provided)
        if (isset($attributesConfig['maxlength'])) {
            if (!is_int($attributesConfig['maxlength'])) {
                $errors[] = "{$context}.maxlength must be an integer in {$configFilePath}";
            } elseif ($attributesConfig['maxlength'] < 0) {
                $errors[] = "{$context}.maxlength must be a positive integer in {$configFilePath}";
            }
        }

        // ✅ Constraint: minlength < maxlength
        if (isset($attributesConfig['minlength']) && isset($attributesConfig['maxlength'])) {
            if (is_int($attributesConfig['minlength']) && is_int($attributesConfig['maxlength'])) {
                if ($attributesConfig['minlength'] >= $attributesConfig['maxlength']) {
                    $errors[] = "{$context}: minlength ({$attributesConfig['minlength']}) must be less than maxlength ({$attributesConfig['maxlength']}) in {$configFilePath}";
                }
            }
        }

        // ✅ Validate 'min' (if provided)
        if (isset($attributesConfig['min']) && !is_numeric($attributesConfig['min'])) {
            $errors[] = "{$context}.min must be a number (int or float) in {$configFilePath}";
        }

        // ✅ Validate 'max' (if provided)
        if (isset($attributesConfig['max']) && !is_numeric($attributesConfig['max'])) {
            $errors[] = "{$context}.max must be a number (int or float) in {$configFilePath}";
        }

        // ✅ Constraint: min < max
        if (isset($attributesConfig['min']) && isset($attributesConfig['max'])) {
            if (is_numeric($attributesConfig['min']) && is_numeric($attributesConfig['max'])) {
                if ($attributesConfig['min'] >= $attributesConfig['max']) {
                    $errors[] = "{$context}: min ({$attributesConfig['min']}) must be less than max ({$attributesConfig['max']}) in {$configFilePath}";
                }
            }
        }

        // ✅ Validate 'pattern' (if provided)
        if (isset($attributesConfig['pattern']) && !is_string($attributesConfig['pattern'])) {
            $errors[] = "{$context}.pattern must be a string (regex pattern) in {$configFilePath}";
        }

        // ✅ Validate 'placeholder' (if provided)
        if (isset($attributesConfig['placeholder']) && !is_string($attributesConfig['placeholder'])) {
            $errors[] = "{$context}.placeholder must be a string in {$configFilePath}";
        }

        return $errors;
    }





    /**
     * Validate 'validation' rules configuration
     *
     * @param string $fieldName Field name for error messages
     * @param mixed $validationConfig Validation rules (should be array)
     * @param string $configFilePath Config file path for error messages
     * @return array<string> Array of validation error messages
     */
    private function validateValidationRules(
        string $fieldName,
        mixed $validationConfig,
        string $configFilePath
    ): array {
        $errors = [];
        $context = "Field '{$fieldName}.form.validation'";

        if (!is_array($validationConfig)) {
            $errors[] = "{$context} must be an array in {$configFilePath}";
            return $errors;
        }

        foreach ($validationConfig as $ruleName => $ruleValue) {
            if (!is_string($ruleName)) {
                $errors[] = "{$context}: Rule name must be a string in {$configFilePath}";
                continue;
            }

            if (!is_bool($ruleValue) && !is_string($ruleValue) && !is_int($ruleValue) && !is_array($ruleValue)) {
                $errors[] = "{$context}.{$ruleName}: Rule value must be bool, string, int, or array in {$configFilePath}";
            }

            // ✅ Special validation for 'unique' rule
            if ($ruleName === 'unique' && is_array($ruleValue)) {
                if (!isset($ruleValue['table'])) {
                    $errors[] = "{$context}.unique: Missing required key 'table' in {$configFilePath}";
                }
                if (!isset($ruleValue['column'])) {
                    $errors[] = "{$context}.unique: Missing required key 'column' in {$configFilePath}";
                }
            }
        }

        return $errors;
    }

    /**
     * Validate 'render' context configuration
     *
     * @param string $fieldName Field name for error messages
     * @param mixed $renderConfig Render configuration (should be array)
     * @param string $configFilePath Config file path for error messages
     * @return array<string> Array of validation error messages
     */
    private function validateRenderContext(
        string $fieldName,
        mixed $renderConfig,
        string $configFilePath
    ): array {
        $errors = [];
        $context = "Field '{$fieldName}.form.render'";

        if (!is_array($renderConfig)) {
            $errors[] = "{$context} must be an array in {$configFilePath}";
            return $errors;
        }

        $allowedRenderKeys = ['show_label', 'display_default_choice'];

        $unknownKeyErrors = $this->unknownKeyDetector->detectUnknownKeys(
            $renderConfig,
            $allowedRenderKeys,
            $context,
            $configFilePath
        );
        $errors = array_merge($errors, $unknownKeyErrors);

        if (isset($renderConfig['show_label']) && !is_bool($renderConfig['show_label'])) {
            $errors[] = "{$context}.show_label must be a boolean in {$configFilePath}";
        }

        if (isset($renderConfig['display_default_choice']) && !is_bool($renderConfig['display_default_choice'])) {
            $errors[] = "{$context}.display_default_choice must be a boolean in {$configFilePath}";
        }

        return $errors;
    }

    /**
     * Validate 'upload' context configuration
     *
     * @param string $fieldName Field name for error messages
     * @param mixed $uploadConfig Upload configuration (should be array)
     * @param string $configFilePath Config file path for error messages
     * @return array<string> Array of validation error messages
     */
    private function validateUploadContext(
        string $fieldName,
        mixed $uploadConfig,
        string $configFilePath
    ): array {
        $errors = [];
        $context = "Field '{$fieldName}.form.upload'";

        if (!is_array($uploadConfig)) {
            $errors[] = "{$context} must be an array in {$configFilePath}";
            return $errors;
        }

        if (isset($uploadConfig['max_size']) && !is_int($uploadConfig['max_size'])) {
            $errors[] = "{$context}.max_size must be an integer in {$configFilePath}";
        }

        if (isset($uploadConfig['mime_types'])) {
            if (!is_array($uploadConfig['mime_types'])) {
                $errors[] = "{$context}.mime_types must be an array in {$configFilePath}";
            } else {
                foreach ($uploadConfig['mime_types'] as $index => $mimeType) {
                    if (!is_string($mimeType)) {
                        $errors[] = "{$context}.mime_types[{$index}] must be a string in {$configFilePath}";
                    }
                }
            }
        }

        if (isset($uploadConfig['subdir']) && !is_string($uploadConfig['subdir'])) {
            $errors[] = "{$context}.subdir must be a string in {$configFilePath}";
        }

        return $errors;
    }

    /**
     * Validate 'validators' context configuration
     *
     * @param string $fieldName Field name for error messages
     * @param mixed $validatorsConfig Validators configuration (should be array)
     * @param string $configFilePath Config file path for error messages
     * @return array<string> Array of validation error messages
     */
    private function validateValidatorsContext(
        string $fieldName,
        mixed $validatorsConfig,
        string $configFilePath
    ): array {
        $errors = [];
        $context = "Field '{$fieldName}.form.validators'";

        if (!is_array($validatorsConfig)) {
            $errors[] = "{$context} must be an array in {$configFilePath}";
            return $errors;
        }

        foreach ($validatorsConfig as $validatorType => $validatorOptions) {
            if (!is_string($validatorType)) {
                $errors[] = "{$context}: Validator type must be a string in {$configFilePath}";
                continue;
            }

            if (!is_array($validatorOptions)) {
                $errors[] = "{$context}.{$validatorType} must be an array in {$configFilePath}";
            }
        }

        return $errors;
    }

    /**
     * Validate 'formatters' context configuration (form-level)
     *
     * @param string $fieldName Field name for error messages
     * @param mixed $formattersConfig Formatters configuration (should be array)
     * @param string $configFilePath Config file path for error messages
     * @return array<string> Array of validation error messages
     */
    private function validateFormattersContext(
        string $fieldName,
        mixed $formattersConfig,
        string $configFilePath
    ): array {
        $errors = [];
        $context = "Field '{$fieldName}.form.formatters'";

        if (!is_array($formattersConfig)) {
            $errors[] = "{$context} must be an array in {$configFilePath}";
            return $errors;
        }

        foreach ($formattersConfig as $formatterType => $formatterOptions) {
            if (!is_string($formatterType)) {
                $errors[] = "{$context}: Formatter type must be a string in {$configFilePath}";
                continue;
            }

            if (!is_array($formatterOptions)) {
                $errors[] = "{$context}.{$formatterType} must be an array in {$configFilePath}";
            }
        }

        return $errors;
    }
}