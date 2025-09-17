<?php

declare(strict_types=1);

namespace Core\Form\Validation;

use App\Helpers\DebugRt;
use Core\Form\Field\FieldInterface;

/**
 * Main validator service
 */
class Validator
{
    /**
     * @var ValidatorRegistry
     */
    private ValidatorRegistry $registry;

    /**
     * Constructor
     *
     * @param ValidatorRegistry $registry
     */
    public function __construct(ValidatorRegistry $registry)
    {
        $this->registry = $registry;
    }


    /**
     * Validate arbitrary data against rules (decoupled from forms).
     *
     * @param array $data
     * @param array $rules
     * @return array<string, array<string>>
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
     * Validate a field
     *
     * @param FieldInterface $field
     * @param array $context Additional context for validation
     * @return array Validation errors
     */
    public function validateField(FieldInterface $field, array $context = []): array
    {
        $errors     = [];
        $options    = $field->getOptions();
        $attributes = $options['attributes'] ?? [];
        $value = $field->getValue();

        // Required validation
        $required = $attributes['required'] ?? false;
        if ($required) {
            $error = $this->registry->validate($value, 'required', array_merge($attributes, $context));
            if ($error) {
                $errors[] = $error;
                return $errors;
            }
        }

        // Skip other validations if empty and not required
        if (($value === null || $value === '') && !$required) {
            return $errors;
        }

        // Email validation
        if ($field->getType() === 'email') {
            $error = $this->registry->validate($value, 'email', array_merge($attributes, $context));
            if ($error) {
                $errors[] = $error;
            }
        }

        // Date validation
        if ($field->getType() === 'date') {
            $error = $this->registry->validate($value, 'date', array_merge($attributes, $context));
            if ($error) {
                $errors[] = $error;
            }
        }


        // Length validation
        $minlength = $attributes['minlength'] ?? null;
        $maxlength = $attributes['maxlength'] ?? null;
        if ($minlength !== null || $maxlength !== null) {
            $lengthOptions = [
                'min' => $minlength,
                'max' => $maxlength,
            ];
            $error = $this->registry->validate($value, 'length', array_merge($lengthOptions, $context));
            if ($error) {
                $errors[] = $error;
            }
        }

        // Custom validation rule (unchanged)
        if (isset($options['validators']) && is_array($options['validators'])) {
            foreach ($options['validators'] as $validator => $validatorOptions) {
                if (is_string($validator) && $this->registry->has($validator)) {
                    $mergedOptions = is_array($validatorOptions)
                        ? array_merge($validatorOptions, $context)
                        : $context;
                    $error = $this->registry->validate($value, $validator, $mergedOptions);
                    if ($error) {
                        $errors[] = $error;
                    }
                }
            }
        }

        return $errors;
    }

    public function old_validateField(FieldInterface $field, array $context = []): array
    {
        $errors = [];
        $options = $field->getOptions();
        $value = $field->getValue();

        // Required validation
        if (!empty($options['required'])) {
            $error = $this->registry->validate($value, 'required', array_merge($options, $context));
            if ($error) {
                $errors[] = $error;
                // Skip other validations if required fails
                return $errors;
            }
        }

        // Skip other validations if empty and not required
        if (($value === null || $value === '') && empty($options['required'])) {
            return $errors;
        }

        // Email validation
        if ($field->getType() === 'email') {
            $error = $this->registry->validate($value, 'email', array_merge($options, $context));
            if ($error) {
                $errors[] = $error;
            }
        }

        // Length validation
        if (isset($options['minlength']) || isset($options['maxlength'])) {
            $lengthOptions = [
                'min' => $options['minlength'] ?? null,
                'max' => $options['maxlength'] ?? null,
            ];

            $error = $this->registry->validate($value, 'length', array_merge($lengthOptions, $context));
            if ($error) {
                $errors[] = $error;
            }
        }

        // if ($field->getName() === 'captcha') {
        //     echo "The field name is 'captcha'.";
        //     // Do something if the name is 'username'
        //     DebugRt::j('0', 'field', $field);
        //     DebugRt::j('0', 'options', $options);
        //     //exit();
        // }


        // Custom validation rule
        if (isset($options['validators']) && is_array($options['validators'])) {
            foreach ($options['validators'] as $validator => $validatorOptions) {
                if (is_string($validator) && $this->registry->has($validator)) {
                    // Merge validator options with context
                    $mergedOptions = is_array($validatorOptions)
                        ? array_merge($validatorOptions, $context)
                        : $context;

                    $error = $this->registry->validate($value, $validator, $mergedOptions);
                    if ($error) {
                        $errors[] = $error;
                    }
                }
            }
        }

        return $errors;
    }


    /**
     * Validate a field
     *
     * @param FieldInterface $field
     * @return array Validation errors
     */
    public function xxxxvalidateField(FieldInterface $field): array
    {
        $errors = [];
        $options = $field->getOptions();
        $value = $field->getValue();

        // Required validation
        if (!empty($options['required'])) {
            $error = $this->registry->validate($value, 'required', $options);
            if ($error) {
                $errors[] = $error;
                // Skip other validations if required fails
                return $errors;
            }
        }

        // Skip other validations if empty and not required
        if (($value === null || $value === '') && empty($options['required'])) {
            return $errors;
        }

        // Email validation
        if ($field->getType() === 'email') {
            $error = $this->registry->validate($value, 'email', $options);
            if ($error) {
                $errors[] = $error;
            }
        }

        // Length validation
        if (isset($options['minlength']) || isset($options['maxlength'])) {
            $lengthOptions = [
                'min' => $options['minlength'] ?? null,
                'max' => $options['maxlength'] ?? null,
            ];

            $error = $this->registry->validate($value, 'length', $lengthOptions);
            if ($error) {
                $errors[] = $error;
            }
        }

        // Custom validation rule
        if (isset($options['validators']) && is_array($options['validators'])) {
            foreach ($options['validators'] as $validator => $validatorOptions) {
                if (is_string($validator) && $this->registry->has($validator)) {
                    $error = $this->registry->validate($value, $validator, $validatorOptions);
                    if ($error) {
                        $errors[] = $error;
                        //Debug::p($errors);
                    }
                }
            }
        }

        return $errors;
    }
}
