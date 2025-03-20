<?php

declare(strict_types=1);

namespace Core\Form;

use Core\Form\CSRF\CSRFToken;

/**
 * Default form implementation
 */
class Form implements FormInterface
{
    private string $name;
    private array $fields = [];
    private array $data = [];
    private array $errors = [];
    private CSRFToken $csrf;
    private array $attributes = [
        'method' => 'POST',
        'action' => '',
        'enctype' => 'multipart/form-data',
    ];

    /**
     * Constructor
     *
     * @param string $name Form name
     * @param CSRFToken $csrf
     * @param array $fields Form fields
     */
    public function __construct(string $name, CSRFToken $csrf, array $fields = [])
    {
        $this->name = $name;
        $this->csrf = $csrf;
        $this->fields = $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function submit(array $data): self
    {
        $this->data = [];
        $this->errors = [];

        // Process each field
        foreach ($this->fields as $name => $field) {
            $value = $data[$name] ?? null;

            // Validate the field
            $fieldErrors = $this->validateField($name, $value, $field);

            if (!empty($fieldErrors)) {
                $this->errors[$name] = $fieldErrors;
            }

            // Store the value
            $this->data[$name] = $value;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * {@inheritdoc}
     */
    public function setData($data): self
    {
        if (is_object($data)) {
            $data = $this->objectToArray($data);
        }

        $this->data = array_intersect_key($data, $this->fields);

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function render(): string
    {
        $output = '<form';

        // Add form attributes
        foreach ($this->attributes as $name => $value) {
            $output .= ' ' . $name . '="' . htmlspecialchars($value) . '"';
        }

        $output .= '>';

        $token = $this->csrf->getToken();
        $output .= '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';

        // Add form fields
        foreach ($this->fields as $name => $field) {
            $output .= $this->renderField($name, $field);
        }

        // Add submit button
        $output .= '<button type="submit" class="btn btn-primary">Submit</button>';

        $output .= '</form>';

        return $output;
    }


    /**
     * Set a form attribute
     *
     * @param string $name Attribute name
     * @param string $value Attribute value
     * @return self
     */
    public function setAttribute(string $name, string $value): self
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Convert an object to an array
     *
     * @param object $object
     * @return array
     */
    private function objectToArray(object $object): array
    {
        $result = [];
        $reflection = new \ReflectionObject($object);

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $result[$property->getName()] = $property->getValue($object);
        }

        return $result;
    }

    /**
     * Validate a field value
     *
     * @param string $name Field name
     * @param mixed $value Field value
     * @param array $field Field definition
     * @return array Validation errors
     */
    private function validateField(string $name, $value, array $field): array
    {
        $errors = [];

        // Required validation
        if (!empty($field['required']) && empty($value)) {
            $errors[] = 'This field is required.';
        }

        // Skip other validations if empty and not required
        if (empty($value) && empty($field['required'])) {
            return $errors;
        }

        // Min length validation
        if (isset($field['minLength']) && strlen($value) < $field['minLength']) {
            $errors[] = "Minimum length is {$field['minLength']} characters.";
        }

        // Max length validation
        if (isset($field['maxLength']) && strlen($value) > $field['maxLength']) {
            $errors[] = "Maximum length is {$field['maxLength']} characters.";
        }

        // Email validation
        if (isset($field['type']) && $field['type'] === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        // Add more validations as needed

        return $errors;
    }

    /**
     * Render a field
     *
     * @param string $name Field name
     * @param array $field Field definition
     * @return string HTML
     */
    private function renderField(string $name, array $field): string
    {
        $type = $field['type'] ?? 'text';
        $label = $field['label'] ?? ucfirst($name);
        $value = $this->data[$name] ?? '';
        $attributes = $field['attributes'] ?? [];
        $hasError = isset($this->errors[$name]);

        $output = '<div class="form-group' . ($hasError ? ' has-error' : '') . '">';

        // Label
        $output .= '<label for="' . $name . '">' . htmlspecialchars($label) . '</label>';

        // Input
        if ($type === 'textarea') {
            $output .= '<textarea name="' . $name . '" id="' . $name . '"';
        } else {
            $output .= '<input type="' . $type . '"';
            $output .= ' name="' . $name . '"';
            $output .= ' id="' . $name . '"';
            $output .= ' value="' . htmlspecialchars($value) . '"';
        }

        // Add attributes
        foreach ($attributes as $attrName => $attrValue) {
            $output .= ' ' . $attrName . '="' . htmlspecialchars($attrValue) . '"';
        }

        if ($type === 'textarea') {
            $output .= '>' . htmlspecialchars($value) . '</textarea>';
        } else {
            $output .= '>';
        }

        // Error messages
        if ($hasError) {
            $output .= '<div class="invalid-feedback d-block">';
            foreach ($this->errors[$name] as $error) {
                $output .= '<div>' . htmlspecialchars($error) . '</div>';
            }
            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function addError(string $field, string $message): self
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
        return $this;
    }
}
