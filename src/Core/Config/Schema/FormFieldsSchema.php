<?php
// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\Core\Config\Schema\FormFieldsSchema.php

declare(strict_types=1);

namespace Core\Config\Schema;

use Psr\Log\LoggerInterface;

/**
 * Schema Validator for Form Field Config Files
 *
 * Validates the structure and business rules of form field configurations.
 *
 * Expected Structure (*_fields.php with 'form' context):
 * ```php
 * return [
 *     'field_name' => [
 *         'label' => 'Field Label', // Optional (string)
 *         'form' => [
 *             'type' => 'text', // Optional (string, default: 'text')
 *             'required' => true, // Optional (bool, default: false)
 *             'minlength' => 2, // Optional (int)
 *             'maxlength' => 100, // Optional (int)
 *             'min' => 0, // Optional (int/float, for number inputs)
 *             'max' => 999, // Optional (int/float, for number inputs)
 *             'pattern' => '^[A-Z]', // Optional (string, regex pattern)
 *             'placeholder' => 'Enter value...', // Optional (string)
 *             'attributes' => [ // Optional (array)
 *                 'class' => 'form-control',
 *                 'data-validate' => 'email',
 *             ],
 *             'validation' => [ // Optional (array of validation rules)
 *                 'email' => true,
 *                 'unique' => ['table' => 'user', 'column' => 'email'],
 *             ],
 *             'render' => [ // Optional (array)
 *                 'show_label' => true,
 *                 'display_default_choice' => true,
 *             ],
 *             'upload' => [ // Optional (array, for file inputs)
 *                 'max_size' => 2097152,
 *                 'mime_types' => ['image/jpeg', 'image/png'],
 *                 'subdir' => 'profiles',
 *             ],
 *             'validators' => [ // Optional (array, custom validators)
 *                 'text' => [
 *                     'forbidden' => ['spam', 'bad'],
 *                 ],
 *             ],
 *             'formatters' => [ // Optional (array, field formatters)
 *                 'text' => [
 *                     'max_length' => 50,
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
 * 4. ✅ 'form' must be an array (if provided)
 * 5. ✅ 'form.type' must be a valid HTML5 input type (if provided)
 * 6. ✅ 'form.required' must be a bool (if provided)
 * 7. ✅ 'form.minlength' must be a positive int (if provided)
 * 8. ✅ 'form.maxlength' must be a positive int (if provided)
 * 9. ✅ 'form.min' must be numeric (if provided)
 * 10. ✅ 'form.max' must be numeric (if provided)
 * 11. ✅ 'form.pattern' must be a string (regex) (if provided)
 * 12. ✅ 'form.placeholder' must be a string (if provided)
 * 13. ✅ 'form.attributes' must be an array (if provided)
 * 14. ✅ 'form.validation' must be an array (if provided)
 * 15. ✅ Constraint logic: minlength < maxlength, min < max
 * 16. ✅ 'form.render' must be an array (if provided)
 * 17. ✅ 'form.upload' must be an array (if provided)
 * 18. ✅ 'form.validators' must be an array (if provided)
 * 19. ✅ Unknown keys detected with suggestions
 *
 * Responsibilities (SRP):
 * - Validate form field config structure
 * - Check input type validity (HTML5 types)
 * - Validate constraint consistency (min/max, minlength/maxlength)
 * - Check validation rule format
 * - Validate render, upload, validators contexts
 *
 * This class does NOT:
 * - Execute validation rules (ValidationService's job)
 * - Render form fields (FormRenderer's job)
 * - Load config files (ConfigService's job)
 * - Check database constraints (SchemaLoaderService's job)
 *
 * @package Core\Config\Schema
 */
class FormFieldsSchema
{
    /**
     * Valid HTML5 input types
     * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#input_types
     */
    private const VALID_INPUT_TYPES = [
        'text', 'email', 'password', 'number', 'tel', 'url', 'search',
        'date', 'datetime-local', 'time', 'month', 'week',
        'color', 'range', 'file', 'hidden',
        'checkbox', 'radio',
        'textarea', 'select', // Extended for common form elements
        'checkbox_group', 'radio_group', // Custom multi-option types
    ];

    /**
     * Constructor
     *
     * @param LoggerInterface $logger For logging validation warnings/errors
     * @param UnknownKeyDetectorService $unknownKeyDetector For detecting typos
     */
    public function __construct(
        private LoggerInterface $logger,
        private UnknownKeyDetectorService $unknownKeyDetector
    ) {
    }

    /**
     * Validate a form fields config array
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

        // ✅ Rule 2: Field config must be an array
        if (!is_array($fieldConfig)) {
            $errors[] = "Field '{$fieldName}' config must be an array in {$configFilePath}";
            return $errors;
        }



        // ✅ Define allowed field-level keys (whitelist)
        $allowedFieldKeys = [
            'label',
            'data_transformer',
            'form',
            'list',  // ✅ Allowed but NOT validated here (ListFieldsSchema's job)
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

        // ✅ Validate 'data_transformer' (if provided)
        if (isset($fieldConfig['data_transformer'])) {
            if (!is_string($fieldConfig['data_transformer'])) {
                $errors[] = "Field '{$fieldName}': 'data_transformer' must be a string in {$configFilePath}";
            } else {
                $validTransformers = ['json_array', 'boolean', 'date', 'datetime', 'int', 'float'];
                if (!in_array($fieldConfig['data_transformer'], $validTransformers, true)) {
                    $closestMatch = $this->unknownKeyDetector->findClosestMatch(
                        $fieldConfig['data_transformer'],
                        $validTransformers
                    );
                    $suggestion = $closestMatch ? " Did you mean '{$closestMatch}'?" : '';
                    $errors[] = "Field '{$fieldName}': Unknown data_transformer '{$fieldConfig['data_transformer']}' in {$configFilePath}.{$suggestion}";
                }
            }
        }

        // ✅ Rule 4-19: Validate 'form' context
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

        // ✅ Rule 4: 'form' must be an array
        if (!is_array($formConfig)) {
            $errors[] = "{$context} must be an array in {$configFilePath}";
            return $errors;
        }

        // ✅ Define allowed form keys (whitelist)
        $allowedFormKeys = [
            'type',
            'required',
            'minlength',
            'maxlength',
            'min',
            'max',
            'pattern',
            'placeholder',
            'attributes',
            'validation',
            'render',
            'upload',
            'formatters',
            'options_provider',
            'options_provider_params',
            'display_default_choice',
            'inline',
            'validators',
        ];

        // ✅ Delegate to UnknownKeyDetectorService (CATCH TYPOS!)
        $unknownKeyErrors = $this->unknownKeyDetector->detectUnknownKeys(
            $formConfig,
            $allowedFormKeys,
            $context,
            $configFilePath
        );
        $errors = array_merge($errors, $unknownKeyErrors);

        // ✅ Rule 5: 'type' must be a valid HTML5 input type (if provided)
        if (isset($formConfig['type'])) {
            if (!is_string($formConfig['type'])) {
                $errors[] = "{$context}.type must be a string in {$configFilePath}";
            } else {
                if (!in_array($formConfig['type'], self::VALID_INPUT_TYPES, true)) {
                    $closestMatch = $this->unknownKeyDetector->findClosestMatch(
                        $formConfig['type'],
                        self::VALID_INPUT_TYPES
                    );
                    $suggestion = $closestMatch ? " Did you mean '{$closestMatch}'?" : '';
                    $errors[] = "{$context}.type: Unknown input type '{$formConfig['type']}' in {$configFilePath}.{$suggestion}";
                }
            }
        }

        // ✅ Rule 6: 'required' must be a bool (if provided)
        if (isset($formConfig['required']) && !is_bool($formConfig['required'])) {
            $errors[] = "{$context}.required must be a boolean in {$configFilePath}";
        }

        // ✅ Rule 7: 'minlength' must be a positive int (if provided)
        if (isset($formConfig['minlength'])) {
            if (!is_int($formConfig['minlength'])) {
                $errors[] = "{$context}.minlength must be an integer in {$configFilePath}";
            } elseif ($formConfig['minlength'] < 0) {
                $errors[] = "{$context}.minlength must be a positive integer in {$configFilePath}";
            }
        }

        // ✅ Rule 8: 'maxlength' must be a positive int (if provided)
        if (isset($formConfig['maxlength'])) {
            if (!is_int($formConfig['maxlength'])) {
                $errors[] = "{$context}.maxlength must be an integer in {$configFilePath}";
            } elseif ($formConfig['maxlength'] < 0) {
                $errors[] = "{$context}.maxlength must be a positive integer in {$configFilePath}";
            }
        }

        // ✅ Rule 15: Constraint logic: minlength < maxlength
        if (isset($formConfig['minlength']) && isset($formConfig['maxlength'])) {
            if (is_int($formConfig['minlength']) && is_int($formConfig['maxlength'])) {
                if ($formConfig['minlength'] >= $formConfig['maxlength']) {
                    $errors[] = "{$context}: minlength ({$formConfig['minlength']}) must be less than maxlength ({$formConfig['maxlength']}) in {$configFilePath}";
                }
            }
        }

        // ✅ Rule 9: 'min' must be numeric (if provided)
        if (isset($formConfig['min']) && !is_numeric($formConfig['min'])) {
            $errors[] = "{$context}.min must be a number (int or float) in {$configFilePath}";
        }

        // ✅ Rule 10: 'max' must be numeric (if provided)
        if (isset($formConfig['max']) && !is_numeric($formConfig['max'])) {
            $errors[] = "{$context}.max must be a number (int or float) in {$configFilePath}";
        }

        // ✅ Rule 15: Constraint logic: min < max
        if (isset($formConfig['min']) && isset($formConfig['max'])) {
            if (is_numeric($formConfig['min']) && is_numeric($formConfig['max'])) {
                if ($formConfig['min'] >= $formConfig['max']) {
                    $errors[] = "{$context}: min ({$formConfig['min']}) must be less than max ({$formConfig['max']}) in {$configFilePath}";
                }
            }
        }

        // ✅ Rule 11: 'pattern' must be a string (regex) (if provided)
        if (isset($formConfig['pattern']) && !is_string($formConfig['pattern'])) {
            $errors[] = "{$context}.pattern must be a string (regex pattern) in {$configFilePath}";
        }

        // ✅ Rule 12: 'placeholder' must be a string (if provided)
        if (isset($formConfig['placeholder']) && !is_string($formConfig['placeholder'])) {
            $errors[] = "{$context}.placeholder must be a string in {$configFilePath}";
        }

        // ✅ Rule 13: 'attributes' must be an array (if provided)
        if (isset($formConfig['attributes']) && !is_array($formConfig['attributes'])) {
            $errors[] = "{$context}.attributes must be an array in {$configFilePath}";
        }

        // ✅ Rule 14: Validate 'validation' rules (if provided)
        if (isset($formConfig['validation'])) {
            $validationErrors = $this->validateValidationRules(
                $fieldName,
                $formConfig['validation'],
                $configFilePath
            );
            $errors = array_merge($errors, $validationErrors);
        }

        // ✅ Rule 16: Validate 'render' context (if provided)
        if (isset($formConfig['render'])) {
            $renderErrors = $this->validateRenderContext(
                $fieldName,
                $formConfig['render'],
                $configFilePath
            );
            $errors = array_merge($errors, $renderErrors);
        }

        // ✅ Rule 17: Validate 'upload' context (if provided)
        if (isset($formConfig['upload'])) {
            $uploadErrors = $this->validateUploadContext(
                $fieldName,
                $formConfig['upload'],
                $configFilePath
            );
            $errors = array_merge($errors, $uploadErrors);
        }

        // ✅ Rule 18: Validate 'validators' context (if provided)
        if (isset($formConfig['validators'])) {
            $validatorErrors = $this->validateValidatorsContext(
                $fieldName,
                $formConfig['validators'],
                $configFilePath
            );
            $errors = array_merge($errors, $validatorErrors);
        }

        // ✅ Validate 'formatters' context (if provided)
        if (isset($formConfig['formatters'])) {
            $formatterErrors = $this->validateFormattersContext(
                $fieldName,
                $formConfig['formatters'],
                $configFilePath
            );
            $errors = array_merge($errors, $formatterErrors);
        }

        return $errors;
    }

    /**
     * Validate 'validation' rules configuration
     *
     * @param string $fieldName Field name for error messages
     * @param mixed $validationConfig Validation rules configuration (should be array)
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

        // ✅ Validation must be an array
        if (!is_array($validationConfig)) {
            $errors[] = "{$context} must be an array in {$configFilePath}";
            return $errors;
        }

        // ✅ Validate each validation rule
        foreach ($validationConfig as $ruleName => $ruleValue) {
            // ✅ Rule name must be a string
            if (!is_string($ruleName)) {
                $errors[] = "{$context}: Rule name must be a string in {$configFilePath}";
                continue;
            }

            // ✅ Rule value must be bool, string, int, or array
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

        // ✅ Define allowed render keys
        $allowedRenderKeys = [
            'show_label',
            'display_default_choice',
        ];

        // ✅ Detect unknown keys
        $unknownKeyErrors = $this->unknownKeyDetector->detectUnknownKeys(
            $renderConfig,
            $allowedRenderKeys,
            $context,
            $configFilePath
        );
        $errors = array_merge($errors, $unknownKeyErrors);

        // ✅ 'show_label' must be bool (if provided)
        if (isset($renderConfig['show_label']) && !is_bool($renderConfig['show_label'])) {
            $errors[] = "{$context}.show_label must be a boolean in {$configFilePath}";
        }

        // ✅ 'display_default_choice' must be bool (if provided)
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

        // ✅ 'max_size' must be int (if provided)
        if (isset($uploadConfig['max_size']) && !is_int($uploadConfig['max_size'])) {
            $errors[] = "{$context}.max_size must be an integer in {$configFilePath}";
        }

        // ✅ 'mime_types' must be array of strings (if provided)
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

        // ✅ 'subdir' must be string (if provided)
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

        // ✅ Each validator type must be an array
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
     * Validate 'formatters' context configuration
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

        // ✅ Each formatter type must be an array
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
