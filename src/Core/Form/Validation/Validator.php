<?php

declare(strict_types=1);

namespace Core\Form\Validation;

use App\Helpers\DebugRt;
use Core\Form\Field\FieldInterface;
use Core\Form\Schema\FieldSchema;
use Psr\Log\LoggerInterface;

/**
 * Main validator service
 *
 * Handles validation for form fields and arbitrary data using registered validator services.
 */
class Validator
{
    /**
     * @var ValidatorRegistry
     */
    private ValidatorRegistry $registry;

    protected FieldSchema $fieldSchema;

    /**
     * Constructor.
     *
     * @param ValidatorRegistry $registry Validator registry service
     */
    public function __construct(
        ValidatorRegistry $registry,
        FieldSchema $fieldSchema,
        protected LoggerInterface $logger
    ) {
        $this->registry    = $registry;
        $this->fieldSchema = $fieldSchema;
        $this->logger      = $logger;
    }


    /**
     * Normalize validator list to ensure consistent associative array format.
     * Converts simple string validators (e.g., ['tel']) to ['tel' => []].
     *
     * @param array<int|string, mixed> $validatorList
     * @return array<string, array<string, mixed>>
     */
    private function normalizeValidatorList(array $validatorList): array
    {
        $normalized = [];
        foreach ($validatorList as $key => $value) {
            if (is_int($key) && is_string($value)) {
                // Simple string validator: convert to ['validator_name' => []]
                $normalized[$value] = [];
            } elseif (is_string($key)) {
                // Already associative: keep as is
                $normalized[$key] = is_array($value) ? $value : [];
            }
            // Ignore invalid entries (e.g., non-string keys/values) to avoid errors
        }
        return $normalized;
    }


    /**
     * Validate arbitrary data against rules (decoupled from forms).
     *
     * @param array<string, mixed> $data   Associative array of field values
     * @param array<string, array<string, mixed>> $rules   Validation rules per field
     * @return array<string, array<int, string>>   Validation errors per field
     */
    public function validateData(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $ruleName => $options) {
                if ($ruleName === 'required' && $options) {
                    $error = $this->registry->validate($value, 'required');
                    if ($error !== null) {
                        $errors[$field][] = $error;
                    }
                } elseif ($this->registry->has($ruleName)) {
                    $error = $this->registry->validate($value, $ruleName, (array)$options);
                    if ($error !== null) {
                        $errors[$field][] = $error;
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Validate a field using its definition and context.
     *
     * @param FieldInterface $field   Field to validate
     * @param array<string, mixed> $context   Additional context for validation
     * @return array<int, string>   Validation errors for the field
     */
    public function validateField(FieldInterface $field, array $context = []): array
    {
        $errors     = [];
        // 📌 1. Get Field Options, attributes, choices and value
        // -- We look at field-->options-->validators for a `validatorList` from config file
        $validatorList = $field->getValidators();
        $validatorList = $this->normalizeValidatorList($validatorList);

        $fieldName     = $field->getName();
        $value         = $field->getValue();
        $type          = $field->getType();
        $attributes    = $field->getAttributes();
        $options       = $field->getOptions();
        $choices       = $field->getChoices();

        // Merge context into attributes
        $mergedAttributes     = array_merge($attributes, $context);

        // 📌 2. Get all validation-related attributes from $attributes. These are ValidationRules
        $attributeValidationRules = $this->extractValidationAttributes($mergedAttributes);

        // 📌 3. Get Validation Rules from the specific Validator.
        // -- These come from the config file (feature)_fields_(root, edit, view)
        $validatorValidationRules = $validatorList[$type] ?? [];
        // if (isset($choices)) {
            // $validatorValidationRules['choices'] = $choices;
        // }
        // 3. Some elements like select have choices, we need to  merge those.
        if (isset($choices)) {
        // if (isset($options['choices'])) {
            // $validatorValidationRules['choices'] = $options['choices'];
            $validatorValidationRules['choices'] = $choices;
        }

        // 📌 4. Log Warning id attributes are repeated in attributes and in validator
        // -- Also Log Warning by checking if an attribute is valid for that element typee using Form Schema
        $finalValidationRules = $this->buildValidationRules(
            $type,
            $attributeValidationRules,
            $validatorValidationRules,
            $context
        );

        // 📌 5. Required validation
        $required = $attributes['required'] ?? false;
        if ($required) {
            $error = $this->registry->validate($value, 'required', $finalValidationRules);
            if ($error) {
                // $errors[] = $error;
                $errors[] = $fieldName . '.' . $error;
                return $errors;
            }
        }

        // 📌 6. Skip other validations if empty and is not required
        if (($value === null || $value === '') && !$required) {
            return $errors;
        }

        // // fixme fuckup bigtime
        // if ($type === 'tel') {
        //     $type = 'tel';
        // }
        // if ($field->getName() === 'generic_text') {
        //     $rrr = 'tel';
        // }


        // 📌 7. We ran default Validator (always)
        // regardless if default validator is present, we Unset so we do not run it more than once.
        // default validator is always run. So if text element then we use TextValidator,
        // if type is select, then we use SelectValidator...
        unset($validatorList[$type]);
        // $rrr = 123;
        $error = $this->registry->validate($value, $type, $finalValidationRules);
        if ($error) {
            $errors[] = $fieldName . '.' . $error;
            return $errors;
        }

        // 📌 8. extra validators, Custom validation rule
        if (isset($validatorList) && is_array($validatorList)) {
            foreach ($validatorList as $validator => $validatorOptions) {
                if ($validator === 'callback') {
                    // Handle callback validator
                    if (isset($validatorOptions['callback']) && is_callable($validatorOptions['callback'])) {
                        $callback = $validatorOptions['callback'];
                        if (!$callback($value)) {
                            $errors[] = $validatorOptions['message'] ?? 'Validation failed.';
                        }
                    }
                } elseif (is_string($validator) && $this->registry->has($validator)) {
                    $finalValidationRules = $this->buildValidationRules(
                        $validator,
                        $attributeValidationRules,
                        $validatorList[$validator],
                        $context
                    );

                    $error = $this->registry->validate($value, $validator, $finalValidationRules);

                    if ($error) {
                        // $errors[] = $error;
                        $errors[] = $fieldName . '.' . $error;
                    }
                } else {
                    // ✅ Handle explicitly configured but unregistered validators
                    $message = sprintf(
                        'Validator "%s" configured for field "%s" (%s) is not registered in the ValidatorRegistry.',
                        $validator,
                        $field->getName(),
                        $field->getType()
                    );

                    if ($this->logger->isAppInDevelopment()) {
                        $this->logger->warning(
                            $message,
                            [
                                'validator' => $validator,
                                'unknown_options' => $field->getName(),
                                'dev_code' => 'ERR-DEV93'
                            ]
                        );
                    } else {
                        // In production, log a warning to avoid breaking the application but still record the issue.
                        $this->logger->warning($message, ['field' => $field->getName(), 'type' => $field->getType()]);
                    }
                }
            }
        }

        return $errors;
    }


    /**
     * Build merged rules from validation attributes, Validator and from field schema, and context.
     *
     * This helper merges:
     * 1. Field schema defaults (default_validation_rules from FieldSchema)
     * 2. field attributes in Config Form attributes
     * 3. field validator options in Config Form validator
     * 4. Context attributes (from validation context)
     *
     * @param string $type Field type (e.g., 'text', 'email', 'password')
     * @param array<string, mixed> $attributeValidationRules Extracted from validation attributes
     * @param array<string, mixed> $validatorValidationRules Extracted from validator's config field definition
     * @param array<string, mixed> $context Additional context for validation
     * @return array<string, mixed> Fully merged validation rules
     */
    protected function buildValidationRules(
        string $type,
        array $attributeValidationRules,
        array $validatorValidationRules,
        array $context
    ): array {
        // 1. Retrieve schema type data once to avoid multiple calls
        $schemaData = $this->fieldSchema->get($type);

        $this->warnDuplicatedRule($attributeValidationRules, $validatorValidationRules, static::class);


        // 2. Set/Build validation rules
        if ($schemaData !== null) {
            // 1. Get field schema defaults for validation rules
            $defaultValidationRules = $schemaData['default_validation_rules'] ?? [];

            $validationRules = array_merge($attributeValidationRules, $defaultValidationRules);
        } else {
            // Fallback if no schema exists
            $validationRules = $attributeValidationRules;
        }

        // 3. Merge custom validator options for this field type
        $mergedValidatorRules = [];
        if (isset($validatorValidationRules)) {
            //$validatorOptions = $validatorValidationRules;
            $mergedValidatorRules = array_merge($validationRules, $validatorValidationRules, $context);
            // $validatorOptions = $mergedOptions;
        }

        // 3. Extract defaults from schema
        $defaultsRules = [];
        foreach ($mergedValidatorRules as $rule => $details) {
            if (is_array($details) && array_key_exists('default', $details)) {
                $defaultsRules[$rule] = $details['default'];
            } else {
                $defaultsRules[$rule] = $details;
            }
        }

        // 4. Warn about unknown options in development
        $this->warnUnknownOptions($mergedValidatorRules, $defaultsRules, static::class);

        $filteredRules = array_filter($defaultsRules);


        // 5. Merge unique allowed lists
        if (isset($defaultsRules['ignore_allowed']) && $defaultsRules['ignore_allowed'] === true) {
            unset ($defaultsRules['allowed']);
            unset ($defaultsRules['ignore_allowed']);
        } else {
            $uniqueAllowed = $this->mergeUniqueList($filteredRules, $validationRules, 'allowed');
        }

        // 6. Merge unique forbidden lists
        if (isset($defaultsRules['ignore_forbidden']) && $defaultsRules['ignore_forbidden'] === true) {
            unset ($defaultsRules['forbidden']);
            unset ($defaultsRules['ignore_forbidden']);
        } else {
            $uniqueForbidden = $this->mergeUniqueList($filteredRules, $validationRules, 'forbidden');
        }


        //$uniqueForbidden = $this->mergeUniqueList($filteredRules, $validationRules, 'forbidden');
        //$uniqueAllowed = $this->mergeUniqueList($filteredRules, $validationRules, 'allowed');
        // $uniqueForbidden = $this->mergeUniqueList($defaults, $validatorOptions, 'blocked_domains');
        // $uniqueAllowed = $this->mergeUniqueList($defaults, $validatorOptions, 'allowed_domains');

        // 7. Build final attributes
        //$finalAttributes = array_merge($filteredRules, $finalValidatorRules);
        $finalValidationRules = $filteredRules;
        $finalValidationRules['forbidden'] = $uniqueForbidden ?? [];
        $finalValidationRules['allowed'] = $uniqueAllowed ?? [];

        return $finalValidationRules;
    }


    /**
     * Extract only validation-related attributes from the field attributes.
     *
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    protected function extractValidationAttributes(array $attributes): array
    {
        $validationKeys = [
            // 'required', notes-: not included because it is done outside of solo validators.
            'required',
            'min', 'max', 'minlength', 'maxlength', 'pattern', 'step',
            // Add more as needed for your validators
        ];

        $filtered = [];
        foreach ($validationKeys as $key) {
            if (array_key_exists($key, $attributes)) {
                $filtered[$key] = $attributes[$key];
            }
        }
        return $filtered;
    }

    /**
     * Warn if unknown options are passed to the validator.
     *
     * @param array<string, mixed> $options
     * @param array<string, mixed> $defaults
     * @param string $validatorName
     * @return void
     */
    protected function warnUnknownOptions(array $options, array $defaults, string $validatorName): void
    {
        $unknown = [];
        foreach ($options as $key => $val) {
            if (!array_key_exists($key, $defaults)) {
                if (!str_contains($key, 'message')) {
                    $unknown[] = $key;
                }
            }
        }
        if (!empty($unknown)) {
            if ($this->logger->isAppInDevelopment()) {
                $forItems = implode(', ', $unknown);
                $message = "[{$validatorName}] Unknown validator options: " . implode(', ', $unknown);

                $this->logger->warningDev(
                    $message,
                    " ---- ERR-DEV92223 - The item u are looking for SHOULD NOT BE in '_root' file. " .
                    " Look under \"validation =>\" for \"{$forItems}\" and remove it/them."
                );
            }

            error_log("[{$validatorName}] Unknown validator options: " . implode(', ', $unknown));
        }
    }

    /**
     * Warn if unknown options are passed to the validator.
     *
     * @param array<string, mixed> $options
     * @param array<string, mixed> $defaults
     * @param string $validatorName
     * @return void
     */
    protected function warnDuplicatedRule(array $attributeValidationRules, array $validatorValidationRules, string $validatorName): void
    {
        $unknown = [];
        foreach ($attributeValidationRules as $key => $val) {
            if (array_key_exists($key, $validatorValidationRules)) {
                if (!str_contains($key, 'message')) {
                    $unknown[] = $key;
                }
            }
        }
        if (!empty($unknown)) {
            if ($this->logger->isAppInDevelopment()) {
                $forItems = implode(', ', $unknown);
                $message = "[{$validatorName}] An attribute(s) was duplicated in the validator Config File: " . implode(', ', $unknown);

                $this->logger->warningDev(
                    $message,
                    " ---- ERR-DEV9222323111 - Do not repeat attribues in the Validator in '_root' file. " .
                    " Look under \"validation =>\" for \"{$forItems}\" and remove it/them."
                );
            }

            error_log("[{$validatorName}] An attribute(s) was duplicated in the validator Config File: " . implode(', ', $unknown));
        }
    }

    /**
     * Merge and deduplicate forbidden/allowed lists from defaults and options.
     *
     * @param array<string, mixed> $defaults
     * @param array<string, mixed> $options
     * @param string $key
     * @return void
     */
    protected function mergeUniqueList(array $defaults, array $options, string $key): array
    {
        $base  = $defaults[$key] ?? [];
        $extra = (isset($options[$key]['default']) && is_array($options[$key]['default'])) ? $options[$key]['default'] : [];

        return array_values(array_unique(array_merge($base, $extra)));
    }
} // 371 407
