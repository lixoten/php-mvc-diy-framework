<?php

declare(strict_types=1);

namespace Core\Form\Renderer;

use Core\Form\FormInterface;
use Core\Form\Field\FieldInterface;
use App\Helpers\DebugRt as Debug;

/**
 * Bootstrap 5 form renderer
 */
class BootstrapRenderer implements FormRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function renderForm(FormInterface $form, array $options = []): string
    {
        $output = $this->renderStart($form, $options);

        // Get error display style option
        $errorDisplay = $options['error_display'] ?? 'inline';


        //Debug::p($form);
        //ebug::p($options, 0);
        // If summary display is requested, render all errors at the top
        if ($errorDisplay === 'summary') {
            $allErrors = [];

            // Collect all field errors
            foreach ($form->getFields() as $field) {
                $fieldErrors = $field->getErrors();
                if (!empty($fieldErrors)) {
                    $fieldLabel = $field->getLabel();
                    foreach ($fieldErrors as $error) {
                        $allErrors[] = '<li><strong>' . htmlspecialchars($fieldLabel) . ':</strong> ' .
                                    htmlspecialchars($error) . '</li>';
                    }
                }
            }

            // Add form-level errors
            $formErrors = $form->getErrors('_form');
            foreach ($formErrors as $error) {
                $allErrors[] = '<li>' . htmlspecialchars($error) . '</li>';
            }

            // Output error summary if there are errors
            if (!empty($allErrors)) {
                $output .= '<div class="alert alert-danger mb-4" role="alert">';
                $output .= '<h5>Please correct the following errors:</h5>';
                $output .= '<ul>' . implode('', $allErrors) . '</ul>';
                $output .= '</div>';
            }

            // Set option to prevent duplicate errors
            $options['hide_inline_errors'] = true;
        } else {
            // Render just form-level errors
            $output .= $this->renderErrors($form, $options);
        }

        // Check if we have a layout configuration
        $layout = $form->getLayout();

        //Debug::p($layout, 0);

        if (!empty($layout['fieldsets'])) {
            //Debug::p($options, 0);
            // Determine column class based on layout
            $columns = $layout['columns'] ?? 1;
            $columnClass = $columns > 1 ? 'row' : '';

            $output .= '<div class="' . $columnClass . '">';

            // Render fieldsets
            foreach ($layout['fieldsets'] as $fieldsetId => $fieldset) {
                // Calculate column width for Bootstrap
                $colWidth = $columns > 1 ? 'col-md-' . (12 / $columns) : '';

                $output .= '<div class="fieldset-container ' . $colWidth . '">';
                $output .= '<fieldset id="' . $fieldsetId . '" class="mb-4">';

                // Add legend if specified
                if (!empty($fieldset['legend'])) {
                    $output .= '<legend>' . htmlspecialchars($fieldset['legend']) . '</legend>';
                }

                // Render fields in this fieldset
                foreach ($fieldset['fields'] as $fieldName) {
                    if ($form->hasField($fieldName)) {
                        $output .= $this->renderField($form->getField($fieldName), $options);
                    }
                }

                $output .= '</fieldset>';
                $output .= '</div>';
            }

            $output .= '</div>';
        } elseif (!empty($layout['sections'])) {
            // Section rendering (existing code)
            foreach ($layout['sections'] as $section) {
                $type = $section['type'] ?? '';

                switch ($type) {
                    case 'header':
                        $title = $section['title'] ?? '';
                        $output .= '<h3 class="form-section-header my-3">' . htmlspecialchars($title) . '</h3>';
                        break;

                    case 'divider':
                        $output .= '<hr class="form-divider my-4">';
                        break;

                    case 'fields':
                        $fields = $section['fields'] ?? [];
                        foreach ($fields as $fieldName) {
                            if ($form->hasField($fieldName)) {
                                $output .= $this->renderField($form->getField($fieldName), $options);
                            }
                        }
                        break;
                }
            }
        } elseif (!empty($layout['sequential'])) {
            // Sequential layout rendering
            foreach ($layout['sequential']['fields'] as $fieldName) {
                if ($form->hasField($fieldName)) {
                    $output .= $this->renderField($form->getField($fieldName), $options);
                }
            }
        } else {
            // Fallback if somehow nothing is set
            foreach ($form->getFields() as $field) {
                $output .= $this->renderField($field, $options);
            }
        }
        //Debug::p($options, 0);

        // Render submit button if requested
        if (!isset($options['no_submit_button']) || !$options['no_submit_button']) {
            $buttonText = $options['submit_text'] ?? 'Submit';
            $buttonClass = $options['submit_class'] ?? 'btn btn-primary';
            $output .= sprintf(
                '<div class="mb-3"><button type="submit" class="%s">%s</button></div>',
                htmlspecialchars($buttonClass),
                htmlspecialchars($buttonText)
            );
        }

        $output .= $this->renderEnd($form, $options);

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function renderField(FieldInterface $field, array $options = []): string
    {
        $type = $field->getType();
        $name = $field->getName();
        $id = $field->getAttribute('id') ?? $name;
        $label = $field->getLabel();
        $value = htmlspecialchars((string)$field->getValue());
        $required = $field->isRequired() ? ' required' : '';

        // Handle field errors with theme awareness
        $errorHTML = '';
        $errorClass = '';
        $ariaAttrs = '';
        $errors = $field->getErrors();

        if (!empty($errors) && empty($options['hide_inline_errors'])) {
            // Standard Bootstrap error class
            $errorClass = ' is-invalid';

            // Add accessibility attributes
            $errorId = $id . '-error';
            $ariaAttrs = ' aria-invalid="true" aria-describedby="' . $errorId . '"';

            // Create error feedback with proper accessibility
            $errorHTML = '<div id="' . $errorId . '" class="invalid-feedback" role="alert">';
            foreach ($errors as $error) {
                $errorHTML .= htmlspecialchars($error) . '<br>';
            }
            $errorHTML .= '</div>';
        }

        // Get all attributes
        $attributes = $field->getAttributes();

        // Build attribute string
        $attrString = '';
        foreach ($attributes as $attrName => $attrValue) {
            // Skip id as we handle it separately
            if ($attrName === 'id') {
                continue;
            }
            $attrString .= ' ' . $attrName . '="' . htmlspecialchars((string)$attrValue) . '"';
        }

        // Ensure $attrString starts with a space if not empty
        if (!empty($attrString)) {
            $attrString = ' ' . ltrim($attrString);
        }

        // Add default Bootstrap classes if not specified
        if (!isset($attributes['class'])) {
            $attributes['class'] = 'form-control';
        }

        $output = '<div class="mb-3">';

        // Different rendering based on field type
        switch ($type) {
            case 'checkbox':
                $checked = $field->getValue() ? ' checked' : '';
                $output .= '<div class="form-check">';
                $output .= '<input type="checkbox" class="form-check-input' . $errorClass . '"';
                $output .= ' id="' . $id . '" name="' . $name . '"';
                $output .= $checked . $required . $ariaAttrs . $attrString . '>';
                $output .= '<label class="form-check-label" for="' . $id . '">' . htmlspecialchars($label) . '</label>';
                $output .= $errorHTML;
                $output .= '</div>';
                break;

            case 'radio':
                $output .= '<label>' . htmlspecialchars($label) . '</label>';
                $options = $field->getOptions()['choices'] ?? [];
                foreach ($options as $optionValue => $optionLabel) {
                    $checked = ($field->getValue() == $optionValue) ? ' checked' : '';
                    $output .= '<div class="form-check">';
                    $output .= '<input type="radio" class="form-check-input' . $errorClass . '"';
                    $output .= ' id="' . $id . '_' . $optionValue . '"';
                    $output .= ' name="' . $name . '" value="' . htmlspecialchars((string)$optionValue) . '"';
                    $output .= $checked . $required . $ariaAttrs . $attrString . '>';
                    $output .= '<label class="form-check-label" for="' . $id . '_' . $optionValue . '">';
                    $output .= htmlspecialchars($optionLabel);
                    $output .= '</label>';
                    $output .= '</div>';
                }
                $output .= $errorHTML;
                break;

            case 'select':
                $output .= '<label class="form-label" for="' . $id . '">' . htmlspecialchars($label) . '</label>';
                $output .= '<select class="form-select' . $errorClass . '" id="' . $id . '" name="' . $name . '"';
                $output .= $required . $ariaAttrs . $attrString . '>';

                $options = $field->getOptions()['choices'] ?? [];
                $placeholder = $field->getOptions()['placeholder'] ?? null;

                if ($placeholder) {
                    $output .= '<option value="">' . htmlspecialchars($placeholder) . '</option>';
                }

                foreach ($options as $optionValue => $optionLabel) {
                    $selected = ($field->getValue() == $optionValue) ? ' selected' : '';
                    $output .= '<option value="' . htmlspecialchars((string)$optionValue) . '"';
                    $output .= $selected . '>';
                    $output .= htmlspecialchars($optionLabel) . '</option>';
                }

                $output .= '</select>';
                $output .= $errorHTML;
                break;

            case 'textarea':
                $output .= '<label class="form-label" for="' . $id . '">' . htmlspecialchars($label) . '</label>';
                $output .= '<textarea class="form-control' . $errorClass . '"';
                $output .= ' id="' . $id . '" name="' . $name . '"';
                $output .= $required . $ariaAttrs . $attrString . '>';
                $output .= $value . '</textarea>';
                $output .= $errorHTML;
                break;

            default:
                $output .= '<label class="form-label" for="' . $id . '">' . htmlspecialchars($label) . '</label>';
                $output .= '<input type="' . $type . '" class="form-control' . $errorClass . '"';
                $output .= ' id="' . $id . '" name="' . $name . '"';
                $output .= ' value="' . $value . '"' . $required . $ariaAttrs . $attrString . '>';
                $output .= $errorHTML;
                break;
        }

        $output .= '</div>';

        return $output;
    }


    /**
     * {@inheritdoc}
     */
    public function renderErrors(FormInterface $form, array $options = []): string
    {
        // Check if we're in summary mode
        $errorDisplay = $options['error_display'] ?? 'inline';

        if ($errorDisplay === 'summary') {
            // Collect ALL errors (both field and form level)
            $allErrors = [];

            // Collect field errors
            foreach ($form->getFields() as $field) {
                $fieldErrors = $field->getErrors();
                if (!empty($fieldErrors)) {
                    $fieldLabel = $field->getLabel();
                    foreach ($fieldErrors as $error) {
                        $allErrors[] = '<li><strong>' . htmlspecialchars($fieldLabel) . ':</strong> ' .
                                    htmlspecialchars($error) . '</li>';
                    }
                }
            }

            // Add form-level errors
            $formErrors = $form->getErrors('_form');
            foreach ($formErrors as $error) {
                $allErrors[] = '<li>' . htmlspecialchars($error) . '</li>';
            }

            // Only render if we have errors OR show_error_container is true
            if (!empty($allErrors) || ($options['show_error_container'] ?? false)) {
                $output = '<div class="alert alert-danger mb-4" role="alert">';
                if (!empty($allErrors)) {
                    $output .= '<h5>Please correct the following errors:</h5>';
                    $output .= '<ul>' . implode('', $allErrors) . '</ul>';
                } else {
                    $output .= '<p class="mb-0">No errors.</p>';
                }
                $output .= '</div>';
                return $output;
            }

            return '';
        }
        else {
            // Original code for inline errors - only form level
            $errors = $form->getErrors('_form');
            if (empty($errors)) {
                return '';
            }

            $output = '<div class="alert alert-danger mb-3" role="alert">';
            foreach ($errors as $error) {
                $output .= htmlspecialchars($error) . '<br>';
            }
            $output .= '</div>';

            return $output;
        }
    }


     /**
     * {@inheritdoc}
     */
    public function renderStart(FormInterface $form, array $options = []): string
    {
        $attributes = $form->getAttributes();

        // Set default method if not provided
        if (!isset($attributes['method'])) {
            $attributes['method'] = 'post';
        }

        // Apply CSS theme if specified - NO CONFIG ACCESS
        // Just use the class name directly from options
        $themeClass = $options['css_theme_class'] ?? '';

        if ($themeClass) {
            // Add theme class if defined
            if (!isset($attributes['class'])) {
                $attributes['class'] = $themeClass;
            } else {
                $attributes['class'] .= ' ' . $themeClass;
            }
        }

        // Add Bootstrap validation class
        if (!isset($attributes['class'])) {
            $attributes['class'] = 'needs-validation';
        } elseif (strpos($attributes['class'], 'needs-validation') === false) {
            $attributes['class'] .= ' needs-validation';
        }

        // Add novalidate attribute for custom validation
        $attributes['novalidate'] = '';

        // Build attribute string
        $attrString = '';
        foreach ($attributes as $name => $value) {
            if ($value === '') {
                $attrString .= ' ' . $name;
            } else {
                $attrString .= ' ' . $name . '="' . htmlspecialchars((string)$value) . '"';
            }
        }

        $output = '<form' . $attrString . '>';

        // Add CSRF token
        $token = $form->getCSRFToken();
        $output .= '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function renderEnd(FormInterface $form, array $options = []): string
    {
        return '</form>';
    }
}