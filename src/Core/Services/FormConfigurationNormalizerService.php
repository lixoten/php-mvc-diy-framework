<?php

declare(strict_types=1);

namespace Core\Services;

use Psr\Log\LoggerInterface;

/**
 * Service responsible for normalizing form configurations.
 *
 * ✅ ROLE: Transform raw, merged configuration data into a predictable, safe structure
 *
 * RESPONSIBILITIES:
 * - Apply default values for missing keys
 * - Cast types (string → bool, ensure arrays are arrays)
 * - Log WARNINGS for correctable data issues (e.g., type mismatches that can be cast)
 * - Ensure consistent structure (all expected keys exist with correct types)
 *
 * DOES NOT:
 * - Validate business rules (e.g., "security_level must be 'low'|'medium'|'high'")
 * - Throw exceptions (always returns normalized data, even if input is questionable)
 * - Check if field names exist in FieldRegistry or Entity
 * - Reject configurations (transforms them to be usable)
 *
 * PHILOSOPHY: "Make the data safe and predictable, but don't enforce business rules."
 */
class FormConfigurationNormalizerService
{
    public function __construct(
        protected LoggerInterface $logger
    ) {
    }

    /**
     * Normalizes a raw form configuration array.
     *
     * @param array<string, mixed> $config The raw, merged configuration array.
     * @return array<string, mixed> The normalized configuration array with defaults applied and types cast.
     */
    public function normalize(array $config): array
    {
        $normalized = $config;

        // --- Render Options Normalization ---
        // ✅ Apply defaults, cast types, ensure structure
        $normalized['render_options'] = $this->normalizeRenderOptions($normalized['render_options'] ?? []);

        // --- Layout Normalization ---
        // ✅ Ensure each section has required keys with correct types
        $rrr = 1;
        $normalized['form_layout'] = $this->normalizeFormLayout($normalized['form_layout'] ?? []);

        // --- Hidden Fields Normalization ---
        // ✅ Convert to array of strings, filter empty values
        $normalized['form_hidden_fields'] = $this->normalizeHiddenFields($normalized['form_hidden_fields'] ?? []);

        // --- Extra Fields Normalization ---
        // ✅ Convert to array of strings, filter empty values
        $normalized['form_extra_fields'] = $this->normalizeExtraFields($normalized['form_extra_fields'] ?? []);

        // Add any other top-level normalizations here

        return $normalized;
    }

    /**
     * Normalizes the 'render_options' section of the configuration.
     *
     * ✅ Applies defaults for all expected keys
     * ✅ Casts values to their expected types
     * ⚠️ Logs warnings for type mismatches that are correctable
     * ❌ Does NOT validate enum values (that's the validator's job)
     *
     * @param array<string, mixed> $renderOptions
     * @return array<string, mixed>
     */
    protected function normalizeRenderOptions(array $renderOptions): array
    {
        // ✅ Define default values and their types for render_options
        $defaults = [
            'from'                       => null,
            'attributes'                 => [],

            'ajax_save'                  => false,
            'auto_save'                  => false,
            'use_local_storage'          => false,

            'force_captcha'              => false,
            'security_level'             => 'low',        // Validator will check if valid
            'layout_type'                => 'sequential', // Validator will check if valid
            'error_display'              => 'inline',     // Validator will check if valid
            'html5_validation'           => false,

            'css_form_theme_class'       => '',
            'css_form_theme_file'        => '',

            'show_title_heading'         => true,
            'title_heading_level'        => 'h3',
            'title_heading_class'        => null,
            'form_heading_wrapper_class' => null,

            'submit_button_variant'      => 'primary',
            'cancel_button_variant'      => 'secondary',
            'show_error_container'       => true,
        ];

        // ✅ Merge defaults (existing values take precedence)
        $normalizedOptions = array_merge($defaults, $renderOptions);

        // ✅ Type casting for boolean flags
        $booleanKeys = [
            'ajax_save', 'auto_save', 'use_local_storage', 'force_captcha',
            'html5_validation', 'show_title_heading', 'show_error_container'
        ];

        foreach ($booleanKeys as $key) {
            if (!is_bool($normalizedOptions[$key])) {
                // ⚠️ Log warning if type needs correction
                $this->logger->warning(
                    "Normalizer: '{$key}' was not a boolean, casting to boolean.",
                    ['original_value' => $normalizedOptions[$key], 'original_type' => gettype($normalizedOptions[$key])]
                );
                $normalizedOptions[$key] = (bool) $normalizedOptions[$key];
            }
        }

        // ✅ Ensure string types for specific keys
        $stringKeys = [
            'security_level', 'layout_type', 'error_display',
            'css_form_theme_class', 'css_form_theme_file',
            'title_heading_level', 'submit_button_variant', 'cancel_button_variant'
        ];

        foreach ($stringKeys as $key) {
            if (!is_string($normalizedOptions[$key])) {
                $this->logger->warning(
                    "Normalizer: '{$key}' was not a string, casting to string.",
                    ['original_value' => $normalizedOptions[$key], 'original_type' => gettype($normalizedOptions[$key])]
                );
                $normalizedOptions[$key] = (string) $normalizedOptions[$key];
            }
        }

        // ✅ Ensure 'attributes' is an array
        if (!is_array($normalizedOptions['attributes'])) {
            $this->logger->warning(
                "Normalizer: 'attributes' was not an array, resetting to empty array.",
                [
                    'original_value' => $normalizedOptions['attributes'],
                    'original_type' => gettype($normalizedOptions['attributes'])
                ]
            );
            $normalizedOptions['attributes'] = [];
        }

        // ❌ REMOVED: Enum validation (moved to FormConfigurationValidatorService)
        // The normalizer does NOT check if 'security_level' is one of ['low', 'medium', 'high']
        // It only ensures it's a string. The validator will enforce allowed values.

        return $normalizedOptions;
    }

    /**
     * Normalizes the 'form_layout' section of the configuration.
     *
     * ✅ Ensures each layout section has 'title', 'fields', and 'divider' keys with correct types
     * ✅ Casts 'title' to string, 'fields' to array, 'divider' to bool
     * ⚠️ Logs warnings for type corrections and unexpected keys
     *
     * @param array<int, array<string, mixed>> $formLayout
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeFormLayout(array $formLayout): array
    {
        $normalizedLayout = [];
        // Define allowed keys within a layout section for identifying unexpected ones
        $allowedLayoutSectionKeys = ['title', 'fields', 'divider'];

        foreach ($formLayout as $index => $section) {
            // ✅ Ensure each section is an array before processing
            if (!is_array($section)) {
                $this->logger->warning(
                    "Normalizer: 'form_layout' section at index {$index} was not an array. Found '" .
                    get_debug_type($section) . "'. Skipping this section.",
                    ['original_value' => $section, 'section_index' => $index]
                );
                continue; // Skip invalid sections
            }

            $normalizedSection = [];

            // ⚠️ Log and drop unexpected keys within the section
            foreach (array_keys($section) as $key) {
                if (!in_array($key, $allowedLayoutSectionKeys, true)) {
                    $this->logger->warning(
                        "Normalizer: 'form_layout' section at index {$index} contains an unexpected key '{$key}'. " .
                        "This key will be ignored. Allowed keys: " . implode(', ', $allowedLayoutSectionKeys) . ".",
                        ['unexpected_key' => $key, 'section_index' => $index]
                    );
                    // Do not add unexpected keys to normalizedSection
                }
            }

            // ✅ Normalize 'title'
            $normalizedSection['title'] = (string) ($section['title'] ?? '');
            if (isset($section['title']) && !is_string($section['title'])) {
                $this->logger->warning(
                    "Normalizer: 'form_layout' section at index {$index}, 'title' was not a string. Casting to string.",
                    ['original_value' => $section['title'], 'original_type' => get_debug_type($section['title']), 'section_index' => $index]
                );
            }

            // ✅ Normalize 'fields'
            if (!isset($section['fields'])) {
                // ⚠️ Log if 'fields' key is entirely missing
                $this->logger->warning(
                    "xxxxNormalizer: 'form_layout' section at index {$index} is missing the 'fields' key. " .
                    "An empty array will be used as default.",
                    [
                        'dev_code'   => $errorCode,
                        'details' => $errors,
                    ]
                );
                $this->logger->warning(
                    "Normalizer: 'form_layout' section at index {$index} is missing the 'fields' key. " .
                    "An empty array will be used as default.",
                    ['section_index' => $index]
                );
                $normalizedSection['fields'] = [];
            } elseif (!is_array($section['fields'])) {
                // ⚠️ Log if 'fields' exists but is not an array
                $this->logger->warning(
                    "Normalizer: 'form_layout' section at index {$index}, 'fields' was not an array. " .
                    "Found '" . get_debug_type($section['fields']) . "'. An empty array will be used as default.",
                    ['original_value' => $section['fields'], 'original_type' => get_debug_type($section['fields']), 'section_index' => $index]
                );
                $normalizedSection['fields'] = [];
            } else {
                $normalizedSection['fields'] = $section['fields']; // Use original if it's already an array
            }


            // ✅ Normalize 'divider'
            $normalizedSection['divider'] = (bool) ($section['divider'] ?? false);
            if (isset($section['divider']) && !is_bool($section['divider'])) {




                $message    = "Normalizer: 'form_layout' section at index {$index}, 'divider' was not a boolean. Casting to boolean.";
                $suggestion = "Suggestion: Make sure value is a boolean '{key}' section.";
                $errorCode  = 'ERR-DEV-0231111';
                        $errors[]   = [
                            'message'    => $message,
                            'suggestion' => $suggestion,
                            'dev_code'   => $errorCode,
                        ];


                $this->logger->warning(
                    "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
                    [
                        'dev_code'   => $errorCode,
                        'details' => $errors,
                    ]
                );



                // $this->logger->warning(
                //     "Normalizer: 'form_layout' section at index {$index}, 'divider' was not a boolean. Casting to boolean.",
                //     ['original_value' => $section['divider'], 'original_type' => get_debug_type($section['divider']), 'section_index' => $index]
                // );
            }

            $normalizedLayout[] = $normalizedSection;
        }
        return $normalizedLayout;
    }



    /**
     * Normalizes the 'form_layout' section of the configuration.
     *
     * ✅ Ensures each layout section has 'title', 'fields', and 'divider' keys
     * ✅ Casts 'title' to string, 'fields' to array, 'divider' to bool
     * ⚠️ Logs warnings for type corrections
     *
     * @param array<int, array<string, mixed>> $formLayout
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeFormLayoutold(array $formLayout): array
    {
        $normalizedLayout = [];
        // Define allowed keys within a layout section for identifying unexpected ones
        $allowedLayoutSectionKeys = ['title', 'fields', 'divider'];

        foreach ($formLayout as $index => $section) {
            if (!is_array($section)) {
                $this->logger->warning(
                    "Normalizer: form_layout section at index {$index} was not an array, skipping.",
                    ['original_type' => gettype($section)]
                );
                continue; // Skip invalid sections
            }

            // ✅ Apply defaults and cast types
            $normalizedSection = [
                'title'   => (string) ($section['title'] ?? ''),
                'fields'  => is_array($section['fields'] ?? null) ? $section['fields'] : [],
                'divider' => (bool) ($section['divider'] ?? false),
            ];

            // ⚠️ Log if 'fields' was not an array
            if (isset($section['fields']) && !is_array($section['fields'])) {
                $this->logger->warning(
                    "Normalizer:
                     form_layout section at index {$index}, 'fields' was not an array, resetting to empty array.",
                    ['original_type' => gettype($section['fields'])]
                );
            }

            $normalizedLayout[] = $normalizedSection;
        }
        return $normalizedLayout;
    }

    /**
     * Normalizes the 'form_hidden_fields' section.
     *
     * ✅ Ensures it's an array of non-empty strings
     * ✅ Filters out non-string values and empty strings
     *
     * @param array<int, string> $hiddenFields
     * @return array<int, string>
     */
    protected function normalizeHiddenFields(array $hiddenFields): array
    {
        // ✅ Convert all values to strings, filter empty/invalid ones
        return array_values(array_filter(array_map('strval', $hiddenFields), fn($v) => $v !== ''));
    }

    /**
     * Normalizes the 'form_extra_fields' section.
     *
     * ✅ Ensures it's an array of non-empty strings
     * ✅ Filters out non-string values and empty strings
     *
     * @param array<int, string> $extraFields
     * @return array<int, string>
     */
    protected function normalizeExtraFields(array $extraFields): array
    {
        // ✅ Convert all values to strings, filter empty/invalid ones
        return array_values(array_filter(array_map('strval', $extraFields), fn($v) => $v !== ''));
    }
}
