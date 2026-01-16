<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Exceptions\FieldSchemaValidationException;
use Core\Interfaces\ConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for validating field definitions against a schema.
 *
 * ✅ Context-Aware Validation:
 * - Validates only the section relevant to the current context (list/form/full)
 * - 'list' context: Only validates 'list' section
 * - 'form' context: Only validates 'form', 'validators' sections
 * - 'full' context: Validates all sections
 */
class FieldDefinitionSchemaValidatorService
{
    private ConfigInterface $configService;
    private LoggerInterface $logger;

    private const VALIDATION_ATTRIBUTES = [
        'required', 'min', 'max', 'step', 'maxlength', 'minlength', 'pattern'
    ];

    private const KNOWN_FORM_LEVEL_CONFIG_KEYS = [
        'type',
        'attributes',
        'renders',
        'options_provider',
        'options_provider_params',
        'display_default_choice',
        'inline',
        'data_transformer',
        'upload',
        'formatters',
    ];

    private const KNOWN_LIST_LEVEL_CONFIG_KEYS = [
        'sortable',
        'formatter',
        'formatters',
        'class',
        'style',
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
     *
     * @param array<string, mixed> $fieldDefinition The field definition array
     * @param string $fieldName The name of the field (e.g., 'title')
     * @param string $pageKey The page context (e.g., 'testy_list', 'testy_edit')
     * @param string $entityName The entity name (e.g., 'testy')
     * @param string $context Validation context: 'list', 'form', or 'full' (default: 'full')
     * @return void
     * @throws FieldSchemaValidationException On validation failure
     */
    public function validateFieldDefinition(
        array $fieldDefinition,
        string $fieldName,
        string $pageKey,
        string $entityName,
        string $context = 'full'
    ): void {
        // ✅ Load schema
        $formSchema = $this->configService->get('forms/schema');
        if ($formSchema === null) {
            $forFile    = $entityName . '_fields_' . 'root/edit/view.php';
            $message    = "Form schema (forms/schema.php) not found. Cannot perform field definition validation.";
            $suggestion = "Ensure 'src/Config/forms/schema.php' exists and is properly configured.";
            $errorCode  = 'ERR-DEV-FD-001';

            $this->reportValidationError(
                $message,
                $errorCode,
                $suggestion,
                [
                    'field'  => $fieldName,
                    'page'   => $pageKey,
                    'entity' => $entityName,
                ]
            );
        }

        $globalSchema = $formSchema['global'] ?? [];

        // ✅ Validate based on context
        switch ($context) {
            case 'list':
                $this->validateListContext($fieldDefinition, $fieldName, $pageKey, $entityName);
                break;

            case 'form':
                $this->validateFormContext($fieldDefinition, $fieldName, $pageKey, $entityName, $formSchema, $globalSchema);
                break;

            case 'full':
                if (isset($fieldDefinition['list'])) {
                    $this->validateListContext($fieldDefinition, $fieldName, $pageKey, $entityName);
                }
                if (isset($fieldDefinition['form'])) {
                    $this->validateFormContext($fieldDefinition, $fieldName, $pageKey, $entityName, $formSchema, $globalSchema);
                }
                break;

            default:
                throw new \InvalidArgumentException("Invalid validation context: '{$context}'. Allowed: 'list', 'form', 'full'.");
        }
    }

    /**
     * ✅ Validates the 'list' section of a field definition.
     */
    protected function validateListContext(
        array $fieldDefinition,
        string $fieldName,
        string $pageKey,
        string $entityName
    ): void {
        if (!isset($fieldDefinition['list']) || !is_array($fieldDefinition['list'])) {
            return; // 'list' section is optional
        }

        $listConfig = $fieldDefinition['list'];
        $unknownListKeys = [];

        foreach (array_keys($listConfig) as $key) {
            if (!in_array($key, self::KNOWN_LIST_LEVEL_CONFIG_KEYS, true)) {
                $unknownListKeys[] = $key;
            }
        }

        if (!empty($unknownListKeys)) {
            $forFile    = $entityName . '_fields_' . 'list.php';
            $message    = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}) has unknown keys in 'list' " .
                          "section: " . implode(', ', $unknownListKeys);
            $suggestion = "Allowed keys in 'list' section: " . implode(', ', self::KNOWN_LIST_LEVEL_CONFIG_KEYS) .
                          ". Look at {$forFile}.";
            $errorCode  = 'ERR-DEV-L-FD-012'; // ok

            $this->reportValidationError(
                $message,
                $errorCode,
                $suggestion,
                [
                    'field'        => $fieldName,
                    'page'         => $pageKey,
                    'entity'       => $entityName,
                    'unknown_keys' => $unknownListKeys,
                ]
            );
        }

        // ✅ Validate 'sortable' is boolean
        if (isset($listConfig['sortable']) && !is_bool($listConfig['sortable'])) {
            $forFile    = $entityName . '_fields_' . 'list.php';
            $message    = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}) in 'list.sortable' must be " .
                          "a boolean.";
            $suggestion = "Change 'sortable' value to true or false in {$forFile}.";
            $errorCode  = 'ERR-DEV-L-FD-013'; // ok

            $this->reportValidationError(
                $message,
                $errorCode,
                $suggestion,
                [
                    'field'  => $fieldName,
                    'page'   => $pageKey,
                    'entity' => $entityName,
                ]
            );
        }

        // ⚠️ Warn if both 'formatter' (singular) and 'formatters' (plural) are present
        if (isset($listConfig['formatter']) && isset($listConfig['formatters'])) {
            $forFile    = $entityName . '_fields_' . 'list.php';
            $message    = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}) has BOTH 'formatter' " .
                          "(singular) AND 'formatters' (plural) in 'list' section.";
            $suggestion = "Use only 'formatters' (plural) array. Remove 'formatter' (singular) from {$forFile}.";
            $errorCode  = 'ERR-DEV-L-FD-014'; // ok

            $this->reportValidationError(
                $message,
                $errorCode,
                $suggestion,
                [
                    'field'  => $fieldName,
                    'page'   => $pageKey,
                    'entity' => $entityName,
                ]
            );
        }
    }

    /**
     * ✅ Validates the 'form' section of a field definition.
     */
    protected function validateFormContext(
        array $fieldDefinition,
        string $fieldName,
        string $pageKey,
        string $entityName,
        array $formSchema,
        array $globalSchema
    ): void {
        // ✅ Validate 'form' section exists
        if (!isset($fieldDefinition['form']) || !is_array($fieldDefinition['form'])) {
            $forFile    = $entityName . '_fields_' . 'root/edit/view.php';
            $message    = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}) is missing 'form' " .
                          "section or 'form' is not an array.";
            $suggestion = "Add a 'form' array with at least a 'type' key. Look at {$forFile}.";
            $errorCode  = 'ERR-DEV-F-FD-004'; //ok

            $this->reportValidationError(
                $message,
                $errorCode,
                $suggestion,
                [
                    'field'  => $fieldName,
                    'page'   => $pageKey,
                    'entity' => $entityName,
                ]
            );
        } //else {
            //$rrr = 1; // fixxme what if form is missing?
        //}

        $formConfig = $fieldDefinition['form'];
        $fieldType = $formConfig['type'] ?? null;

        if ($fieldType === null) {
            $forFile    = $entityName . '_fields_' . 'root/edit/view.php';
            $message    = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}) has a 'form' section but " .
                          "no 'type' defined.";
            $suggestion = "Add 'type' => 'text' (or appropriate type) to the 'form' array in {$forFile}.";
            $errorCode  = 'ERR-DEV-F-FD-002'; // ok

            $this->reportValidationError(
                $message,
                $errorCode,
                $suggestion,
                [
                    'field'  => $fieldName,
                    'page'   => $pageKey,
                    'entity' => $entityName,
                ]
            );
        }

        if (!isset($formSchema[$fieldType])) {
            $validTypes = array_filter(array_keys($formSchema), fn($key) => $key !== 'global');
            $forFile    = $entityName . '_fields_' . 'root/edit/view.php';
            $message    = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}) uses unknown form type '{$fieldType}'.";
            $suggestion = "Valid types: " . implode(', ', $validTypes) . ". Update 'form.type' in {$forFile} or add '{$fieldType}' to forms/schema.php.";
            $errorCode  = 'ERR-DEV-F-FD-003'; // ok

            $this->reportValidationError(
                $message,
                $errorCode,
                $suggestion,
                [
                    'field'       => $fieldName,
                    'page'        => $pageKey,
                    'entity'      => $entityName,
                    'invalid_type' => $fieldType,
                    'valid_types' => $validTypes,
                ]
            );
        }

        $typeSchema = $formSchema[$fieldType];

        // ✅ Validate form-level config keys
        $this->validateFormLevelConfigKeys($fieldName, $pageKey, $entityName, $formConfig, $fieldType, $typeSchema, $globalSchema);

        // ✅ Validate 'form.attributes'
        if (isset($formConfig['attributes']) && is_array($formConfig['attributes'])) {
            $this->validateFormAttributes($fieldName, $pageKey, $entityName, $formConfig['attributes'], $fieldType, $typeSchema, $globalSchema);
        }

        // ✅ Validate 'validators' section
        if (isset($fieldDefinition['validators']) && is_array($fieldDefinition['validators'])) {
            $this->validateFieldValidators($fieldName, $pageKey, $entityName, $fieldDefinition['validators'], $fieldType, $typeSchema);
        }

        // ✅ Check for duplicated validation rules
        if (isset($formConfig['attributes']) && is_array($formConfig['attributes'])) {
            $this->checkDuplicatedValidationRules($fieldName, $pageKey, $entityName, $formConfig['attributes'], $fieldDefinition['validators'] ?? [], $fieldType);
        }
    }

    /**
     * Validates attributes in 'form.attributes' section.
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
            $schemaDefinition = $typeSchema[$attrName] ?? $globalSchema[$attrName] ?? null;

            if ($schemaDefinition === null) {
                $unknownAttributes[] = $attrName;
            } elseif ($schemaDefinition === false) {
                $forFile    = $entityName . '_fields_' . 'root/edit/view.php';
                $message    = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}, type: {$fieldType}) " .
                              "uses attribute '{$attrName}' which is explicitly disallowed for type '{$fieldType}'.";
                $suggestion = "Remove '{$attrName}' from 'form.attributes' in {$forFile} or change the field type.";
                $errorCode  = 'ERR-DEV-F-FD-015'; //ok

                $this->reportValidationError(
                    $message,
                    $errorCode,
                    $suggestion,
                    [
                        'field'               => $fieldName,
                        'page'                => $pageKey,
                        'entity'              => $entityName,
                        'disallowed_attribute' => $attrName,
                        'field_type'          => $fieldType,
                    ]
                );
            } elseif (is_array($schemaDefinition)) {
                $this->validateSchemaValue(
                    $attrValue,
                    $schemaDefinition,
                    $attrName,
                    $fieldName,
                    $pageKey,
                    $entityName,
                    $fieldType,
                    'form.attributes'
                );
            }
        }

        if (!empty($unknownAttributes)) {
            $forFile    = $entityName . '_fields_' . 'root/edit/view.php';
            $message    = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}, type: {$fieldType}) has " .
                          "unknown attributes in 'form.attributes': " . implode(', ', $unknownAttributes);
            $suggestion = "Remove unknown attributes or add them to forms/schema.php under '{$fieldType}' or " .
                          "'global' section. Look at {$forFile}.";
            $errorCode  = 'ERR-DEV-F-FD-005'; // ok

            $this->reportValidationError(
                $message,
                $errorCode,
                $suggestion,
                [
                    'field'              => $fieldName,
                    'page'               => $pageKey,
                    'entity'             => $entityName,
                    'unknown_attributes' => $unknownAttributes,
                    'field_type'         => $fieldType,
                ]
            );
        }
    }

    /**
     * Validates options in 'validators' section.
     */
    protected function validateFieldValidators(
        string $fieldName,
        string $pageKey,
        string $entityName,
        array $validatorsConfig,
        string $fieldType,
        array $typeSchema
    ): void {
        if (!isset($validatorsConfig[$fieldType]) || !is_array($validatorsConfig[$fieldType])) {
            return;
        }

        $fieldSpecificValidators = $validatorsConfig[$fieldType];
        $defaultValidationRules = $typeSchema['default_validation_rules'] ?? [];
        $unknownValidatorOptions = [];

        foreach ($fieldSpecificValidators as $ruleName => $ruleValue) {
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
                    $fieldName,
                    $pageKey,
                    $entityName,
                    $fieldType,
                    "validators.{$fieldType}"
                );
            }
        }

        if (!empty($unknownValidatorOptions)) {
            $forFile    = $entityName . '_fields_' . 'root/edit/view.php';
            $message    = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}, type: {$fieldType}) has " .
                          "unknown validator options in 'validators.{$fieldType}': '"
                          . implode(', ', $unknownValidatorOptions) . "'";
            $suggestion = "Check for typos or add these options to forms/schema.php under '{$fieldType}' -> " .
                          "'default_validation_rules'. Look at {$forFile}.";
            $errorCode  = 'ERR-DEV-F-FD-006'; // ok

            $this->reportValidationError(
                $message,
                $errorCode,
                $suggestion,
                [
                    'field'           => $fieldName,
                    'page'            => $pageKey,
                    'entity'          => $entityName,
                    'unknown_options' => $unknownValidatorOptions,
                    'field_type'      => $fieldType,
                ]
            );
        }
    }

    /**
     * Validates keys directly under 'form' section.
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
            if ($configKey === 'type' || $configKey === 'attributes') {
                continue;
            }

            $isAttributeInSchema = (isset($typeSchema[$configKey]) && is_array($typeSchema[$configKey])) ||
                                   (isset($globalSchema[$configKey]) && is_array($globalSchema[$configKey]));

            // if ($configKey === 'formatters' && $fieldType !== 'tel') {
            if (
                $configKey === 'formatters'
                && !in_array($fieldType, ['tel', 'file'], true)
            ) {
                $forFile    = $entityName . '_fields_' . 'root/edit/view.php';
                $message    = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}, type: {$fieldType}) " .
                              "has 'formatters' key under 'form', but this is only allowed for 'tel' type fields.";
                $suggestion = "Remove 'formatters' from 'form' section or change field type to 'tel' in {$forFile}.";
                $errorCode  = 'ERR-DEV-F-FD-011'; // ok

                $this->reportValidationError(
                    $message,
                    $errorCode,
                    $suggestion,
                    [
                        'field'      => $fieldName,
                        'page'       => $pageKey,
                        'entity'     => $entityName,
                        'field_type' => $fieldType,
                    ]
                );
            }

            if ($isAttributeInSchema) {
                $forFile    = $entityName . '_fields_' . 'root/edit/view.php';
                $message    = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}, type: {$fieldType}): " .
                              "Configuration key '{$configKey}' is defined as an HTML attribute in forms/schema.php " .
                              "but found directly under 'form'.";
                $suggestion = "Move '{$configKey}' from 'form' => ['{$configKey}' => ...] to 'form' => " .
                              "['attributes' => ['{$configKey}' => ...]] in {$forFile}.";
                $errorCode  = 'ERR-DEV-F-FD-007'; // ok

                $this->reportValidationError(
                    $message,
                    $errorCode,
                    $suggestion,
                    [
                        'field'         => $fieldName,
                        'page'          => $pageKey,
                        'entity'        => $entityName,
                        'misplaced_key' => $configKey,
                    ]
                );
            } elseif (!in_array($configKey, self::KNOWN_FORM_LEVEL_CONFIG_KEYS, true)) {
                $unknownFormConfigKeys[] = $configKey;
            }
        }

        if (!empty($unknownFormConfigKeys)) {
            $forFile    = $entityName . '_fields_' . 'root/edit/view.php';
            $message    = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}, type: {$fieldType}) has " .
                          "unknown form-level configuration key(s) under 'form': " .
                          implode(', ', $unknownFormConfigKeys);
            $suggestion = "Move to 'form.attributes' if they are HTML attributes, or add to " .
                          "KNOWN_FORM_LEVEL_CONFIG_KEYS if valid. Look at {$forFile}.";
            $errorCode  = 'ERR-DEV-F-FD-008'; // ok

            $this->reportValidationError(
                $message,
                $errorCode,
                $suggestion,
                [
                    'field'        => $fieldName,
                    'page'         => $pageKey,
                    'entity'       => $entityName,
                    'unknown_keys' => $unknownFormConfigKeys,
                    'field_type'   => $fieldType,
                ]
            );
        }
    }

    /**
     * Validates a value against schema definition.
     */
    protected function validateSchemaValue(
        mixed $value,
        array $schemaDefinition,
        string $name,
        string $fieldName,
        string $pageKey,
        string $entityName,
        string $fieldType,
        string $section
    ): void {
        if (!isset($schemaDefinition['values'])) {
            return;
        }

        $expectedValues = $schemaDefinition['values'];
        $isValid = true;
        $problem = '';

        if (is_string($expectedValues)) {
            $isValid = match ($expectedValues) {
                'string' => is_string($value),
                'int' => is_int($value),
                'bool' => is_bool($value),
                'numeric' => is_numeric($value),
                'array' => is_array($value),
                'array_of_mime_types' => $this->isValidMimeTypeArray($value),
                default => throw new \RuntimeException("Unknown type specifier: {$expectedValues}")
            };

            // ✅ More descriptive problem messages for array_of_mime_types
            if ($expectedValues === 'array_of_mime_types' && !$isValid) {
                if (!is_array($value)) {
                    $problem = "expected an array of valid MIME type strings, but got " . get_debug_type($value);
                } elseif (empty($value)) {
                    $problem = "expected a non-empty array of valid MIME type strings, but got an empty array";
                } else {
                    // ✅ Find the first invalid MIME type to show in error message
                    $invalidMimeType = $this->getFirstInvalidMimeType($value);
                    $problem = "expected an array of valid MIME type strings (e.g., 'image/jpeg', 'application/pdf'), but found invalid MIME type: '{$invalidMimeType}'";
                }
            } else {
                $problem = "expected a {$expectedValues}";
            }        } elseif (is_array($expectedValues)) {
            $isValid = in_array($value, $expectedValues, true);
            $problem = 'expected one of [' . implode(', ', $expectedValues) . ']';
        }

        if (!$isValid) {
            // $valueStr = is_scalar($value) ? (string)$value : get_debug_type($value);
            $valueStr = ($expectedValues === 'array_of_mime_types' && is_array($value))
                ? 'array with invalid MIME types'
                : (is_scalar($value) ? (string)$value : get_debug_type($value));

            $forFile  = $entityName . '_fields_' . 'root/edit/view.php';
            $message  = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}, type: {$fieldType}) in " .
                        "'{$section}': Invalid value for '{$name}'. {$problem}, but got '{$valueStr}'.";
            $suggestion = "Fix the value in {$forFile} to match the expected type/values defined in forms/schema.php.";
            $errorCode  = 'ERR-DEV-F-FD-010'; // ok

            $this->reportValidationError(
                $message,
                $errorCode,
                $suggestion,
                [
                    'field'         => $fieldName,
                    'page'          => $pageKey,
                    'entity'        => $entityName,
                    'attribute'     => $name,
                    'invalid_value' => $valueStr,
                    'expected'      => $problem,
                    'field_type'    => $fieldType,
                    'section'       => $section,
                ]
            );
        }
    }

    /**
     * Checks for duplicated validation rules.
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
            return;
        }

        $fieldSpecificValidators = $fieldValidatorsConfig[$fieldType];
        $duplicatedRules = [];

        foreach (self::VALIDATION_ATTRIBUTES as $attrName) {
            if (isset($formAttributes[$attrName]) && array_key_exists($attrName, $fieldSpecificValidators)) {
                $duplicatedRules[] = $attrName;
            }
        }

        if (!empty($duplicatedRules)) {
            $forItems   = implode(', ', $duplicatedRules);
            $forFile    = $entityName . '_fields_' . 'root/edit/view.php';
            $message    = "Field '{$fieldName}' (page: {$pageKey}, entity: {$entityName}, type: {$fieldType}): " .
                          "Duplicated validation rule(s). Attribute(s) '{$forItems}' are defined in BOTH " .
                          "'form.attributes' AND 'validators.{$fieldType}'.";
            $suggestion = "Remove '{$forItems}' from 'validators.{$fieldType}' section in {$forFile} since they are " .
                          "already in 'form.attributes'.";
            $errorCode  = 'ERR-DEV-F-FD-012'; // ok

            $this->reportValidationError(
                $message,
                $errorCode,
                $suggestion,
                [
                    'field'            => $fieldName,
                    'page'             => $pageKey,
                    'entity'           => $entityName,
                    'duplicated_rules' => $duplicatedRules,
                    'field_type'       => $fieldType,
                ]
            );
        }
    }

    /**
     * ✅ Centralized error reporting - follows your exact pattern.
     *
     * @param string $message The error message
     * @param string $devCode Developer error code
     * @param string|null $suggestion How to fix the issue
     * @param array<string, mixed>|null $details Additional context
     * @throws FieldSchemaValidationException
     */
    private function reportValidationError(
        string $message,
        string $devCode,
        ?string $suggestion = null,
        ?array $details = null
    ): void {
        throw new FieldSchemaValidationException($message, $devCode, $suggestion);
    }

    /**
     * Validates that a value is an array of properly formatted MIME type strings.
     *
     * @param mixed $value The value to validate
     * @return bool True if valid array of MIME types, false otherwise
     */
    private function isValidMimeTypeArray(mixed $value): bool
    {
        if (!is_array($value) || empty($value)) {
            return false;
        }

        $validMimePatterns = [
            // Images
            '/^image\/(jpeg|jpg|png|gif|webp|svg\+xml|bmp|tiff|x-icon|avif)$/i',
            // Audio
            '/^audio\/(mpeg|mp3|ogg|wav|webm|aac|flac|midi)$/i',
            // Video
            '/^video\/(mp4|mpeg|ogg|webm|quicktime|x-msvideo|x-flv)$/i',
            // Applications
            '/^application\/(pdf|json|xml|zip|gzip|x-tar|msword|' .
            'vnd\.openxmlformats-officedocument\.wordprocessingml\.document|' .
            'vnd\.ms-excel|vnd\.openxmlformats-officedocument\.spreadsheetml\.sheet|' .
            'vnd\.ms-powerpoint|vnd\.openxmlformats-officedocument\.presentationml\.presentation|' .
            'octet-stream|javascript|x-www-form-urlencoded)$/i',
            // Text
            '/^text\/(plain|html|css|csv|calendar|javascript)$/i',
            // Fonts
            '/^font\/(woff|woff2|ttf|otf)$/i',
        ];

        foreach ($value as $item) {
            if (!is_string($item)) {
                return false;
            }

            // Check if the MIME type matches any of the valid patterns
            $isValid = false;
            foreach ($validMimePatterns as $pattern) {
                if (preg_match($pattern, $item)) {
                    $isValid = true;
                    break;
                }
            }

            if (!$isValid) {
                return false; // ✅ This will catch 'imagxe/jpexg', 'imxage/pnxg', etc.
            }
        }

        return true;
    }

    /**
     * ✅ NEW: Returns the first invalid MIME type string found in the array.
     * Used for better error reporting.
     *
     * @param array<mixed> $value The array to check
     * @return string The first invalid MIME type found, or 'unknown' if none found
     */
    private function getFirstInvalidMimeType(array $value): string
    {
        $validMimePatterns = [
            // Images
            '/^image\/(jpeg|jpg|png|gif|webp|svg\+xml|bmp|tiff|x-icon|avif)$/i',
            // Audio
            '/^audio\/(mpeg|mp3|ogg|wav|webm|aac|flac|midi)$/i',
            // Video
            '/^video\/(mp4|mpeg|ogg|webm|quicktime|x-msvideo|x-flv)$/i',
            // Applications
            '/^application\/(pdf|json|xml|zip|gzip|x-tar|msword|' .
            'vnd\.openxmlformats-officedocument\.wordprocessingml\.document|' .
            'vnd\.ms-excel|vnd\.openxmlformats-officedocument\.spreadsheetml\.sheet|' .
            'vnd\.ms-powerpoint|vnd\.openxmlformats-officedocument\.presentationml\.presentation|' .
            'octet-stream|javascript|x-www-form-urlencoded)$/i',
            // Text
            '/^text\/(plain|html|css|csv|calendar|javascript)$/i',
            // Fonts
            '/^font\/(woff|woff2|ttf|otf)$/i',
        ];

        foreach ($value as $item) {
            if (!is_string($item)) {
                return '(non-string value: ' . get_debug_type($item) . ')';
            }

            // Check if the MIME type matches any of the valid patterns
            $isValid = false;
            foreach ($validMimePatterns as $pattern) {
                if (preg_match($pattern, $item)) {
                    $isValid = true;
                    break;
                }
            }

            if (!$isValid) {
                return $item; // ✅ Return the first invalid MIME type string
            }
        }

        return 'unknown'; // Should not reach here if isValidMimeTypeArray() is working correctly
    }
}
