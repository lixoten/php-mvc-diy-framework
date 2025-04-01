<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Form helper for working with forms and form errors
 */
class FormHelper
{
    /**
     * Display error message for a field
     *
     * @param string $field Field name
     * @param array $errors Array of errors
     * @param string $class CSS class for the error message
     * @return string HTML for error message
     */
    public function showError(string $field, array $errors, string $class = 'invalid-feedback d-block'): string
    {
        if (isset($errors[$field])) {
            return '<div class="' . $class . '">' . htmlspecialchars($errors[$field]) . '</div>';
        }
        return '';
    }

    /**
     * Get old input value
     *
     * @param string $field Field name
     * @param mixed $default Default value
     * @param array|null $formData Form data array
     * @return string Field value
     */
    public function old(string $field, $default = '', ?array $formData = null): string
    {
        if ($formData && isset($formData[$field])) {
            return htmlspecialchars($formData[$field]);
        }
        return htmlspecialchars($default);
    }

    /**
     * Check if field has error
     *
     * @param string $field Field name
     * @param array $errors Array of errors
     * @param string $class CSS class to add if field has error
     * @return string CSS class string
     */
    public function isInvalid(string $field, array $errors, string $class = 'is-invalid'): string
    {
        return isset($errors[$field]) ? ' ' . $class : '';
    }

    /**
     * Generate a text input field
     *
     * @param string $name Field name
     * @param string $label Field label
     * @param array $errors Error messages
     * @param array|null $formData Form data
     * @param array $attributes Additional HTML attributes
     * @return string HTML for the input field
     */
    public function textField(
        string $name,
        string $label,
        array $errors = [],
        ?array $formData = null,
        array $attributes = []
    ): string {
        $type = $attributes['type'] ?? 'text';
        $id = $attributes['id'] ?? $name;
        $class = $attributes['class'] ?? 'form-control';
        $class .= $this->isInvalid($name, $errors);
        $value = $this->old($name, $attributes['value'] ?? '', $formData);

        // Remove processed attributes
        unset($attributes['type'], $attributes['id'], $attributes['class'], $attributes['value']);

        $html = '<div class="form-group mb-3">';
        $html .= '<label for="' . $id . '">' . htmlspecialchars($label) . '</label>';
        $html .= '<input type="' . $type . '" id="' . $id . '" name="'
                                 . $name . '" class="' . $class . '" value="' . $value . '"';

        // Add any remaining attributes
        foreach ($attributes as $attr => $val) {
            $html .= ' ' . $attr . '="' . htmlspecialchars($val) . '"';
        }

        $html .= '>';
        $html .= $this->showError($name, $errors);
        $html .= '</div>';

        return $html;
    }

    /**
     * Generate a textarea field
     *
     * @param string $name Field name
     * @param string $label Field label
     * @param array $errors Error messages
     * @param array|null $formData Form data
     * @param array $attributes Additional HTML attributes
     * @return string HTML for the textarea field
     */
    public function textareaField(
        string $name,
        string $label,
        array $errors = [],
        ?array $formData = null,
        array $attributes = []
    ): string {
        $id = $attributes['id'] ?? $name;
        $class = $attributes['class'] ?? 'form-control';
        $class .= $this->isInvalid($name, $errors);
        $value = $this->old($name, $attributes['value'] ?? '', $formData);
        $rows = $attributes['rows'] ?? 5;

        // Remove processed attributes
        unset($attributes['id'], $attributes['class'], $attributes['value'], $attributes['rows']);

        $html = '<div class="form-group mb-3">';
        $html .= '<label for="' . $id . '">' . htmlspecialchars($label) . '</label>';
        $html .= '<textarea id="' . $id . '" name="' . $name . '" class="' . $class . '" rows="' . $rows . '"';

        // Add any remaining attributes
        foreach ($attributes as $attr => $val) {
            $html .= ' ' . $attr . '="' . htmlspecialchars($val) . '"';
        }

        $html .= '>' . $value . '</textarea>';
        $html .= $this->showError($name, $errors);
        $html .= '</div>';

        return $html;
    }

    /**
     * Generate a submit button
     *
     * @param string $text Button text
     * @param array $attributes Additional HTML attributes
     * @return string HTML for the button
     */
    public function submitButton(string $text, array $attributes = []): string
    {
        $class = $attributes['class'] ?? 'btn btn-primary';

        // Remove processed attributes
        unset($attributes['class']);

        $html = '<button type="submit" class="' . $class . '"';

        // Add any remaining attributes
        foreach ($attributes as $attr => $val) {
            $html .= ' ' . $attr . '="' . htmlspecialchars($val) . '"';
        }

        $html .= '>' . htmlspecialchars($text) . '</button>';

        return $html;
    }
}
