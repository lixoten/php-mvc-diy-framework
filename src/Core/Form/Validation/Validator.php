<?php

declare(strict_types=1);

namespace Core\Form\Validation;

use App\Helpers\DebugRt;
use Core\Exceptions\ValidatorNotFoundException;
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
        // $this->logger      = $logger;
    }


    /**
     * Normalize validator list to ensure consistent associative array format.
     * E.g., ['required'] => ['required' => []]
     * E.g., ['text' => ['minlength' => 5]] remains as is.
     * This method is designed to handle the structure returned by `FieldInterface::getValidators()`.
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
            } else {
                // Log a warning for malformed rule if encountered, though ideally input is well-formed.
                $this->logWarning("Malformed validation rule encountered. Skipping: " . json_encode([$key => $value]));
            }
            // Ignore invalid entries (e.g., non-string keys/values) to avoid errors
        }
        return $normalized;
    }


    /**
     * Validate a set of data against a set of rules.
     *
     * @param array<string, mixed> $data The data to validate (e.g., form input).
     * @param array<string, array<string, mixed>> $rules The validation rules for each field.
     *                                                      Format: ['fieldName' => ['validatorName' => ['option' => 'value'] | true]]
     * @return array<string, array<string>> An associative array of field names to arrays of error messages.
     */
    public function validateData(array $data, array $rules): array // ✅ NEW / IMPLEMENTED METHOD
    {
        $allErrors = [];

        foreach ($rules as $fieldName => $fieldRules) {
            $value = $data[$fieldName] ?? null;
            $fieldErrors = [];

            // $fieldRules is already expected to be in a normalized format: validatorName => options
            // (assuming normalizeValidatorList might be called internally or rules come pre-normalized)
            // If rules come from user input, you might want to call $this->normalizeValidatorList($fieldRules);
            foreach ($fieldRules as $validatorName => $validatorOptions) {
                // Ensure validatorOptions is an array, as validatorService->validate expects array $options
                $effectiveOptions = is_array($validatorOptions) ? $validatorOptions : [];

                try {
                    $validatorService = $this->registry->get($validatorName);
                } catch (ValidatorNotFoundException $e) {
                    $this->logWarning("Validator '{$validatorName}' not found for field '{$fieldName}'. " . $e->getMessage());
                    continue; // Skip this rule if validator isn't found
                }

                $error = $validatorService->validate($value, $effectiveOptions);

                if ($error !== null) {
                    $fieldErrors[] = $error;
                }
            }

            if (!empty($fieldErrors)) {
                $allErrors[$fieldName] = $fieldErrors;
            }
        }

        return $allErrors;
    }


    /**
     * Validate arbitrary data against rules (decoupled from forms).
     *
     * @param array<string, mixed> $data   Associative array of field values
     * @param array<string, array<string, mixed>> $rules   Validation rules per field
     * @return array<string, array<int, string>>   Validation errors per field
     */
    public function oldvalidateData(array $data, array $rules): array
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
    public function oldvalidateField(FieldInterface $field, array $context = []): array
    {
        $errors     = [];
        // 1. Get Field Options, attributes and value
        // $options    = $field->getOptions();
        $validatorList = $field->getValidators();
        $validatorList = $this->normalizeValidatorList($validatorList);

        $fieldName     = $field->getName();
        $value         = $field->getValue();
        $type          = $field->getType();
        $attributes    = $field->getAttributes();
        $options       = $field->getOptions();

        // Merge context into attributes
        $mergedAttributes     = array_merge($attributes, $context);

        // 2. extract all validation-related attributes
        $validationAttributes = $this->extractValidationAttributes($mergedAttributes);

        // Ensure the field's choices (if any) are included in validator options
        $validatorOptionsForType = $validatorList[$type] ?? [];
        if (isset($options['choices'])) {
            $validatorOptionsForType['choices'] = $options['choices'];
        }
        $finalValidationAttributes = $this->buildValidationAttributes(
            $type,
            $validationAttributes,
            $validatorOptionsForType,
            $context
        );



        // 3. Required validation
        $required = $attributes['required'] ?? false;
        if ($required) {
            $error = $this->registry->validate($value, 'required', $finalValidationAttributes);
            if ($error) {
                // $errors[] = $error;
                $errors[] = $fieldName . '.' . $error;
                return $errors;
            }
        }

        // Skip other validations if empty and not required
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


        // Unset so we do not run it more than once.
        unset($validatorList[$type]);
        // $rrr = 123;
        $error = $this->registry->validate($value, $type, $finalValidationAttributes);
        if ($error) {
            $errors[] = $fieldName . '.' . $error;
            return $errors;
        }


        // Length validation
        // $minlength = $attributes['minlength'] ?? null;
        // $maxlength = $attributes['maxlength'] ?? null;
        // unset($validatorList['length']);
        // if ($minlength !== null || $maxlength !== null) {
        //     $lengthOptions = [
        //         'min' => $minlength,
        //         'max' => $maxlength,
        //     ];
        //     $error = $this->registry->validate($value, 'length', array_merge($lengthOptions, $context));
        //     if ($error) {
        //         $errors[] = $error;
        //     }
        // }



        // 5. Custom validation rule
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
                    $finalValidationAttributes = $this->buildValidationAttributes(
                        $validator,
                        $validationAttributes,
                        $validatorList[$validator],
                        $context
                    );

                    $error = $this->registry->validate($value, $validator, $finalValidationAttributes);

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
                    //$this->logger?->warning('finfo failed: ' . $e->getMessage());


                    if ($this->logger->isAppInDevelopment()) {
                        $this->logger->warningDev(
                            $message,
                            "ERR-DEV93",
                            ['validator' => $validator, 'unknown_options' => $field->getName()]
                        );
                    } else {
                        // In production, log a warning to avoid breaking the application but still record the issue.
                        // Assuming you have a logger injected, otherwise use error_log().
                        $this->logger->warning($message, ['field' => $field->getName(), 'type' => $field->getType()]);
                    }
                    // Optionally, you might add a generic error message to the field for the user
                    // $errors[] = $this->translator->get('common.error.validation_internal_error');
                }
            }
        }

        return $errors;
    }


    /**
     * Validate a single FieldInterface object.
     *
     * @param FieldInterface $field The field object to validate.
     * @param array<string, mixed> $context Additional context for validation (currently unused).
     * @return array<string, array<string>> An associative array mapping the field's name to an array of error messages.
     */
    public function validateField(FieldInterface $field, array $context = []): array // ✅ NEW / IMPLEMENTED METHOD
    {
        $fieldName = $field->getName();
        $fieldValue = $field->getValue();
        // Assume getValidators() returns an array in format: ['validatorName' => ['option' => 'value'] | true]
        $fieldValidators = $field->getValidators();

        $errors = [];

        // It's good practice to normalize the field's validators too if they aren't guaranteed to be.
        // $normalizedFieldValidators = $this->normalizeValidatorList($fieldValidators);

        foreach ($fieldValidators as $validatorName => $validatorOptions) {
            // Ensure validatorOptions is an array
            $effectiveOptions = is_array($validatorOptions) ? $validatorOptions : [];

            try {
                $validatorService = $this->registry->get($validatorName);
            } catch (ValidatorNotFoundException $e) {
                $this->logWarning("Validator '{$validatorName}' not found for field '{$fieldName}'. " . $e->getMessage());
                continue;
            }

            $error = $validatorService->validate($fieldValue, $effectiveOptions);

            if ($error !== null) {
                $errors[] = $error;
            }
        }

        if (!empty($errors)) {
            return [$fieldName => $errors];
        }

        return [];
    }
    /**
     * Logs a warning message through the configured logger.
     */
    private function logWarning(string $message): void // ✅ NEW / IMPLEMENTED METHOD
    {
        $this->logger->warning($message);
    }



    // /**
    //  * Log a warning message in development mode
    //  */
    // private function logWarning(string $message): void
    // {
    //     trigger_error("Field Registry Service Warning: {$message}", E_USER_WARNING);
    // }

    /**
     * Build merged validation attributes from field schema, validator options, and context.
     *
     * This helper merges:
     * 1. Field schema defaults (val_fields from FieldSchema)
     * 2. Custom validator options (from field->getValidators())
     * 3. Context attributes (from validation context)
     *
     * @param string $type Field type (e.g., 'text', 'email', 'password')
     * @param array<string, mixed> $validationAttributes Extracted validation attributes
     * @param array<string, mixed> Custom validators from field definition
     * @param array<string, mixed> $context Additional context for validation
     * @return array<string, mixed> Fully merged validation attributes
     */
    protected function buildValidationAttributes(
        string $type,
        array $validationAttributes,
        array $validator,
        array $context
    ): array {
        // Retrieve schema data once to avoid multiple calls
        $schemaData = $this->fieldSchema->get($type);
        //$schemaData = array_filter($schemaData);
        if ($schemaData !== null) {
            // 1. Get field schema defaults for validation
            $schemaValFields = $schemaData['val_fields'] ?? [];

            $schema = array_merge($validationAttributes, $schemaValFields);
        } else {
            // Fallback if no schema exists
            $schema = $validationAttributes;
        }

        // 2. Merge custom validator options for this field type
        $validatorOptions = [];
        if (isset($validator)) {
            $validatorOptions = $validator;
            $mergedOptions = array_merge($validationAttributes, $validatorOptions, $context);
            $validatorOptions = $mergedOptions;
        }

        // 3. Extract defaults from schema
        $defaults = [];
        foreach ($schema as $attribute => $details) {
            if (is_array($details) && array_key_exists('default', $details)) {
                $defaults[$attribute] = $details['default'];
            } else {
                $defaults[$attribute] = $details;
            }
        }

        // 4. Warn about unknown options in development
        $this->warnUnknownOptions($validatorOptions, $defaults, static::class);

        $defaults = array_filter($defaults);

        // 5. Merge unique forbidden/allowed lists
        $uniqueForbidden = $this->mergeUniqueList($defaults, $validatorOptions, 'forbidden');
        $uniqueAllowed = $this->mergeUniqueList($defaults, $validatorOptions, 'allowed');
        // $uniqueForbidden = $this->mergeUniqueList($defaults, $validatorOptions, 'blocked_domains');
        // $uniqueAllowed = $this->mergeUniqueList($defaults, $validatorOptions, 'allowed_domains');

        // 6. Build final merged attributes
        $finalAttributes = array_merge($defaults, $validatorOptions);
        $finalAttributes['forbidden'] = $uniqueForbidden;
        $finalAttributes['allowed'] = $uniqueAllowed;
        // $finalAttributes['blocked_domains'] = $uniqueForbidden;
        // $finalAttributes['allowed_domains'] = $uniqueAllowed;

        return $finalAttributes;
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
                $unknown[] = $key;
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
        $extra = (isset($options[$key]) && is_array($options[$key])) ? $options[$key] : [];

        return array_values(array_unique(array_merge($base, $extra)));
    }
} // 371
