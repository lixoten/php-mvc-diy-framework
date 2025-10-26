<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

use App\Helpers\DebugRt;
use Core\Form\Field\Field;
use Core\Form\Field\FieldInterface;
use Core\Form\Schema\FieldSchema;

/**
 * Abstract base field type
 */
abstract class AbstractFieldType implements FieldTypeInterface
{
    protected FieldSchema $fieldSchema;

    public function __construct(FieldSchema $fieldSchema)
    {
        $this->fieldSchema = $fieldSchema;
    }


    /**
     * {@inheritdoc}
     */
    public function buildField(string $name, array $options = []): FieldInterface
    {
        // Important!!! Do not confuse Options with Attributes.

        // Merge default options with provided options
        $defaultOptions     = $this->getDefaultOptions();
        $resolvedOptions    = array_merge($defaultOptions, $options);

        // 1. Custom Attributes from screen/page Config
        $customAttributes   = $resolvedOptions['attributes'] ?? [];

        $type = $this->getName();


        // 1. Get default attributes from schema
        $defaultAttributes = $this->getDefaultAttributes($type);

        // 2. Merge custom attributes from config (custom overrides defaults)
        $customAttributes = $resolvedOptions['attributes'] ?? [];


        // 2. VALIDATE: Only responsibility is validation. Validate the custom attributes
        //$type = $this->getName();
        $validatedAttributes = $this->validateAttributes($customAttributes, $type);



        $mergedAttributes = array_merge($defaultAttributes, $validatedAttributes);
        unset($resolvedOptions['attributes']); // we no longer need it, we are ready got them in customAttributes.





        //DebugRt::j('0', '', $type);
        if (isset($resolvedOptions['show_char_counter'])) {
            $validArray = [
                'text',
                'tel',
                'textarea'
            ];
            if (!in_array($type, $validArray)) {
                $message = "Invalid 'show_character_counter' for field type '{$type}'.";
                $this->logDevWarning($message . " - ERR-DEV102");
            }
        }
        if (isset($resolvedOptions['live_validation'])) {
            $validArray = ['text',
                'tel',
            'textarea',
            'date'];
            if (!in_array($type, $validArray)) {
                $message = "Invalid 'live_validation' for field type '{$type}'.";
                $this->logDevWarning($message . " - ERR-DEV102");
            }
        }




        // 3. BUILD: Only responsibility is building with validated attributes
        return $this->createFieldInstance($name, $type, $mergedAttributes, $resolvedOptions);
    }


    /**
     * Creates field instance with validated attributes + defaults
     *
     * @param string $name
     * @param string $type
     * @param array<string, mixed> $validatedAttributes
     * @param array<string, mixed> $options
     * @return FieldInterface
     */
    protected function createFieldInstance(
        string $name,
        string $type,
        array $validatedAttributes,
        array $options
    ): FieldInterface {
        // Set type if not explicitly provided
        if (!isset($validatedAttributes['type'])) {
            $validatedAttributes['type'] = $type;
        }

        // Build the field
        // Create and return field
        $newField = new Field($name, $options, $validatedAttributes);
        return $newField;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return [
            //'label' => null,
        ];
    }



    /**
     * Validates and filters an array of custom attributes against the field schema.
     *
     * @param array $customAttributes The attributes to validate and filter.
     * @return array The filtered, valid attributes.
     */
    private function validateAttributes(array $attributes, string $fieldType): array
    {
        $fieldSchema = $this->fieldSchema->get($fieldType);
        $filteredAttributes = [];

        foreach ($attributes as $attrName => $attrValue) {
            if (isset($fieldSchema[$attrName])) {
                $validAttribute = $this->validateSingleAttribute($attrName, $attrValue, $fieldSchema[$attrName]);
                if ($validAttribute !== null) {
                     $filteredAttributes[$attrName] = $validAttribute;
                }
            } else {
                $message = "Invalid attribute '{$attrName}' for field type '{$fieldType}'.";
                $this->logDevWarning($message . " - ERR-DEV101");
            }
        }

        return $filteredAttributes;
    }

    /**
     * Log a warning message in development mode
     */
    private function logDevWarning(string $message): void
    {
        if ($_ENV['APP_ENV'] === 'development') {
            trigger_error("Attribute: {$message}", E_USER_WARNING);
        }

        // Always log to system log
        error_log("Field Attribute Warning: {$message}");
    }

    /**
     * Validates a single attribute against its schema definition.
     *
     * @param string $attrName The attribute name being validated
     * @param mixed $attrValue The attribute value to validate
     * @param array $definition The schema definition for this attribute
     *
     * @return mixed The validated attribute value (unchanged) or null if validation fails
     */
    protected function validateSingleAttribute(string $attrName, $attrValue, array $definition): mixed
    {
        $schemaValue = $definition;

        // Check if the schema specifies a value validator
        if (is_array($schemaValue) && isset($schemaValue['values'])) {
            $validationRule = $schemaValue['values'];

            // 1. Check for a specific array of values (like 'autocomplete')
            if (is_array($validationRule)) {
                if (!in_array($attrValue, $validationRule)) {
                    $message = "Invalid value '{$attrValue}' for attribute '{$attrName}'.";
                    $this->logDevWarning($message . " - ERR-DEV101");
                    return null;
                }
            // 2. Check for multiple allowed types (e.g., ['int', 'float'])
            } elseif (is_array($validationRule) && (in_array('int', $validationRule) || in_array('float', $validationRule))) {
                $isValid = false;
                foreach ($validationRule as $type) {
                    if ($type === 'int' && is_int($attrValue)) {
                        $isValid = true;
                    } elseif ($type === 'float' && (is_float($attrValue) || (is_numeric($attrValue) && strpos((string)$attrValue, '.') !== false))) {
                        $isValid = true;
                    }
                }
                if (!$isValid) {
                    $message = "Invalid value '{$attrValue}' for attribute '{$attrName}'. Must be int or float.";
                    $this->logDevWarning($message . " - ERR-DEV101");
                    return null;
                }
            // 3. Check for boolean values
            } elseif ($validationRule === 'bool') {
                if (!is_bool($attrValue)) {
                    $message = "Value '{$attrValue}' for '{$attrName}' must be a boolean (true or false).";
                    $this->logDevWarning($message . " - ERR-DEV101");
                    return null;
                }
            // 4. Check for numeric values
            } elseif ($validationRule === 'numeric') {
                if (!is_numeric($attrValue)) {
                    $message = "Value '{$attrValue}' for '{$attrName}' must be a numeric value.";
                    $this->logDevWarning($message . " - ERR-DEV101");
                    return null;
                }
            // 5. Check for string values
            } elseif ($validationRule === 'string') {
                if (!is_string($attrValue)) {
                    $message = "Value '{$attrValue}' for '{$attrName}' must be a string.";
                    $this->logDevWarning($message . " - ERR-DEV101");
                    return null;
                }
            }
            // 6. You can add other rules here, like 'string', 'url', etc.
        }

        // If the attribute is valid, add it to the filtered list
        // but Only add the attribute if its value is NOT the default
        //if ($schemaValue['default'] !== $value) {
        //    $filteredAttributes[$name] = $value;
        //}
        return $attrValue;
    }


    public function getDefaultAttributes(string $type): array
    {
        $defaults = [];
        $schema = $this->fieldSchema->get($type); // Reuse your schema method
        unset($schema['val_fields']); // val_fields These are not needed till Validation
        foreach ($schema as $attribute => $details) {
            if (is_array($details) && array_key_exists('default', $details)) {
                $defaults[$attribute] = $details['default'];
            } else {
                // Attributes that are just a name (like 'id' => null)
                $defaults[$attribute] = $details;
            }
        }
        $defaults = array_filter($defaults);

        return $defaults;
    }
}

//////////////////////////////
//////////////////////////////
//////////////////////////////
//////////////////////////////
//////////////////////////////
//////////////////////////////
//////////////////////////////

    // public function getDefaultAttributesUber(string $type): array
    // {
    //     $defaults = [];
    //     $schema = $this->getValidAttributes($type); // Reuse your schema method

    //     foreach ($schema as $attribute => $details) {
    //         if (is_array($details) && array_key_exists('default', $details)) {
    //             $defaults[$attribute] = $details['default'];
    //         } else {
    //             // Attributes that are just a name (like 'id' => null)
    //             $defaults[$attribute] = $details;
    //         }
    //     }

    //     return $defaults;
    // }
// }

/*
## autocomplete.
    - Boolean Values
        - on: The default value. The browser can automatically complete the form field.
        - off: The browser's autocomplete feature is disabled for the field.
                This is generally used for sensitive information like credit card security codes.

    - Detailed Autofill Hint Values.
        - Personal Information
            - name: Full name.
            - honorific-prefix: Title (e.g., "Mr.", "Dr.").
            - given-name: First name.
            - additional-name: Middle name.
            - family-name: Last name.
            - honorific-suffix: Suffix (e.g., "Jr.", "Ph.D.").
            - nickname: A person's nickname.
            - username: A username or account name.
            - new-password: A new password for creating an account.
            - current-password: An existing password for logging in.
            - one-time-code: A one-time password used for multi-factor authentication.
            - sex: Gender identity.

            - Contact Information
                - email: An email address.
                - tel: A full telephone number.
                - url: A URL.
                - photo: A URL for a user's photo   .

            - Address Information
                - street-address: The full street address.
                - address-line1: First line of a street address.
                - address-line2: Second line of a street address.
                - address-level1: State or province.
                - address-level2: City or locality.
                - postal-code: The postal or ZIP code.
                - country: A country code.
                - country-name: A country's full name.

            - Credit Card Information
                - cc-name: Full name as it appears on a credit card.
                - cc-number: Credit card number.
                - cc-exp: Credit card expiration date.
                - cc-csc: Credit card security code.

            - Other
                - bday: A person's birthday.
                - organization: The name of a company.
                - language: A language preference.
                - timezone: A time zone.
                - impp: An instant messaging protocol endpoint.
*/
