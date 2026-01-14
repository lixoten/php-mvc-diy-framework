<?php

declare(strict_types=1);

namespace Core\Services;

use Psr\Log\LoggerInterface;
use Core\Exceptions\ConfigurationValidationException;

/**
 * Service responsible for validating a normalized form configuration array.
 *
 * ✅ ROLE: Enforce strict business rules on normalized data
 *
 * RESPONSIBILITIES:
 * - Validate enum values (security_level, layout_type, error_display, etc.)
 * - Check field existence in FieldRegistry
 * - Check field existence on Entity class (for hidden/extra fields)
 * - Verify required keys are present and non-empty
 * - Detect unexpected configuration keys (strict schema enforcement)
 * - Log ERRORS for violations
 * - Optionally throw exceptions for critical failures
 *
 * DOES NOT:
 * - Modify data (no type casting, no defaults)
 * - Handle missing keys that should have defaults (normalizer already did that)
 * - Fix correctable data issues (normalizer already did that)
 *
 * PHILOSOPHY: "The data structure is safe—now enforce the rules."
 */
class FormConfigurationValidatorService
{
    /**
     * Cache to store validated field definitions to avoid redundant validation.
     * @var array<string, mixed>
     */
    private array $validatedFieldsCache = [];

    public function __construct(
        protected LoggerInterface $logger,
        protected FieldRegistryService $fieldRegistryService,
        private EntityMetadataService $entityMetadataService,
        private FieldDefinitionSchemaValidatorService $fieldDefinitionSchemaValidatorService
    ) {
    }

    /**
     * Validates a normalized form configuration array.
     *
     * ✅ Assumes data has already been normalized (defaults applied, types cast)
     * ✅ Enforces business rules on the clean data
     * ✅ Returns validation result (does not modify config)
     *
     * @param array<string, mixed> $config The normalized configuration array.
     * @param string $pageKey The current page key (e.g., 'testy_edit').
     * @param string $entityName The current entity name (e.g., 'testy').
     * @param string $configIdentifier A string identifier for the configuration
     *                                                                source (e.g., 'Testy/Config/testy_view_edit.php').
     * @return array{isValid: bool, errors: array<string>} Returns an array with validation status and errors.
     */
    public function validate(
        array $config,
        string $pageKey,
        string $entityName,
        string $configIdentifier
    ): array {
        $errors = [];

        // Notes-: Should never be triggered due to Normalization Class
        // 📌 1. Check for unexpected top-level keys
        $allowedTopLevelKeys = ['render_options', 'form_layout', 'form_hidden_fields', 'form_extra_fields'];
        foreach (array_keys($config) as $key) {
            if (!in_array($key, $allowedTopLevelKeys, true)) {
                $message = "Config '{$configIdentifier}': Unexpected top-level configuration key found: '{$key}'.";
                $suggestion = "Suggestion: Only these area allowed. " . implode(', ', $allowedTopLevelKeys);
                $errorCode = 'ERR-DEV-TL001';
                $errors[]  = [
                    'message'    => $message,
                    'suggestion' => $suggestion,
                    'dev_code' => $errorCode,
                    'details'    => [
                        'entity' => $entityName,
                    ]
                ];
            }
        }

        // 📌 2. Check for required top-level keys
        $requiredTopLevelKeys = ['render_options', 'form_layout', 'form_hidden_fields', 'form_extra_fields'];
        foreach ($requiredTopLevelKeys as $key) {
            // Notes-: Should never be triggered due to Normalization Class
            if (!isset($config[$key])) {
                $message = "Config '{$configIdentifier}': Missing top-level key: '{$key}'.";
                $suggestion = "Suggestion: Add missing '{$key}' section.";
                $errorCode = 'ERR-DEV-TL-002';
                $errors[]  = [
                    'message'    => $message,
                    'suggestion' => $suggestion,
                    'dev_code'   => $errorCode,
                    // 'details'    => [
                    //     'entity' => $entityName,
                    // ]
                ];
            }
            // Notes-: Should never be triggered due to Normalization Class
            if (isset($config[$key]) && !is_array($config[$key])) {
                $message = "Config '{$configIdentifier}': Invalid top-level key: '{$key}'. Expected " .
                           "an array.";
                $suggestion = "Suggestion: Make sure this is an array '{$key}' section.";
                $errorCode = 'ERR-DEV-TL-003';
                $errors[]  = [
                    'message'    => $message,
                    'suggestion' => $suggestion,
                    'dev_code'   => $errorCode,
                ];
            }
        }



        // 📌 3. Resolve the entity FQCN early for hidden/extra field validation
        // $entityName = "adsADS";
        $entityFqcn = $this->getEntityFqcn($entityName);
        if (!class_exists($entityFqcn)) {
            $message    = "Config '{$configIdentifier}': Entity class '{$entityFqcn}' not found.";
            $suggestion = "Suggestion: Make sure we are using a valid Entity.";
            $errorCode  = 'ERR-DEV-TL-004';
            $errors[]   = [
                'message'    => $message,
                'suggestion' => $suggestion,
                'dev_code'   => $errorCode,
            ];

            // ✅ CRITICAL HALT: Log the error and immediately return, as further validation
            //                   dependent on the entity would be invalid.
            $this->logger->critical('Form configuration validation halted due to missing entity class: '. $message, [
                'config_identifier' => $configIdentifier,
                'pageKey' => $pageKey,
                'entityName' => $entityName,
                'dev_code' => $errorCode,
                'message' => $message,
                'suggestion' => $suggestion,
            ]);

            return ['isValid' => false, 'errors' => $errors];
        }

        // 📌 4. Validate render_options (enum checks, type verification)
        if (isset($config['render_options']) && is_array($config['render_options'])) {
            $errors = array_merge($errors, $this->validateRenderOptions($config['render_options'], $configIdentifier));
        }


        // ✅ 6. Validate form_layout (structure, field existence in FieldRegistry)
        if (isset($config['form_layout']) && is_array($config['form_layout'])) {
            $errors = array_merge(
                $errors,
                $this->validateFormLayout(
                    $config['form_layout'],
                    $pageKey,
                    $entityName,
                    $configIdentifier
                )
            );
        }

        // Important!!! this might be an issue if we ever decide that we can create a form field that does not need to
        //              exist as an entity field.
        //              Lets say we are capturing values that are then derived for a field that is then stored in DB.
        // ✅ 7a. Validate hidden_fields (must be strings AND exist on entity)
        if (isset($config['form_layout']) && is_array($config['form_layout'])) {
            foreach ($config['form_layout'] as $index => $section) {
                foreach ($section['fields'] as $key => $field) {
                    if (!is_string($field)) {
                        $message = "Config '{$configIdentifier}': Form_layout field at index {$index} is not a string.";
                        $suggestion = "Suggestion: Make sure '{$field}' in '{$key}' section is a string.";
                        $errorCode  = 'ERR-DEV-003a';
                        $errors[]   = [
                            'message'    => $message,
                            'suggestion' => $suggestion,
                            'dev_code' => $errorCode,
                        ];
                    } elseif (
                        class_exists($entityFqcn) &&
                        !$this->entityMetadataService->hasField($entityFqcn, $field)
                    ) {
                        $message    = "Config '{configIdentifier}': Form_layout field '{$field}' at index {$index} " .
                                        "not found as a property/getter in entity '{$entityFqcn}'.";
                        $suggestion = "Suggestion: Correct or remove missing '{$field}' from Form_layout Fields.";
                        $errorCode  = 'ERR-DEV-003b';
                        $errors[]   = [
                            'message'    => $message,
                            'suggestion' => $suggestion,
                            'dev_code' => $errorCode,
                        ];
                    }
                }
            }
        }

        // ✅ 7. Validate hidden_fields (must be strings AND exist on entity)
        if (isset($config['form_hidden_fields']) && is_array($config['form_hidden_fields'])) {
            foreach ($config['form_hidden_fields'] as $index => $field) {
                if (!is_string($field)) {
                    $message    = "Config '{$configIdentifier}': Form hidden field at index {$index} is not a string.";
                    $suggestion = "Suggestion: Make sure '{$field}' in '{$key}' section is a string.";
                    $errorCode  = 'ERR-DEV-004';
                    $errors[]   = [
                        'message'    => $message,
                        'suggestion' => $suggestion,
                        'dev_code' => $errorCode,
                    ];
                } elseif (class_exists($entityFqcn) && !$this->entityMetadataService->hasField($entityFqcn, $field)) {
                    $message    = "Config '{$configIdentifier}': Form hidden field '{$field}' at index {$index} not " .
                                  "found as a property/getter in entity '{$entityFqcn}'.";
                    $suggestion = "Suggestion: Correct or remove missing '{$field}' from Form Hidden Fields.";
                    $errorCode  = 'ERR-DEV-005';
                    $errors[]   = [
                        'message'    => $message,
                        'suggestion' => $suggestion,
                        'dev_code' => $errorCode,
                        'details'    => [
                            'entity' => $entityName,
                        ]
                    ];
                }
            }
        }

        // ✅ 8. Validate extra_fields (must be strings AND exist on entity)
        if (isset($config['form_extra_fields']) && is_array($config['form_extra_fields'])) {
            foreach ($config['form_extra_fields'] as $index => $field) {
                if (!is_string($field)) {
                    $message    = "Config '{$configIdentifier}': Form extra field at index {$index} is not a string.";
                    $suggestion = "Suggestion: Check '{$key}' section.";
                    $errorCode  = 'ERR-DEV-006';
                    $errors[]   = [
                        'message'    => $message,
                        'suggestion' => $suggestion,
                        'dev_code'   => $errorCode,
                    ];
                } elseif (class_exists($entityFqcn) && !$this->entityMetadataService->hasField($entityFqcn, $field)) {
                    $message = "Config '{$configIdentifier}': Form extra field '{$field}' at index {$index} not " .
                               "found as a property/getter in entity '{$entityFqcn}'.";
                    $suggestion = "Suggestion: Check '{$key}' section.";
                    $errorCode  = 'ERR-DEV-007';
                    $errors[]   = [
                        'message'    => $message,
                        'suggestion' => $suggestion,
                        'dev_code'   => $errorCode,
                    ];
                }
            }
        }

        // ✅ 9. Always log errors, but NEVER throw exceptions
        if (!empty($errors)) {
            foreach ($errors as $index => $error) {
                // $message    = $error['message'];
                // $suggestion = "Suggestion: Make sure value is a boolean '{key}' section.";
                // $errorCode  = 'ERR-DEV-0231111';
                //         $errors[]   = [
                //             'message'    => $message,
                //             'suggestion' => $suggestion,
                //             'dev_code'   => $errorCode,
                //         ];

                $this->logger->critical('Form configuration validation error detected: '. $error['message'], [
                    'config_identifier' => $configIdentifier,
                    'pageKey' => $pageKey,
                    'entityName' => $entityName,
                    'dev_code' => $error['dev_code'],
                    'suggestion' => $error['suggestion'],
                    'details' => $error['details'] ?? null,
                    // 'errors' => $errors
                ]);


            }

        }
        // >>>>> this line?
        return ['isValid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Validates the 'render_options' section.
     *
     * ✅ Enforces enum values (security_level, layout_type, error_display, etc.)
     * ✅ Verifies expected types (should already be correct after normalization)
     * ✅ Checks for unexpected keys
     * ❌ Does NOT modify data
     *
     * @param array<string, mixed> $renderOptions
     * @param string $configIdentifier A string identifier for the configuration source.
     * @return array<string> An array of error messages.
     */
    protected function validateRenderOptions(array $renderOptions, string $configIdentifier): array
    {
        $errors = [];

        // ✅ Define ALL expected keys within 'render_options' for strict validation
        $allowedRenderOptionsKeys = [
            'from', 'attributes', 'ajax_save', 'auto_save', 'use_local_storage',
            'force_captcha', 'security_level', 'layout_type', 'error_display', 'html5_validation',
            'css_form_theme_class', 'css_form_theme_file',
            'show_title_heading', 'title_heading_level', 'title_heading_class', 'form_heading_wrapper_class',
            'submit_button_variant', 'cancel_button_variant', 'show_error_container',
        ];

        // 📌 1. Check for unexpected keys within 'render_options'
        foreach (array_keys($renderOptions) as $key) {
            if (!in_array($key, $allowedRenderOptionsKeys, true)) {
                $message    = "Config '{$configIdentifier}': Unexpected key found in 'render_options': '{$key}'.";
                $suggestion = "Suggestion: Check '{$key}' section.";
                $errorCode  = 'ERR-DEV-RO-001';
                $errors[]   = [
                    'message'    => $message,
                    'suggestion' => $suggestion,
                    'dev_code' => $errorCode,
                ];
            }
        }

        // 📌 2. Validate boolean types (should already be cast by normalizer, but double-check)
        $booleanKeys = [
            'ajax_save',
            'auto_save',
            'use_local_storage',
            'force_captcha',
            'html5_validation',
            'show_title_heading',
            'show_error_container'
        ];
        foreach ($booleanKeys as $key) {
            // Notes-: Should never be triggered due to Normalization Class
            if (isset($renderOptions[$key]) && !is_bool($renderOptions[$key])) {
                $message    = "Config '{$configIdentifier}': Render option '{$key}' must be a boolean. Found: " .
                               gettype($renderOptions[$key]);
                $suggestion = "Suggestion: Check '{$key}' section.";
                $errorCode  = 'ERR-DEV-RO-002';
                $errors[]   = [
                    'message'    => $message,
                    'suggestion' => $suggestion,
                    'dev_code' => $errorCode,
                ];
            }
        }

        // 📌 3. Validate security_level enum
        $validSecurityLevels = ['low', 'medium', 'high'];
        if (
            isset($renderOptions['security_level']) &&
            !in_array($renderOptions['security_level'], $validSecurityLevels, true)
        ) {
            $message    = "Config '{$configIdentifier}': Render option 'security_level' must be one of ['" .
                           implode("', '", $validSecurityLevels) . "']. Found: '{$renderOptions['security_level']}'";
            $suggestion = "Suggestion: Check '{$key}' section.";
            $errorCode  = 'ERR-DEV-RO-003';
            $errors[]   = [
                'message'    => $message,
                'suggestion' => $suggestion,
                'dev_code'   => $errorCode,
            ];
        }

        // 📌 4. Validate layout_type enum
        $validLayoutTypes = ['sequential', 'fieldsets', 'sections'];
        if (isset($renderOptions['layout_type']) && !in_array($renderOptions['layout_type'], $validLayoutTypes, true)) {
            $message    = "Config '{$configIdentifier}': Render option 'layout_type' must be one of ['" .
                           implode("', '", $validLayoutTypes) . "']. Found: '{$renderOptions['layout_type']}'";
            $suggestion = "Suggestion: Check '{$key}' section.";
            $errorCode  = 'ERR-DEV-RO-004';
            $errors[]   = [
                'message'    => $message,
                'suggestion' => $suggestion,
                'dev_code' => $errorCode,
            ];
        }

        // 📌 5. Validate error_display enum
        $validErrorDisplays = ['inline', 'summary'];
        if (
            isset($renderOptions['error_display']) &&
            !in_array($renderOptions['error_display'], $validErrorDisplays, true)
        ) {
            $message    = "Config '{$configIdentifier}': Render option 'error_display' must be one of ['" .
                           implode("', '", $validErrorDisplays) . "']. Found: '{$renderOptions['error_display']}'";
            $suggestion = "Suggestion: Check '{$key}' section.";
            $errorCode  = 'ERR-DEV-RO-005';
            $errors[]   = [
                'message'    => $message,
                'suggestion' => $suggestion,
                'dev_code'   => $errorCode,
            ];
        }

        // 📌 6. Validate title_heading_level enum
        $validHeadingLevels = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        if (
            isset($renderOptions['title_heading_level']) &&
            !in_array($renderOptions['title_heading_level'], $validHeadingLevels, true)
        ) {
            $message    = "Config '{$configIdentifier}': Render option 'title_heading_level' must be one of " .
                   "['" . implode("', '", $validHeadingLevels) . "']. Found: '{$renderOptions['title_heading_level']}'";
            $suggestion = "Suggestion: Check '{$key}' section.";
            $errorCode  = 'ERR-DEV-RO-006';
            $errors[]   = [
                'message'    => $message,
                'suggestion' => $suggestion,
                'dev_code'   => $errorCode,
            ];
        }

        // 📌 7. Validate submit_button_variant and cancel_button_variant enums
        $validButtonVariants =
                              ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'link'];
        $buttonVariantKeys = ['submit_button_variant', 'cancel_button_variant'];
        foreach ($buttonVariantKeys as $key) {
            if (isset($renderOptions[$key])) {
                if (!is_string($renderOptions[$key])) {
                    $message    = "Config '{$configIdentifier}': Render option '{$key}' must be a string. Found: " .
                                   gettype($renderOptions[$key]);
                    $suggestion = "Suggestion: Check '{$key}' section.";
                    $errorCode  = 'ERR-DEV-RO-007a';
                    $errors[]   = [
                        'message'    => $message,
                        'suggestion' => $suggestion,
                        'dev_code'   => $errorCode,
                    ];
                } elseif (!in_array($renderOptions[$key], $validButtonVariants, true)) {
                    $message = "Config '{$configIdentifier}': Render option '{$key}' must be one of ['" .
                                implode("', '", $validButtonVariants) . "']. Found: '{$renderOptions[$key]}'";
                    $suggestion = "Suggestion: Check '{$key}' section.";
                    $errorCode  = 'ERR-DEV-RO-007b';
                    $errors[]   = [
                        'message'    => $message,
                        'suggestion' => $suggestion,
                        'dev_code' => $errorCode,
                    ];
                }
            }
        }

        return $errors;
    }


    /**
     * Validates the 'form_layout' structure.
     *
     * Rules:
     * - 'form_layout' must be an array of sections (each section is an array).
     * - 'form_layout' must not be empty (must contain at least one section).
     * - At least one section within the 'form_layout' must contain a non-empty 'fields' array.
     * - Each section:
     *     - May optionally have a 'title' key; if present, its value must be a string.
     *     - Must have a 'fields' key; its value must be an array.
     *     - May optionally have a 'divider' key; if present, its value must be a boolean.
     * - Unexpected keys within a section are considered errors.
     *
     * IMPORTANT: This method validates the LAYOUT'S STRUCTURE.
     * It does NOT validate the existence of individual field names listed in a 'fields' array.
     * That specific task is delegated to `validateReferencedFieldNames` for separation of concerns.
     *
     * @param array<int, array<string, mixed>> $formLayout The form_layout array to validate.
     * @param string $pageKey The current page key.
     * @param string $entityName The current entity name.
     * @param string $configIdentifier A string identifier for the configuration source.
     * @return array<array<string, string>> An array of error messages.
     */
    protected function validateFormLayout(
        array $formLayout,
        string $pageKey,
        string $entityName,
        string $configIdentifier
    ): array {
        $errors = [];
        $hasAnyNonEmptyFields = false;
        // ✅ These are the ONLY allowed keys at the SECTION level within 'form_layout'.
        $allowedLayoutSectionKeys = ['title', 'fields', 'divider'];

        // ✅ Rule: The overall 'form_layout' array cannot be empty.
        if (empty($formLayout)) {
            $message    = "Config '{$configIdentifier}': The 'form_layout' array cannot be empty. It must contain " .
                          "at least one section definition.";
            $suggestion = "Suggestion: Add at least one section (e.g., `['fields' => ['some_field']]`) to 'form_layout'.";
            $errorCode  = 'ERR-DEV-FL-024';
            $errors[]   = [
                'message'    => $message,
                'suggestion' => $suggestion,
                'dev_code'   => $errorCode,
            ];
            return $errors; // No further section validation possible if it's empty.
        }

        foreach ($formLayout as $index => $section) {
            // ✅ Rule: Each entry in 'form_layout' (each section) must itself be an array.
            if (!is_array($section)) {
                $message    = "Config '{$configIdentifier}': Form layout section at index {$index} must be an array. Found: " . get_debug_type($section);
                $suggestion = "Suggestion: Ensure each entry in 'form_layout' is an array defining a section (e.g., `['title' => 'Section', 'fields' => []]`).";
                $errorCode  = 'ERR-DEV-FL-025';
                $errors[]   = [
                    'message'    => $message,
                    'suggestion' => $suggestion,
                    'dev_code'   => $errorCode,
                ];
                continue; // Cannot validate contents if not an array.
            }

            // ❌ This check ensures there are no misspelled or unexpected keys at the section level,
            // like 'fieldxxs' or 'uszzzzzzzzzzzzrage'. This is a structural validation of the layout schema.
            foreach (array_keys($section) as $key) {
                if (!in_array($key, $allowedLayoutSectionKeys, true)) {
                    $message    = "Config '{$configIdentifier}': Unexpected key '{$key}' found in form layout " .
                                  "section at index {$index}. Only these keys are allowed: " .
                                  implode(', ', $allowedLayoutSectionKeys);
                    $suggestion = "Suggestion: Remove unexpected key '{$key}' or correct its name to one of the allowed keys.";
                    $errorCode  = 'ERR-DEV-FL-026';
                    $errors[]   = [
                        'message'    => $message,
                        'suggestion' => $suggestion,
                        'dev_code'   => $errorCode,
                    ];
                }
            }

            // ✅ Rule: The 'title' KEY (if present at the SECTION level) must be a string.
            // This is for the section title (e.g., 'Your Favorite'), not a field named 'title'.
            if (isset($section['title']) && !is_string($section['title'])) {
                $message    = "Config '{$configIdentifier}': Form layout section at index {$index} has a 'title' " .
                              "value that is not a string. Found: " . get_debug_type($section['title']);
                $suggestion = "Suggestion: Ensure the 'title' for this section is a string.";
                $errorCode  = 'ERR-DEV-FL-027';
                $errors[]   = [
                    'message'    => $message,
                    'suggestion' => $suggestion,
                    'dev_code'   => $errorCode,
                ];
            }

            // ✅ Rule: The 'divider' KEY (if present at the SECTION level) must be a boolean.
            if (isset($section['divider']) && !is_bool($section['divider'])) {
                $message    = "Config '{$configIdentifier}': Form layout section at index {$index}, 'divider' " .
                              "must be a boolean. Found: " . get_debug_type($section['divider']);
                $suggestion = "Suggestion: Ensure 'divider' is a boolean (true or false).";
                $errorCode  = 'ERR-DEV-FL-030';
                $errors[]   = [
                    'message'    => $message,
                    'suggestion' => $suggestion,
                    'dev_code'   => $errorCode,
                ];
            }

            // ✅ Rule: The 'fields' KEY must exist at the SECTION level and its value must be an array.
            // This catches cases like `'fields' => 'Your Favorite'` where it's a string, or if 'fields' is missing.
            if (!isset($section['fields']) || !is_array($section['fields'])) {
                $message    = "Config '{$configIdentifier}': Form layout section at index {$index} is missing " .
                              "'fields' key or 'fields' is not an array. Found: " .
                              (isset($section['fields']) ? get_debug_type($section['fields']) : 'not set');
                $suggestion = "Suggestion: Add a 'fields' array (can be empty, e.g., `[]`) to this section.";
                $errorCode  = 'ERR-DEV-FL-028';
                $errors[]   = [
                    'message'    => $message,
                    'suggestion' => $suggestion,
                    'dev_code'   => $errorCode,
                ];
            } else {
                // Track if any section has non-empty fields, which is a specific rule.
                if (!empty($section['fields'])) {
                    $hasAnyNonEmptyFields = true;
                }

                // ✅ DELEGATION: Validate individual field names exist.
                // This method (validateFormLayout) does NOT check the content of $section['fields'] itself,
                // but calls a dedicated method for that specific task, separating concerns.
                $errors = array_merge(
                    $errors,
                    $this->validateReferencedFieldNames(
                        $section['fields'],       // The array of field names (e.g., ['title', 'generic_number'])
                        $pageKey,
                        $entityName,
                        $configIdentifier,
                        "form_layout section at index {$index}" // Context for error message
                    )
                );
            }
        }

        // ✅ Rule: After checking all sections, ensure at least one section had fields defined.
        if (!$hasAnyNonEmptyFields) {
            $message    = "Config '{$configIdentifier}': The 'form_layout' does not contain any sections " .
                          "with fields defined. At least one section must have a non-empty 'fields' array.";
            $suggestion = "Suggestion: Ensure at least one 'form_layout' section has fields defined (e.g., `['fields' => ['your_field_name']]`).";
            $errorCode  = 'ERR-DEV-FL-034';
            $errors[]   = [
                'message'    => $message,
                'suggestion' => $suggestion,
                'dev_code'   => $errorCode,
            ];
        }

        return $errors;
    }



    /**
     * Validates the 'form_layout' structure.
     *
     * ✅ Checks for required keys ('title', 'fields')
     * ✅ Validates field names exist in FieldRegistry
     * ✅ Ensures 'fields' array is not empty
     * ❌ Does NOT modify data
     *
     * @param array<int, array<string, mixed>> $formLayout
     * @param string $pageKey The current page key.
     * @param string $entityName The current entity name.
     * @param string $configIdentifier A string identifier for the configuration source.
     * @return array<string> An array of error messages.
     */
    protected function validateFormLayoutOLD(
        array $formLayout,
        string $pageKey,
        string $entityName,
        string $configIdentifier
    ): array {
        $errors = [];

        // ❌ Check if the overall 'form_layout' array is empty
        if (empty($formLayout)) {
            $message    = "Config '{$configIdentifier}': The 'form_layout' array cannot be empty. It must contain " .
                          "at least one section definition.";
            $suggestion = "Suggestion: Check 'form_layout' section.";
            $errorCode  = 'ERR-DEV-024';
            $errors[]   = [
                'message'    => $message,
                'suggestion' => $suggestion,
                'dev_code'   => $errorCode,
            ];

            return $errors; // No further validation possible
        }

        foreach ($formLayout as $index => $section) {
            if (!is_array($section)) {
                $message    = "Config '{$configIdentifier}': Form layout section at index {$index} must be an array.";
                $suggestion = "Suggestion: Check '{form_layout}' section.";
                $errorCode  = 'ERR-DEV-FL-025';
                $errors[]   = [
                    'message'    => $message,
                    'suggestion' => $suggestion,
                    'dev_code'   => $errorCode,
                ];
                continue;
            }

            // // ❌ Check for unexpected keys within a layout section
            // foreach (array_keys($section) as $key) {
            //     if (!in_array($key, $allowedLayoutSectionKeys, true)) {
            //         $message    = "Config '{$configIdentifier}': Unexpected key '{$key}' found in form layout " .
            //                       "section at index {$index}. Only these are allowed: " .
            //                       implode(', ', $allowedLayoutSectionKeys);
            //         $suggestion = "Suggestion: Check against allowed.";
            //         $errorCode  = 'ERR-DEV-FL-026';
            //         $errors[]   = [
            //             'message'    => $message,
            //             'suggestion' => $suggestion,
            //             'dev_code'   => $errorCode,
            //         ];
            //     }
            // }

            // ✅ Check for required 'title' key
            if (!isset($section['title']) || !is_string($section['title'])) {
                $message    = "Config '{$configIdentifier}': Form layout section at index {$index} is missing " .
                              "a 'title' or 'title' is not a string.";
                $suggestion = "Suggestion: Check '{form_layout}' section.";
                $errorCode  = 'ERR-DEV-027';
                $errors[]   = [
                    'message'    => $message,
                    'suggestion' => $suggestion,
                    'dev_code'   => $errorCode,
                ];
            }

            // ✅ Check for required 'fields' key and that it's an array
            if (!isset($section['fields']) || !is_array($section['fields'])) {
                $message    = "Config '{$configIdentifier}': Form layout section at index {$index} is missing " .
                              "'fields' or 'fields' is not an array.";
                $suggestion = "Suggestion: Check '{form_layout}' section.";
                $errorCode  = 'ERR-DEV-028';
                $errors[]   = [
                    'message'    => $message,
                    'suggestion' => $suggestion,
                    'dev_code'   => $errorCode,
                ];
            } else {
                // ❌ Check if the 'fields' array is empty
                if (empty($section['fields'])) {
                    $message    = "Config '{$configIdentifier}': Form layout section at index {$index} has an " .
                                  "empty 'fields' array. A layout section must contain at least one field.";
                    $suggestion = "Suggestion: Check '{form_layout}' and add some fields.";
                    $errorCode  = 'ERR-DEV-029';
                    $errors[]   = [
                        'message'    => $message,
                        'suggestion' => $suggestion,
                        'dev_code'   => $errorCode,
                    ];
                }

                // ✅ Validate individual field names exist in FieldRegistry
                $errors = array_merge(
                    $errors,
                    $this->validateReferencedFieldNames(
                        $section['fields'],
                        $pageKey,
                        $entityName,
                        $configIdentifier,
                        "form_layout section at index {$index}"
                    )
                );
            }

            // ✅ Validate 'divider' is a boolean (if present)
            if (isset($section['divider']) && !is_bool($section['divider'])) {
                $message    = "Config '{$configIdentifier}': Form layout section at index {$index}, 'divider' " .
                              "must be a boolean.";
                $suggestion = "Suggestion: Check '{form_layout}' section.";
                $errorCode  = 'ERR-DEV-030';
                $errors[]   = [
                    'message'    => $message,
                    'suggestion' => $suggestion,
                    'dev_code'   => $errorCode,
                ];
            }
        }
        return $errors;
    }

    /**
     * Validates a list of referenced field names, ensuring their existence and schema validity.
     *
     * ✅ Checks if field exists in FieldRegistry
     * ✅ Validates field definition schema via FieldDefinitionSchemaValidatorService
     * ✅ Uses internal cache to avoid redundant validations
     * ❌ Does NOT modify data
     *
     * @param array<string> $fieldNames The list of field names to validate.
     * @param string $pageKey The current page key (e.g., 'testy_edit').
     * @param string $entityName The current entity name (e.g., 'testy').
     * @param string $configIdentifier A string identifying the source config file.
     * @param string $context A descriptive string indicating where the fields are referenced.
     * @param string|null $entityFqcn Optional: The FQCN of the entity (unused in current implementation,
     *                                          kept for future use).
     * @return array<string> An array of error messages specific to this field list.
     */
    protected function validateReferencedFieldNames(
        array $fieldNames,
        string $pageKey,
        string $entityName,
        string $configIdentifier,
        string $context,
        ?string $entityFqcn = null
    ): array {
        $errors = [];

        foreach ($fieldNames as $fieldIndex => $fieldName) {
            if (!is_string($fieldName)) {
                $message    = "Config '{$configIdentifier}': {$context}, field at index {$fieldIndex} is not a " .
                              "string (field name).";
                $suggestion = "Suggestion: Check '{$context}' field.";
                $errorCode  = 'ERR-DEV-031';
                $errors[]   = [
                    'message'    => $message,
                    'suggestion' => $suggestion,
                    'dev_code'   => $errorCode,
                ];
                continue;
            }

            // if ($validEntity === true) {
              // ✅ Use cache to avoid redundant validation
                $cacheKey = "{$entityName}::{$pageKey}::{$fieldName}";
                if (isset($this->validatedFieldsCache[$cacheKey])) {
                continue; // Already validated this field in this request
                }
            // }

            // ✅ Get the field definition from FieldRegistryService
            $fieldDefinition = $this->fieldRegistryService->getFieldWithFallbacks($fieldName, $pageKey, $entityName);

            if ($fieldDefinition === null) {
                // ❌ Field not found in FieldRegistry
                $message    = "Config '{$configIdentifier}': {$context}, field '{$fieldName}' at index {$fieldIndex} " .
                              "could not be found via FieldRegistryService.";
                $suggestion = "Suggestion: Fix or removed field '{$fieldName}' from '{$context}'.";
                $errorCode  = 'ERR-DEV-032';
                $errors[]   = [
                    'message'    => $message,
                    'suggestion' => $suggestion,
                    'dev_code' => $errorCode,
                ];
                continue; // Cannot validate schema if definition is missing
            }

            // ✅ Perform schema validation on the retrieved field definition
            // This will throw FieldSchemaValidationException if invalid (fast fail)
            try {
                $this->fieldDefinitionSchemaValidatorService->validateFieldDefinition(
                    $fieldDefinition,
                    $fieldName,
                    $pageKey,
                    $entityName
                );
            } catch (\Exception $e) {
                // ❌ Schema validation failed
                $message    = "Config '{$configIdentifier}': {$context}, field '{$fieldName}' at index {$fieldIndex} " .
                              "failed schema validation: {$e->getMessage()}";
                $suggestion = "Suggestion: Check '{$context}' field.";
                $errorCode  = 'ERR-DEV-033';
                $errors[]   = [
                    'message'    => $message,
                    'suggestion' => $suggestion,
                    'dev_code' => $errorCode,
                ];
                continue;
            }

            // ✅ If validation passes, cache it for this request
            $this->validatedFieldsCache[$cacheKey] = $fieldDefinition;
        }

        return $errors;
    }

    /**
     * Determines if an exception should be thrown based on the detected errors.
     *
     * ⚠️ Configurable strictness: Can be tied to environment (dev vs prod)
     *
     * @param array<string> $errors
     * @return bool
     */
    protected function shouldThrowException(array $errors): bool
    {
        // ✅ For development: Always throw on any error (strict mode)
        // ⚠️ For production: Could log errors but return normalized config instead
        return !empty($errors);
    }

    /**
     * Derives the fully qualified class name (FQCN) for an entity.
     *
     * ✅ Assumes entity class follows the pattern: App\Features\{UcfirstEntityName}\{UcfirstEntityName}
     *
     * @param string $entityName The singular, lowercase entity name (e.g., 'testy').
     * @return string The fully qualified class name.
     */
    protected function getEntityFqcn(string $entityName): string
    {
        $capitalizedEntityName = ucfirst($entityName);
        return "App\\Features\\{$capitalizedEntityName}\\{$capitalizedEntityName}";
    }
}
