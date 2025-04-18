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
     * Validate a field
     *
     * @param FieldInterface $field
     * @param array $context Additional context for validation
     * @return array Validation errors
     */
    public function validateField(FieldInterface $field, array $context = []): array
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
        if (isset($options['minLength']) || isset($options['maxLength'])) {
            $lengthOptions = [
                'min' => $options['minLength'] ?? null,
                'max' => $options['maxLength'] ?? null,
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
        if (isset($options['minLength']) || isset($options['maxLength'])) {
            $lengthOptions = [
                'min' => $options['minLength'] ?? null,
                'max' => $options['maxLength'] ?? null,
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
