<?php

declare(strict_types=1);

namespace Core\Form\Renderer;

use Core\Form\FormInterface;
use Core\Form\Field\FieldInterface;
use App\Helpers\DebugRt;

use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

/**
 * Bootstrap 5 form renderer
 */
class BootstrapFormRenderer implements FormRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function renderForm(FormInterface $form, array $options = []): string
    {
        $output = $this->renderStart($form, $options);

        // Get error display style option
        $errorDisplay = $options['error_display'] ?? 'inline';

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
        // $layout = [];
        // $layout[0]['fields'] = $options['form_fields'];
        // $layout = $options['layout'];

        if (!is_array($layout) || empty($layout)) {
            $layout[0]['fields'] = $options['form_fields'];
        }

        $layout_type = $options['layout_type'] ?? 'sequential';




        // Track if we have a captcha field
        $isCaptchaRequired = $form->isCaptchaRequired();
        //DebugRt::j('1', '', $isCaptchaRequired);
        //$captchaFieldName = 'captcha';
        //$hasCaptcha = $form->hasField($captchaFieldName);

        if ($layout_type === 'fieldsets' && !empty($layout)) {
            // Determine column class based on layout
            // $columns = $layout['columns'] ?? 1;
            $columns = count($layout);

            $columnClass = $columns > 1 ? 'row' : '';

            $output .= '<div class="' . $columnClass . '">';

            // Render fieldsets
            foreach ($layout as $fieldsetId => $fieldset) {
                // Calculate column width for Bootstrap
                $colWidth = $columns > 1 ? 'col-md-' . (12 / $columns) : '';

                $output .= '<div class="fieldset-container ' . $colWidth . '">';
                // $fldId = $fieldset['id'] ?? "fielsdset-" . htmlspecialchars("$fieldsetId");
                $fldId = $fieldset['id'] ?? "fieldset-" . htmlspecialchars("$fieldsetId");

                $output .= '<fieldset id="' . $fldId . '" class="mb-4">';

                // Add title as legend if specified
                if (!empty($fieldset['title'])) {
                    $output .= '<legend>' . htmlspecialchars($fieldset['title']) . '</legend>';
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
        } elseif ($layout_type === 'sections' && !empty($layout)) {
            foreach ($layout as $sectionId => $section) {
                $title = $section['title'] ?? '';
                $secId = $section['id'] ?? "section-" . htmlspecialchars("$sectionId");

                $output .= '<h3 class="form-section-header my-3" id="section-' . $secId . '">' .
                    htmlspecialchars($title) . '</h3>';

                if (!empty($section['divider'])) {
                    $output .= '<hr class="form-divider my-4" style="border:2px solid red;">';
                }

                $fields = $section['fields'] ?? [];
                foreach ($fields as $fieldName) {
                    if ($form->hasField($fieldName)) {
                        $output .= $this->renderField($form->getField($fieldName), $options);
                    }
                }
            }
        } elseif ($layout_type === 'sequentiaxxxl' && !empty($layout)) {
            // Sequential layout rendering
            foreach ($layout as $setId => $set) {
                foreach ($set['fields'] as $fieldName) {
                    if ($form->hasField($fieldName)) {
                        if ($fieldName !== 'captcha') {
                            $output .= $this->renderField($form->getField($fieldName), $options);
                        }
                    }
                }
            }
        } else {
            // Defaults to Sequential and Fallback if somehow nothing is set
            foreach ($layout as $setId => $set) {
                foreach ($set['fields'] as $fieldName) {
                    if ($form->hasField($fieldName)) {
                        if ($fieldName !== 'captcha') {
                            $output .= $this->renderField($form->getField($fieldName), $options);
                        }
                    }
                }
            }


            // foreach ($layout['fields'] as $fieldName) {
            //     if ($form->hasField($fieldName)) {
            //         if ($fieldName !== 'captcha') {
            //             $output .= $this->renderField($form->getField($fieldName), $options);
            //         }
            //     }
            // }
        }

        // Now render CAPTCHA in a consistent place before submit button
        if ($isCaptchaRequired) {
            $captchaFieldName = 'captcha';
            $output .= '<div class="security-wrapper mb-4">';
            $output .= '<h5 class="security-heading mb-3">Security Verification</h5>';
            $output .= $this->renderField($form->getField($captchaFieldName), $options);
            $output .= '</div>';
        }

        if (!empty($options['ajax_save'])) {
            $output .= '<div id="ajax-save-spinner" style="display:none;" class="text-info mb-2">'
                . '<span class="spinner-border spinner-border-sm"></span> Saving...'
                . '</div>';
        }

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

        $output .= $this->renderDraftNotification($options); // js-feature

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
        $errors = $field->getErrors();
        //$required = $field->isRequired() ? ' required' : ''; //fixme


        // // Handle field errors with theme awareness
        // $errorHTML = '';
        // $errorClass = '';
        // $ariaAttrs = '';
        // $errors = $field->getErrors();

        // if (!empty($errors) && empty($options['hide_inline_errors'])) {
        //     // Standard Bootstrap error class
        //     $errorClass = ' is-invalid';

        //     // Add accessibility attributes
        //     $errorId = $id . '-error';
        //     $ariaAttrs = ' aria-invalid="true" aria-describedby="' . $errorId . '"';

        //     // Create error feedback with proper accessibility
        //     $errorHTML = '<div id="' . $errorId . '" class="invalid-feedback" role="alert">';
        //     foreach ($errors as $error) {
        //         $errorHTML .= htmlspecialchars($error) . '<br>';
        //     }
        //     $errorHTML .= '</div>';
        // }

        // Get all attributes
        $attributes = $field->getAttributes();

        // Add default Bootstrap classes if not specified
        $class = 'form-control';
        if (isset($attributes['class'])) {
            $class .= ' ' . $attributes['class'];
        }
        // if (isset($attributes['style'])) {
        //     $attributes['style'] = 'form-control ' . $attributes['style'];
        // } else {
        //     $attributes['style'] = 'form-control';
        // }










        $fieldOptions = $field->getOptions();

        // Add live validation attribute if enabled in config
        if (!empty($fieldOptions['live_validation'])) {
            $attributes['data-live-validation'] = 'true';
        }












        // Build attribute string
        $attrString = '';
        // foreach ($attributes as $attrName => $attrValue) {
        //     // Skip id as we handle it separately
        //     if ($attrName === 'id') {
        //         continue;
        //     }
        //     $attrString .= ' ' . $attrName . '="' . htmlspecialchars((string)$attrValue) . '"';
        // }
        foreach ($attributes as $attrName => $attrValue) {
            // Skip id as we handle it separately
            if ($attrName === 'id') {
                continue;
            }
            if ($attrName === 'type') {
                continue;
            }
            if ($attrName === 'class') {
                continue;
            }
            if ($attrName === 'name') {
                continue;
            }
            // Boolean attributes (like required) should not have a value if true
            if (is_bool($attrValue)) {
                if ($attrValue) {
                    $attrString .= ' ' . $attrName;
                }
                continue;
            }
            if ($attrValue !== null) {
                $attrString .= ' ' . $attrName . '="' . htmlspecialchars((string)$attrValue) . '"';
            }
            // $attrString .= ' ' . $attrName . '="' . htmlspecialchars((string)$attrValue) . '"';
        }

        // Ensure $attrString starts with a space if not empty
        if (!empty($attrString)) {
            $attrString = ' ' . ltrim($attrString);
        }


        $output = '<div class="mb-3">';


        // Error handling
        $errorHTML = '';
        $errorClass = '';
        $ariaAttrs = '';
        if (!empty($errors) && empty($options['hide_inline_errors'])) {
            $errorClass = ' is-invalid';
            $errorId = $id . '-error';
            $ariaAttrs = ' aria-invalid="true" aria-describedby="' . $errorId . '"';
            $errorHTML = '<div id="' . $errorId . '" class="invalid-feedback" role="alert">';
            foreach ($errors as $error) {
                $errorHTML .= htmlspecialchars($error) . '<br>';
            }
            $errorHTML .= '</div>';
        }

        // Special handling for CAPTCHA field type
        if ($field->getType() === 'captcha') {
            $fieldOptions = $field->getOptions();
            $captchaService = $fieldOptions['captcha_service'] ?? null;

            if ($captchaService) {
                // Get theme and size from options
                $theme = $options['theme'] ?? $fieldOptions['theme'] ?? 'light';
                $size = $options['size'] ?? $fieldOptions['size'] ?? 'normal';

                // Render CAPTCHA
                $output = '<div class="mb-3">';
                $output .= '<label class="form-label" for="' . $id . '">' . htmlspecialchars($label) . '</label>';
                $output .= $captchaService->render($id, [
                    'theme' => $theme,
                    'size' => $size
                ]);

                // THIS IS THE IMPORTANT PART - SHOW ERRORS!
                if ($field->hasError()) {
                    $output .= '<div class="invalid-feedback d-block">';
                    foreach ($field->getErrors() as $error) {
                        $output .= htmlspecialchars($error);
                    }
                    $output .= '</div>';
                }

                $output .= '</div>';
                return $output;
            }
        }

        // Different rendering based on field type
        switch ($type) {
            case 'checkbox':
                $checked = $field->getValue() ? ' checked' : '';

                // Build attribute string for the checkbox input
                $checkboxAttributes = $field->getAttributes();
                $checkboxAttributes['id'] = $id;
                $checkboxAttributes['name'] = $name;

                if ($checked) {
                    $checkboxAttributes['checked'] = true;
                }

                $checkboxAttrString = '';
                foreach ($checkboxAttributes as $attrName => $attrValue) {
                    if ($attrName === 'id' || $attrName === 'name') {
                        $checkboxAttrString .= ' ' . $attrName . '="' . htmlspecialchars((string)$attrValue) . '"';
                        continue;
                    }
                    if (is_bool($attrValue)) {
                        if ($attrValue) {
                            $checkboxAttrString .= ' ' . $attrName;
                        }
                        continue;
                    }
                    $checkboxAttrString .= ' ' . $attrName . '="' . htmlspecialchars((string)$attrValue) . '"';
                }

                $output .= '<div class="form-check">';
                $output .= '<input type="checkbox" class="form-check-input' . $errorClass . '"' .
                    $checkboxAttrString . '>';
                $output .= '<label class="form-check-label" for="' . $id . '">' . htmlspecialchars($label) .
                    '</label>';
                $output .= $errorHTML;
                $output .= '</div>';
                break;

            case 'radio':
                $output .= '<label>' . htmlspecialchars($label) . '</label>';
                $optionsList = $field->getOptions()['choices'] ?? [];
                foreach ($optionsList as $optionValue => $optionLabel) {
                    $checked = ($field->getValue() == $optionValue) ? ' checked' : '';

                    // Build attribute string for each radio input
                    $radioAttributes = $field->getAttributes();
                    $radioAttributes['id'] = $id . '_' . $optionValue;
                    $radioAttributes['value'] = $optionValue;
                    $radioAttributes['name'] = $name;

                    // Add checked attribute if needed
                    if ($checked) {
                        $radioAttributes['checked'] = true;
                    }

                    // Build attribute string
                    $radioAttrString = '';
                    foreach ($radioAttributes as $attrName => $attrValue) {
                        if ($attrName === 'id' || $attrName === 'name' || $attrName === 'value') {
                            $radioAttrString .= ' ' . $attrName . '="' . htmlspecialchars((string)$attrValue) . '"';
                            continue;
                        }
                        if (is_bool($attrValue)) {
                            if ($attrValue) {
                                $radioAttrString .= ' ' . $attrName;
                            }
                            continue;
                        }
                        $radioAttrString .= ' ' . $attrName . '="' . htmlspecialchars((string)$attrValue) . '"';
                    }

                    $output .= '<div class="form-check">';
                    $output .= '<input type="radio" class="form-check-input' . $errorClass . '"'
                        . $radioAttrString . '>';
                    $output .= '<label class="form-check-label" for="' . $id . '_' . $optionValue . '">';
                    $output .= htmlspecialchars($optionLabel);
                    $output .= '</label>';
                    $output .= '</div>';
                }
                break;

            case 'select':
                $output .= '<label class="form-label" for="' . $id . '">';
                $output .= htmlspecialchars($label);
                $output .= '</label>';

                //$output .= $required . $ariaAttrs . $attrString . '>';

                $output .= '<input type="text" class="'
                    . $class . $errorClass . '" id="' . $id . '" name="' . $name . '" ';

                $output .= 'value="' . $value . '"' . $attrString . '>';

                $options = $field->getOptions()['choices'] ?? [];
                $placeholder = $field->getOptions()['placeholder'] ?? null;

                if ($placeholder) {
                    $output .= '<option value="">' . htmlspecialchars($placeholder) . '</option>';
                }

                foreach ($options as $optionValue => $optionLabel) {
                    $selected = ($field->getValue() == $optionValue) ? ' selected' : '';
                    $output .= '<option value="' . htmlspecialchars((string)$optionValue) . '"' . $selected . '>';
                    $output .= htmlspecialchars($optionLabel) . '</option>';
                }

                $output .= '</select>';
                $output .= $errorHTML;
                break;

            case 'textarea':
                $output .= '<label class="form-label" for="' . $id . '">' . htmlspecialchars($label) . '</label>';

                $output .= '<textarea class="' . $class . $errorClass . '" id="' . $id
                    . '" name="' . $name . '"' . $ariaAttrs . $attrString . '>' . $value . '</textarea>';
                $output .= $errorHTML;

                $output .= $this->renderCharCounter($field);        // js-feature
                $output .= $this->renderLiveErrorContainer($field); // js-feature
                break;

            case 'text':
                $output .= '<label class="form-label" for="' . $id . '">' . htmlspecialchars($label) . '</label>';
                $output .= '<input type="text" class="' . $class . $errorClass . '" id="' . $id . '" name="' .
                    $name . '" value="' . $value . '"' . $attrString . '>';
                $output .= $errorHTML;

                $output .= $this->renderCharCounter($field);        // js-feature
                $output .= $this->renderLiveErrorContainer($field); // js-feature

                break;

            default:
                $output .= '<label class="form-label" for="' . $id . '">' . htmlspecialchars($label) . '</label>';
                $output .= '<input type="text" class="' . $class . $errorClass . '" id="' . $id . '" name="' .
                    $name . '" value="' . $value . '"' . $attrString . '>';
                $output .= $errorHTML;

                $output .= $this->renderCharCounter($field);        // js-feature
                $output .= $this->renderLiveErrorContainer($field); // js-feature
                break;
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Render a character counter for a field if enabled in config.
     *
     * @param FieldInterface $field
     * @return string
     */
    private function renderCharCounter(FieldInterface $field): string // js-feature
    {
        // Character Counter Feature - JS
        $fieldOptions = $field->getOptions();
        if (!empty($fieldOptions['show_char_counter'])) {
            $id = $field->getAttribute('id') ?? $field->getName();
            $maxlength = $field->getAttribute('maxlength') ?? 30;
            return '<small id="' . $id . '-counter" class="form-text char-counter" style="display:none;">0 / ' .
                (int)$maxlength . '</small>';
        }
        return '';
    }

    /**
     * Render a live validation error container for a field if enabled in config.
     *
     * @param FieldInterface $field
     * @return string
     */
    private function renderLiveErrorContainer(FieldInterface $field): string // js-feature
    {
        // Live Validation Feature - JS
        $fieldOptions = $field->getOptions();
        if (!empty($fieldOptions['live_validation'])) {
            $id = $field->getAttribute('id') ?? $field->getName();
            // Optionally add an ID for JS targeting, e.g., "{$id}-error"
            return '<div class="live-error text-danger mt-1" id="' . $id . '-error"></div>';
        }
        return '';
    }

    /**
     * Render draft notification and discard button if auto-save and localStorage are enabled.
     *
     * @param array $options
     * @return string
     */
    private function renderDraftNotification(array $options): string // js-feature
    {
        // Auto Save / Draft Feature - JS
        if (!empty($options['auto_save']) && !empty($options['use_local_storage'])) {
            $output  = '<div id="draft-notification" style="display:none;" class="alert alert-warning"></div>';
            $output .= '<button type="button" id="discard-draft-btn" style="display:none;"
                class="btn btn-secondary btn-sm">Discard Draft</button>';
            return $output;
        }
        return '';
    }



    /**
     * {@inheritdoc}
     */
    public function renderErrors(FormInterface $form, array $options = []): string
    {
        // Check if we're in summary mode
        $errorDisplay = $options['error_display'] ?? 'inline';
        // DebugRt::j('1', '', $errorDisplay);
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
        } else {
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


        // Include HTML attributes from options (ADD THIS CODE)
        $htmlAttributes = $options['attributes'] ?? [];
        if (!empty($htmlAttributes)) {
            $attributes = array_merge($attributes, $htmlAttributes);
        }

        // Add auto-save and localStorage flags from render_options
        //$renderOptions = $options['render_options'] ?? [];
        if (!empty($options['auto_save'])) {
            $attributes['data-auto-save'] = 'true';
        }
        if (!empty($options['use_local_storage'])) {
            $attributes['data-use-local-storage'] = 'true';
        }

        if (!empty($options['ajax_save'])) {
            $attributes['data-ajax-save'] = 'true';
        }



        // Handle direct HTML attributes like onsubmit (ADD THIS CODE)
        $directAttributes = ['onsubmit', 'onclick', 'onchange', 'onblur', 'onfocus'];
        foreach ($directAttributes as $attr) {
            if (isset($options[$attr])) {
                $attributes[$attr] = $options[$attr];
            }
        }


        // Set default method if not provided
        if (!isset($attributes['method'])) {
            $attributes['method'] = 'post';
        }

        // Apply CSS theme if specified - NO CONFIG ACCESS
        // Just use the class name directly from options
        $themeClass = $options['css_form_theme_class'] ?? '';

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



        // novalidate attribute for custom validation
        if (!($options['html5_validation'] ?? false)) {
            $attributes['novalidate'] = '';
        }

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

        // NEW: Add form heading if specified
        if (!empty($options['form_heading'])) {
            $headingLevel = $options['form_heading_level'] ?? 'h2';
            $headingClass = $options['form_heading_class'] ?? 'form-heading mb-4';
            $output .= "<{$headingLevel} class=\"{$headingClass}\">" .
                    htmlspecialchars($options['form_heading']) .
                    "</{$headingLevel}>";
        }

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
