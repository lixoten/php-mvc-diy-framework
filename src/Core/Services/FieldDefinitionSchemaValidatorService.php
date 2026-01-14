<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Exceptions\FieldSchemaValidationException;
use Core\Interfaces\ConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for validating field definitions against a schema.
 *
 * This service ensures that field configurations (attributes, validation rules)
 * adhere to the defined `forms/schema.php` and best practices.
 * It identifies unknown attributes, disallowed attributes for a given type,
 * unknown validation options, and duplicated validation rules defined
 * as both HTML attributes and explicit validator settings.
 *
 * @todo Consider expanding forms/schema.php or introducing a separate meta-schema
 *       to explicitly define the structure and allowed keys/types for the 'list'
 *       and the root 'form' configuration (e.g., 'renders', 'options_provider').
 */
class FieldDefinitionSchemaValidatorService
{
    private ConfigInterface $configService;
    private LoggerInterface $logger;

    // Attributes that are explicitly HTML attributes but also have validation semantics
    private const VALIDATION_ATTRIBUTES = [
        'required', 'min', 'max', 'step', 'maxlength', 'minlength', 'pattern'
    ];

    // Common form-level configuration keys not typically found under 'attributes' in schema
    // This list helps identify known form-level config keys to avoid false positives for 'unknown'.
    // NOTE: Their types are NOT validated against forms/schema.php in its current structure.
    private const KNOWN_FORM_LEVEL_CONFIG_KEYS = [
        'type', // Handled separately as it dictates the schema type
        'attributes', // Handled separately
        'renders',
        'options_provider',
        'options_provider_params',
        'display_default_choice',
        'inline',
        'data_transformer', // Could be form or field level
        'upload', // Specific to file type fields
        'formatters',
    ];


    public function __construct(
        ConfigInterface $configService,
        LoggerInterface $logger
    ) {
        $this->configService = $configService;
        $this->logger = $logger;
    }

    /**
     * Validates a field definition array against the forms schema.
     * Logs warnings for any discrepancies found.
     *
     * @param array<string, mixed> $fieldDefinition The field definition array (e.g., from testy_fields_root.php)
     * @param string $fieldName The name of the field (e.g., 'title')
     * @param string $pageKey The page context (e.g., 'testy_list', 'testy_edit')
     * @param string $entityName The entity name (e.g., 'testy')
     * @return void
     */
    public function validateFieldDefinition(
        array $fieldDefinition,
        string $fieldName,
        string $pageKey,
        string $entityName
    ): void {
        $formSchema = $this->configService->get('forms/schema');
        if ($formSchema === null) {
            $this->reportValidationError(
                "Form schema (forms/schema.php) not found. Cannot perform field definition validation.",
                'ERR-DEV-FIELD-000'
                );
            return;
        }

        $globalSchema = $formSchema['global'] ?? [];

        $fieldType = null;

        // --- Validate 'form' section structure and config keys ---
        if (isset($fieldDefinition['form']) && is_array($fieldDefinition['form'])) {
            $formConfig = $fieldDefinition['form'];
            $fieldType = $formConfig['type'] ?? null;

            if ($fieldType === null) {
                $forFile    = $entityName . '_field_' . 'root/edit/view.php';
                $message    = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}) has a 'form' section " .
                              "but no 'type' defined. Cannot validate attributes or validators.";
                $suggestion = "Suggestion: Look at $forFile and add missing type in 'form.type =>'";
                $errorCode  = 'ERR-DEV-FIELD-001';
                $this->reportValidationError(
                    $message,
                    $errorCode,
                    $suggestion,
                    [
                        'field'      => $fieldName,
                    ]
                );
                return; // Cannot proceed with type-specific validation
            }

            if (!isset($formSchema[$fieldType])) {
                $this->reportValidationError(
                    "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}) uses unknown form type '{$fieldType}'. Cannot validate attributes or validators against schema.",
                    'ERR-DEV-FIELD-002'
                );
                // Can still proceed if global attributes exist, but type-specific validation is skipped.
            } else {
                $typeSchema = $formSchema[$fieldType];

                // Validate keys directly under 'form' (excluding 'type' and 'attributes')
                $this->validateFormLevelConfigKeys($fieldName, $pageKey, $entityName, $formConfig, $fieldType, $typeSchema, $globalSchema);

                // Validate 'form.attributes'
                if (isset($formConfig['attributes']) && is_array($formConfig['attributes'])) {
                    $this->validateFormAttributes($fieldName, $pageKey, $entityName, $formConfig['attributes'], $fieldType, $typeSchema, $globalSchema);
                }
            }
        }

        // --- Validate 'validators' section ---
        // Only proceed if a fieldType was successfully identified from the 'form' section
        if ($fieldType !== null && isset($formSchema[$fieldType])) {
            $typeSchema = $formSchema[$fieldType]; // Re-get to ensure it's from known schema
            if (isset($fieldDefinition['validators']) && is_array($fieldDefinition['validators'])) {
                $this->validateFieldValidators($fieldName, $pageKey, $entityName, $fieldDefinition['validators'], $fieldType, $typeSchema);
            }

            // --- Check for duplicated validation rules (attributes vs. validators section) ---
            if (isset($fieldDefinition['form']['attributes']) && is_array($fieldDefinition['form']['attributes'])) {
                $this->checkDuplicatedValidationRules(
                    $fieldName,
                    $pageKey,
                    $entityName,
                    $fieldDefinition['form']['attributes'],
                    $fieldDefinition['validators'] ?? [], // Pass empty array if no validators section
                    $fieldType
                );
            }
        }
    }

    /**
     * Validates attributes defined in the 'form.attributes' section,
     * including their existence, applicability, and value types.
     *
     * @param string $fieldName
     * @param string $pageKey
     * @param string $entityName
     * @param array<string, mixed> $attributes The 'attributes' array from the field's 'form' section.
     * @param string $fieldType The determined form field type (e.g., 'text', 'email').
     * @param array<string, mixed> $typeSchema The schema for the specific field type.
     * @param array<string, mixed> $globalSchema The global schema definition.
     */
    protected function validateFormAttributes(
        string $fieldName,
        string $pageKey,
        string $entityName,
        array $attributes,
        string $fieldType,
        array $typeSchema,
        array $globalSchema
    ): void {
        $unknownAttributes = [];
        foreach ($attributes as $attrName => $attrValue) {
            $schemaDefinition = null;

            // Check type-specific schema first
            if (isset($typeSchema[$attrName])) {
                $schemaDefinition = $typeSchema[$attrName];
            } elseif (isset($globalSchema[$attrName])) {
                // If not found in type-specific or not explicitly disallowed, check global schema
                $schemaDefinition = $globalSchema[$attrName];
            }
// $schemaDefinition = false;
            if ($schemaDefinition === null) {
                $unknownAttributes[] = $attrName;
            } elseif ($schemaDefinition === false) { // This condition checks if attribute is explicitly null/disallowed
                $errDetails = [];
                $errDetails['title'] = "Form Configuration Validation Failed. Form Attributes : ERR-DEV-FIELD-003";
                $errDetails['title'] .= "xxxxxxxx";
                $errDetails['msg']   = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}, type: {$fieldType}) uses attribute '{$attrName}' which is explicitly disallowed for type '{$fieldType}'.";
                $message =  implode("\n", $errDetails);

                $errDetails['field']  = $fieldName;
                $errDetails['page']   = $pageKey;
                $errDetails['entity'] = $entityName;
                $errDetails['type']   = $fieldType;
                //$errDetails['configKey']   = "Configuration key: '{$configKey}' is defined as an HTML attribute in forms/schema.php but found directly under 'form'. It should be moved to 'form.attributes'.";
                $errDetails['error_code']  = "ERR-DEV-FIELD-003";
                //$errDetails['suggestions'] = " Fix;: Look in config file for '{$configKey}' under 'form =>' and move it to 'attributes =>'.";
                //$errDetails['error']  = "🔴 Errors Found:";

                $this->reportValidationError(
                    $message,
                    'ERR-DEV-FIELD-003',
                    '',
                    $errDetails
                );
            } elseif (is_array($schemaDefinition)) {
                // Validate the attribute value against the schema's 'values' definition
                $this->validateSchemaValue(
                    $attrValue,
                    $schemaDefinition,
                    $attrName,
                    "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}, type: {$fieldType}), attribute '{$attrName}'"
                );
            }
            // If $schemaDefinition is just a string (e.g., 'autocomplete' => []), it's valid but without type info.
        }

        if (!empty($unknownAttributes)) {
            $message = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}, type: {$fieldType}) has unknown attributes: " . implode(', ', $unknownAttributes);
            $this->reportValidationError($message, 'ERR-DEV-FIELD-004');
        }
    }

    /**
     * Validates options defined in the 'validators' section for a specific field type,
     * including their existence and value types.
     *
     * @param string $fieldName
     * @param string $pageKey
     * @param string $entityName
     * @param array<string, mixed> $validatorsConfig The raw 'validators' section from the field definition.
     * @param string $fieldType
     * @param array<string, mixed> $typeSchema The schema for the specific field type (e.g., $formSchema['text']).
     */
    protected function validateFieldValidators(string $fieldName, string $pageKey, string $entityName, array $validatorsConfig, string $fieldType, array $typeSchema): void
    {
        if (!isset($validatorsConfig[$fieldType]) || !is_array($validatorsConfig[$fieldType])) {
            // No specific validators for this field type or invalid format, nothing to validate here.
            return;
        }

        $fieldSpecificValidators = $validatorsConfig[$fieldType];
        $defaultValidationRules  = $typeSchema['default_validation_rules'] ?? [];

        $unknownValidatorOptions = [];
        foreach ($fieldSpecificValidators as $ruleName => $ruleValue) {
            // Skip 'message' keys as per user's example in warnUnknownOptions
            if (str_contains($ruleName, 'message')) {
                continue;
            }

            $ruleSchema = $defaultValidationRules[$ruleName] ?? null;

            if ($ruleSchema === null) {
                $unknownValidatorOptions[] = $ruleName;
            } elseif (is_array($ruleSchema)) {
                $this->validateSchemaValue(
                    $ruleValue,
                    $ruleSchema,
                    $ruleName,
                    "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}, type: {$fieldType}), validator rule '{$ruleName}'"
                );
            }
        }

        if (!empty($unknownValidatorOptions)) {
            $message = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}, type: {$fieldType}) has unknown validator options for type '{$fieldType}': " . implode(', ', $unknownValidatorOptions);
            $this->reportValidationError(
                $message,
                'ERR-DEV-FIELD-005',
                "You either have a type-o, a validating attribute that does not belong in validator options,
                or just something that does not define in schema for type: {$fieldType})."
            );
        }
    }

    /**
     * Validates keys directly under the 'form' section of a field definition.
     * This checks for misplaced attributes and identifies unknown form-level config keys.
     *
     * @param string $fieldName
     * @param string $pageKey
     * @param string $entityName
     * @param array<string, mixed> $formConfig The raw 'form' section from the field definition.
     * @param string $fieldType The determined form field type.
     * @param array<string, mixed> $typeSchema The schema for the specific field type.
     * @param array<string, mixed> $globalSchema The global schema definition.
     */
    protected function validateFormLevelConfigKeys(
        string $fieldName,
        string $pageKey,
        string $entityName,
        array $formConfig,
        string $fieldType,
        array $typeSchema,
        array $globalSchema
    ): void {
        $unknownFormConfigKeys = [];
        foreach ($formConfig as $configKey => $configValue) {
            // Skip keys handled by dedicated validation methods
            if ($configKey === 'type' || $configKey === 'attributes') {
                continue;
            }

            // Check if this form-level key is actually defined as an attribute in the schema
            $isAttributeInSchema = (isset($typeSchema[$configKey]) && is_array($typeSchema[$configKey])) ||
                                   (isset($globalSchema[$configKey]) && is_array($globalSchema[$configKey]));


            // ✅ Specific validation for 'formatters' under 'form'
            if ($configKey === 'formatters') {
                if ($fieldType !== 'tel') {
                    $this->reportValidationError(
                        "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}, type: {$fieldType}): 'formatters' key is only allowed under 'form' for fields of type 'tel'.",
                        'ERR-DEV-FIELD-011',
                        "Remove 'formatters' from the 'form' section for non-'tel' fields, or ensure the field 'type' is 'tel'."
                    );
                }
                // If it is 'tel', it's valid, so continue to next configKey
                continue; // Skip further checks for this known key
            }


            if ($isAttributeInSchema) {
                // This key is defined as an HTML attribute in forms/schema.php,
                // but it's found directly under 'form' instead of 'form.attributes'.
                // $errDetails = [];
                // $errDetails['title'] = "Form 5 Configuration Validation Failed";
                // $errDetails['msg']   = "Field: '{$fieldName}', Configuration key: '{$configKey}' is defined as an HTML attribute in forms/schema.php but found directly under 'form'. It should be moved to 'form.attributes'.";
                // $message =  implode("\n", $errDetails);

                // $errDetails['field']  = $fieldName;
                // $errDetails['page']   = $pageKey;
                // $errDetails['entity'] = $entityName;
                // $errDetails['type']   = $fieldType;
                // $errDetails['configKey']   = "Configuration key: '{$configKey}' is defined as an HTML attribute in
                //                               forms/schema.php but found directly under 'form'.
                //                               It should be moved to 'form.attributes'.";
                // $errDetails['error_code']  = "ERR-DEV-FIELD-007";
                // $errDetails['suggestions'] = " Fix;: Look in config file for '{$configKey}' under 'form =>' and
                //                                move it to 'attributes =>'.";
                // $errDetails['error']  = "🔴 Errors Found:";

                $message = "Field: '{$fieldName}', Configuration key: '{$configKey}' is defined as an HTML attribute " .
                           "in forms/schema.php but found directly under 'form'.";
                $suggestion = "Suggestion: Look in config file for '{$configKey}' under 'form =>' and " .
                              "move it to 'form.attributes =>'.";

                $errorCode = 'ERR-DEV-FIELD-007';
                $this->reportValidationError(
                    $message,
                    $errorCode,
                    $suggestion,
                    [
                        'entity'      => $entityName,
                    ]
                );
                // $this->reportValidationError(
                    // "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}, type: {$fieldType}): Configuration key '{$configKey}' is defined as an HTML attribute in forms/schema.php but found directly under 'form'. It should be moved to 'form.attributes'.",
                    // 'ERR-DEV-FIELD-007',
                    // "Look in config file for '{$configKey}' under 'form =>' and move it to 'attributes =>'."
                // );
            } elseif (!in_array($configKey, self::KNOWN_FORM_LEVEL_CONFIG_KEYS, true)) {
                // This is a form-level config key not defined as an attribute and not in our known list.
                // NOTE: forms/schema.php does not define the structure or types for these keys.
                $unknownFormConfigKeys[] = $configKey;
            }
            // For known form-level config keys (e.g., 'renders', 'options_provider'),
            // we currently don't have schema to validate their internal structure or types.
        }

        if (!empty($unknownFormConfigKeys)) {
            $message = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}, type: {$fieldType}) has unknown form-level configuration key(s): " . implode(', ', $unknownFormConfigKeys);
            $suggestion = "These keys are not defined as HTML attributes in forms/schema.php, nor are they in the list of known form-level config keys. Consider adding them to 'form.attributes' if they are HTML attributes, or define a meta-schema for form-level configurations.";
            $this->reportValidationError($message, 'ERR-DEV-FIELD-008', $suggestion);
        }
    }


    /**
     * Helper to validate a given value against a schema definition's 'values' rule.
     *
     * @param mixed $value The value to validate.
     * @param array<string, mixed> $schemaDefinition The schema array for the
     *                             attribute/rule (e.g., ['values' => 'int']).
     * @param string $name The name of the attribute or rule for logging.
     * @param string $contextPrefix A string prefix for log messages to provide context.
     */
    protected function validateSchemaValue(
        mixed $value,
        array $schemaDefinition,
        string $name,
        string $contextPrefix
    ): void {
        if (!isset($schemaDefinition['values'])) {
            return; // No value validation defined in schema
        }

        $expectedValues = $schemaDefinition['values'];
        $isValid = true;
        $problem = '';

        if (is_string($expectedValues)) {
            switch ($expectedValues) {
                case 'string':
                    $isValid = is_string($value);
                    $problem = 'expected a string';
                    break;
                case 'int':
                    $isValid = is_int($value);
                    $problem = 'expected an integer';
                    break;
                case 'bool':
                    $isValid = is_bool($value);
                    $problem = 'expected a boolean';
                    break;
                case 'numeric':
                    $isValid = is_numeric($value);
                    $problem = 'expected a numeric value';
                    break;
                case 'array':
                    $isValid = is_array($value);
                    $problem = 'expected an array';
                    break;
                // Add more primitive types as needed
                default:
                    // Unknown type specifier in schema, cannot validate
                    $this->reportValidationError("{$contextPrefix}: Schema defines unknown type specifier '{$expectedValues}' for value validation.", 'ERR-DEV-FIELD-009');
                    return;
            }
        } elseif (is_array($expectedValues)) {
            // Specific allowed values (e.g., ['ltr', 'rtl'])
            $isValid = in_array($value, $expectedValues, true);
            $problem = 'expected one of [' . implode(', ', array_map(fn($v) => is_bool($v) ? ($v ? 'true' : 'false') : (string)$v, $expectedValues)) . ']';
        }

        if (!$isValid) {
                $errDetails = [];
                $errDetails['title'] = "Form Configuration Validation Failed. Form Attributes : ERR-DEV-FIELD-003";
                $errDetails['title'] .= "Attributes against Form Schema";
                $errDetails['msg']   =  "{$contextPrefix}: Invalid value '{$valueStr}'. {$problem}, but got '{$valueStr}'.";
                $message =  implode("\n", $errDetails);

                // $errDetails['contextPrefix']  = $contextPrefix;
                // $errDetails['valueStr']   = $valueStr;
                // $errDetails['entity'] = $entityName;
                // $errDetails['type']   = $fieldType;
                //$errDetails['configKey']   = "Configuration key: '{$configKey}' is defined as an HTML attribute in forms/schema.php but found directly under 'form'. It should be moved to 'form.attributes'.";
                $errDetails['error_code']  = "ERR-DEV-FIELD-010";
                $errDetails['problem']  =  "{$contextPrefix}: Invalid value '{$valueStr}'. {$problem}, but got '{$valueStr}'.";
                //$errDetails['suggestions'] = " Fix;: Look in config file for '{$configKey}' under 'form =>' and move it to 'attributes =>'.";
                //$errDetails['error']  = "🔴 Errors Found:";


            $valueStr = is_scalar($value) ? (string)$value : get_debug_type($value);
            $this->reportValidationError(
                "{$contextPrefix}: Invalid value '{$valueStr}'. {$problem}, but got '{$valueStr}'.",
                'ERR-DEV-FIELD-010',
                '',
                $errDetails
            );
        }
    }

    /**
     * Checks for duplication where an attribute is defined in both 'form.attributes'
     * and the 'validators' section for the same field.
     *
     * @param string $fieldName
     * @param string $pageKey
     * @param string $entityName
     * @param array<string, mixed> $formAttributes The 'attributes' array from the field's 'form' section.
     * @param array<string, mixed> $fieldValidatorsConfig The raw 'validators' section from the field definition.
     * @param string $fieldType The type of the form field (e.g., 'text', 'email').
     */
    protected function checkDuplicatedValidationRules(
        string $fieldName,
        string $pageKey,
        string $entityName,
        array $formAttributes,
        array $fieldValidatorsConfig,
        string $fieldType
    ): void {
        if (!isset($fieldValidatorsConfig[$fieldType])) {
            // No specific validators for this type, so no duplication possible
            return;
        }

        $fieldSpecificValidators = $fieldValidatorsConfig[$fieldType];
        $duplicatedRules = [];

        foreach (self::VALIDATION_ATTRIBUTES as $attrName) {
            // Check if the attribute is present in 'form.attributes'
            if (isset($formAttributes[$attrName])) {
                // Check if it's also explicitly defined as a validator rule for this type
                if (array_key_exists($attrName, $fieldSpecificValidators)) {
                    $duplicatedRules[] = $attrName;
                }
            }
        }

        if (!empty($duplicatedRules)) {
            $forItems = implode(', ', $duplicatedRules);
            $message = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}, type: {$fieldType}): Duplicated validation rule(s). Attribute(s) '{$forItems}' are defined in both 'form.attributes' and 'validators.{$fieldType}'.";
            $suggestion = "Do not repeat attributes that are validation rules (e.g., required, minlength) in the 'validators.{$fieldType}' section if they are already in 'form.attributes'. Look under 'validators =>' for '{$forItems}' and remove it/them.";
            $this->reportValidationError($message, 'ERR-DEV-FIELD-006', $suggestion);
        }
    }

    /**
     * Handles a validation error by either throwing an exception or logging a warning,
     * based on the 'throwOnValidationFailure' flag.
     *
     * @param string $message The main error message.
     * @param string $devCode A unique developer error code.
     * @param string|null $suggestion An optional suggestion for the developer.
     * @throws FieldSchemaValidationException If production
     */
    private function reportValidationError(
        string $message,
        string $devCode,
        ?string $suggestion = null,
        ?array $details = null
    ): void {
        // Only log if not throwing and in development (or as per logger's config)
        if (($_ENV['APP_ENV'] ?? null) === 'development') { //fixme
            // throw new FieldSchemaValidationException($message, $devCode, $suggestion);
            $this->logger->warning(
                $message,
                ['dev_code' => $devCode, 'suggestion' => $suggestion, 'details' => $details]
            );
        throw new FieldSchemaValidationException($message, $devCode, $suggestion);
        } else {
            // $this->logger->warning($message, ['suggestion' => $suggestion, 'details' => $details]);
            throw new FieldSchemaValidationException($message, $devCode, $suggestion);
        }
    }
}
