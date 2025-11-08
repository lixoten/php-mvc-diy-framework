<?php

declare(strict_types=1);

namespace Core\Form\Renderer;

use Core\Form\FormInterface;
use Core\Form\Field\FieldInterface;
use App\Helpers\DebugRt;
use Core\Services\ClosureFormatterService;
use Core\Services\FormatterService;
use Core\Services\ThemeServiceInterface;
use Psr\Log\LoggerInterface;

use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

/**
 * Bootstrap 5 form renderer
 */
class BootstrapFormRenderer implements FormRendererInterface
{
    private ThemeServiceInterface $themeService;
    private FormatterService $formatterService;
    private LoggerInterface $logger;

    /**
     * @param FormatterService $formatterService
     */
    public function __construct(
        ThemeServiceInterface $themeService,
        FormatterService $formatterService,
        LoggerInterface $logger
    ) {
        $this->themeService = $themeService;
        $this->formatterService = $formatterService;
        $this->logger = $logger;
    }

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

        //if (!is_array($layout) || empty($layout)) {
         //   $layout[0]['fields'] = $options['form_fields'];
        //}

        $layout_type = $options['layout_type'] ?? 'sequential';




        // Track if we have a captcha field
        $isCaptchaRequired = $form->isCaptchaRequired();
        //DebugRt::j('1', '', $isCaptchaRequired);
        //$captchaFieldName = 'captcha';
        //$hasCaptcha = $form->hasField($captchaFieldName);

        foreach ($form->getFields() as $field) {
            if ($field->getType() === 'hidden') {
                $output .= $this->renderField($field, $options);
                $field->setType('display');
                $rrr = 4;
            }
        }


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
        static $autofocusSet = false;


        $type = $field->getType();
        $name = $field->getName();
        $id = $field->getAttribute('id') ?? $name;
        // $label = $field->getLabel();
        $label = htmlspecialchars($field->getLabel());  // Escape first for security


        //$value = htmlspecialchars((string)$field->getValue());
        // Get the raw value
        $rawValue = $field->getValue();
        $fieldOptions = $field->getOptions();
        $value = '';

        // $formatters = $fieldOptions['formatter'];
        $formatters = $field->getFormatters();


        //$accesskey = $fieldConfig['form']['attributes']['accesskey'] ?? null;
        $accesskey = $field->getAttribute('accesskey');

        if ($accesskey) {
            // Underline the access key in the label (case-insensitive)
            $pos = stripos($label, $accesskey);
            if ($pos !== false) {
                $label = substr_replace(
                    $label,
                    '<u>' . substr($label, $pos, 1) . '</u>',
                    $pos,
                    1
                );
            } else {
                // If not in label, append in parentheses
                $label .= ' (<u>' . strtoupper($accesskey) . '</u>)';
            }
        }


        // Important!!! - Uber_GEO, Uber_Formatter, Uber_Phone
        if (!empty($field->getErrors())) {
            // Show the original user input (escaped)
            $value = htmlspecialchars((string)$rawValue ?? '');
        } elseif (isset($formatters)) {
            //$formatters = $formatter;

            // Ensure formatters is an array for uniform processing
            if (!is_array($formatters)) {
                $formatters = [$formatters];
            }

            // Start with the raw value
            $currentValue = $rawValue;

            // Apply each formatter in sequence
            foreach ($formatters as $key => $formatter) {
                if (is_int($key) && is_string($formatter)) {
                    // Simple string: 'phone'
                    $currentValue = $this->formatterService->format($formatter, $currentValue, []);

                } elseif (is_int($key) && is_callable($formatter)) {
                    $aaa = new ClosureFormatterService();
                    // $currentValue = $this->formatterService->formatClosure($formatter, $currentValue, []);
                    $currentValue = $aaa->format($formatter, $currentValue, []);

                } elseif (is_string($key)) {
                    // Associative array: 'phone' => [options] or closure //xx
                    if (is_callable($formatter)) {
                        $currentValue = $formatter($currentValue);
                    } else {
                        $currentValue = $this->formatterService->format($key, $currentValue, $formatter ?? []);
                    }
                }
            }

            // Final value after all formatters: escape unless it's a display field with HTML output
            if (($type === 'display' || $type === 'file') && !empty($formatters)) {
                $value = (string)$currentValue;
            } else {
                $value = htmlspecialchars((string)$currentValue ?? '');
            }
        } else {
            // Fallback for fields without a formatter
            $value = htmlspecialchars((string)$rawValue ?? '');
        }


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



        // Autofocus logic: set autofocus on the first field with errors
        if (!empty($field->getErrors()) && !$autofocusSet) {
            $attributes['autofocus'] = true;
            $autofocusSet = true;
        }


        // Add ARIA attributes for accessibility (applies to all fields)
        if ($field->isRequired()) {
            $attributes['aria-required'] = 'true';
        }
        if (!empty($errors)) {
            $attributes['aria-invalid'] = 'true';
            $attributes['aria-describedby'] = $id . '-error';
        }





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


        // Build attribute string
        $attrString = '';
        foreach ($attributes as $attrName => $attrValue) {
            // Skip id as we handle it separately
            if ($attrName === 'data-show-value') {
                continue;
            }
            if ($attrName === 'minlength_message') {
                continue;
            }
            if ($attrName === 'maxlength_message') {
                continue;
            }
            if ($attrName === 'custom_minlength_message') {
                continue;
            }
            if ($attrName === 'custom_maxlength_message') {
                continue;
            }
            if ($attrName === 'custom_min_message') {
                continue;
            }
            if ($attrName === 'custom_max_message') {
                continue;
            }
            if ($attrName === 'custom_invalid_message') {
                continue;
            }
            if ($attrName === 'min_message') {
                continue;
            }
            if ($attrName === 'max_message') {
                continue;
            }
            if ($attrName === 'invalid_message') {
                continue;
            }
            if ($attrName === 'required_message') {
                continue;
            }
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
                $output .= '<label class="form-label" for="' . $id . '">' . $label . '</label>';
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
            case 'display':
                $output .= '<span class="form-label" for="' . $id . '">' . $label . '</span>';
                $output .= "<div class=\"{$class}{$errorClass}\" id=\"{$id}\" name=\"{$name}\"" .
                    "{$ariaAttrs}{$attrString}>{$value}</div>";
                //$output .= $errorHTML;
                break;
            case 'file':
                $output .= '<label class="form-label" for="' . $id . '">' . $label . '</label>';
                 $output .= '<div class="mb-2">' . $value . '</div>';

                // // Display current image if value exists
                // if (!empty($rawValue)) {
                //     // Use the image formatter if defined
                //     foreach ($formatters as $formatter) {
                //         $formatterName = is_array($formatter) ? $formatter['name'] : $formatter;
                //         $formatterOptions = is_array($formatter) ? ($formatter['options'] ?? []) : [];
                //         if ($formatterName === 'image') {
                //             $imgHtml = $this->formatterService->format('image', $rawValue, $formatterOptions);
                //             $output .= '<div class="mb-2">' . $imgHtml . '</div>';
                //             break;
                //         }
                //     }
                // }

                $output .= '<input type="file" class="' . $class . $errorClass . '" id="' . $id . '" name="' . $name . '"' . $attrString . '>';
                $output .= $errorHTML;
                break;


                // $output .= '<label class="form-label" for="' . $id . '">' . $label . '</label>';
                // $output .= '<input type="file" class="' . $class . $errorClass . '" id="' . $id . '" name="' . $name . '"' . $attrString . '>';
                // $output .= $errorHTML;
                // break;


            case 'checkbox':
                // $checked = $field->getValue() ? ' checked' : '';


                 // Build attribute string for the checkbox input
                 $checkboxAttributes = $field->getAttributes();
                 $checkboxAttributes['id'] = $id;
                 $checkboxAttributes['name'] = $name;
                 $checkboxAttributes['value'] = '1';

                // Add ARIA attributes for accessibility (applies to all fields)
                if ($field->isRequired()) {
                    $checkboxAttributes['aria-required'] = 'true';
                }

                if ($field->getValue()) {
                    $checkboxAttributes['checked'] = true;
                }

                $checkboxAttrString = '';
                foreach ($checkboxAttributes as $attrName => $attrValue) {
                    if ($attrName === 'id' || $attrName === 'name') {
                        $checkboxAttrString .= ' ' . $attrName . '="' . htmlspecialchars((string)$attrValue) . '"';
                        continue;
                    }
                    // Skip id as we handle it separately
                    if ($attrName === 'minlength_message') {
                        continue;
                    }
                    if ($attrName === 'maxlength_message') {
                        continue;
                    }
                    if ($attrName === 'min_message') {
                        continue;
                    }
                    if ($attrName === 'max_message') {
                        continue;
                    }
                    if ($attrName === 'invalid_message') {
                        continue;
                    }
                    if ($attrName === 'required_message') {
                        continue;
                    }
                    if ($attrName === 'type') {
                        continue;
                    }
                    // if ($attrName === 'class') {
                    //     continue;
                    // }
                    if (is_bool($attrValue)) {
                        if ($attrValue) {
                            $checkboxAttrString .= ' ' . $attrName;
                        }
                        continue;
                    }
                    if ($attrValue !== null) {
                        $checkboxAttrString .= ' ' . $attrName . '="' . htmlspecialchars((string)$attrValue) . '"';
                    }
                    //$checkboxAttrString .= ' ' . $attrName . '="' . htmlspecialchars((string)$attrValue) . '"';
                }

                $output .= '<div class="form-check">';
                $output .= '<input type="checkbox" class="form-check-input' . $errorClass . '"' .
                    $ariaAttrs . $checkboxAttrString . '>';
                $output .= '<label class="form-check-label" for="' . $id . '">' . $label .
                    '</label>';
                $output .= $errorHTML;
                $output .= '</div>';
                break;

            case 'checkbox_group':
                $output .= '<label class="form-label">' . $label . '</label>';
                $choices = $field->getOptions()['choices'] ?? [];
                $inline = $field->getOptions()['inline'] ?? false;
                $currentValue = is_array($field->getValue()) ? $field->getValue() : [];

                $containerClass = $inline ? 'form-check form-check-inline' : 'form-check';

                foreach ($choices as $choiceValue => $choiceLabel) {
                    $checked = in_array($choiceValue, $currentValue) ? ' checked' : '';
                    $choiceId = $id . '_' . $choiceValue;

                    $output .= '<div class="' . $containerClass . '">';
                    $output .= '<input type="checkbox" class="form-check-input' . $errorClass . '" ';
                    $output .= 'id="' . $choiceId . '" ';
                    $output .= 'name="' . $name . '[]" ';
                    $output .= 'value="' . htmlspecialchars((string)$choiceValue) . '"' . $checked . '>';
                    $output .= '<label class="form-check-label" for="' . $choiceId . '">';
                    $output .= htmlspecialchars($choiceLabel);
                    $output .= '</label>';
                    $output .= '</div>';
                }

                $output .= $errorHTML;
                break;



            case 'radio':
                $output .= '<label>' . $label . '</label>';
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
                $output .= $label;
                $output .= '</label>';

                $output .= '<select class="' . $class . $errorClass . '" id="' . $id . '" name="' . $name . '"' .
                    $ariaAttrs . $attrString . '>';

                // $options = $field->getOptions()['options'] ?? [];
                $choices = $field->getOptions()['choices'] ?? [];
                $defaultChoice = $field->getOptions()['default_choice'] ?? null;
                if ($defaultChoice) {
                    $output .= '<option value="">' . htmlspecialchars($defaultChoice) . '</option>';
                }

                // foreach ($options as $optionValue => $optionLabel) {
                //     $selected = ($field->getValue() == $optionValue) ? ' selected' : '';
                //     $output .= '<option value="' . htmlspecialchars((string)$optionValue) . '"' . $selected . '>';
                //     $output .= htmlspecialchars($optionLabel) . '</option>';
                // }
                foreach ($choices as $choiceValue => $optionLabel) {
                    $selected = ($field->getValue() == $choiceValue) ? ' selected' : '';
                    $output .= '<option value="' . htmlspecialchars((string)$choiceValue) . '"' . $selected . '>';
                    $output .= htmlspecialchars($optionLabel) . '</option>';
                }

                $output .= '</select>';
                $output .= $errorHTML;
                break;

            case 'textarea':
                $output .= "<label class=\"form-label\" for=\"{$id}\">{$label}</label>";
                $output .= "<textarea class=\"{$class}{$errorClass}\" id=\"{$id}\" name=\"{$name}\"" .
                    "{$ariaAttrs}{$attrString}>{$value}</textarea>";
                $output .= $errorHTML;
                break;
            case 'color':
                $output .= "<label class=\"form-label\" for=\"{$id}\">{$label}</label>";
                $output .= "<input type=\"color\" class=\"{$class} {$errorClass}\" id=\"{$id}\""
                        . " name=\"{$name}\" value=\"{$value}\" {$attrString}>";
                // Add datalist if provided
                if (isset($fieldOptions['datalist']) && is_array($fieldOptions['datalist'])) {
                    $datalistId = $attributes['list'] ?? $id . '-list';
                    $output .= '<datalist id="' . $datalistId . '">';
                    foreach ($fieldOptions['datalist'] as $option) {
                        $output .= '<option value="' . htmlspecialchars($option) . '">';
                    }
                    $output .= '</datalist>';
                }
                $output .= $errorHTML;
                break;

            case 'text':
                $output .= '<label class="form-label" for="' . $id . '">' . $label . '</label>';
                $output .= '<input type="text" class="' . $class . $errorClass . '" id="' . $id .
                    '" name="' . $name . '" value="' . $value . '"' . $attrString . '>';
                // Add datalist if provided
                if (isset($fieldOptions['datalist']) && is_array($fieldOptions['datalist'])) {
                    $datalistId = $attributes['list'] ?? $id . '-list';
                    $output .= '<datalist id="' . $datalistId . '">';
                    foreach ($fieldOptions['datalist'] as $option) {
                        $output .= '<option value="' . htmlspecialchars($option) . '">';
                    }
                    $output .= '</datalist>';
                }
                $output .= $errorHTML;
                break;

            case 'password':
                $output .= '<label class="form-label" for="' . $id . '">' . $label . '</label>';
                $output .= '<input type="password" class="' . $class . $errorClass . '" id="' . $id .
                    '" name="' . $name . '" value="' . $value . '"' . $attrString . '>';
                $output .= $errorHTML;
                break;

            case 'email':
                //$fieldOptions = $field->getOptions();

                $output .= '<label class="form-label" for="' . $id . '">' . $label . '</label>';
                $output .= '<input type="email" class="' . $class . $errorClass . '" id="' . $id .
                    '" name="' . $name . '" value="' . $value . '"' . $attrString . '>';
                $output .= $errorHTML;
                break;

            case 'tel':
                $output .= '<label class="form-label" for="' . $id . '">' . $label . '</label>';
                $output .= '<input type="tel" class="' . $class . $errorClass . '" id="' . $id . '" name="' .
                    $name . '" value="' . $value . '"' . $attrString . '>';
                // Add datalist if provided
                if (isset($fieldOptions['datalist']) && is_array($fieldOptions['datalist'])) {
                    $datalistId = $attributes['list'] ?? $id . '-list';
                    $output .= '<datalist id="' . $datalistId . '">';
                    foreach ($fieldOptions['datalist'] as $option) {
                        $output .= '<option value="' . htmlspecialchars($option) . '">';
                    }
                    $output .= '</datalist>';
                }
                $output .= $errorHTML;
                break;

            case 'url':
                $output .= '<label class="form-label" for="' . $id . '">' . $label . '</label>';
                $output .= '<input type="url" class="' . $class . $errorClass . '" id="' . $id .
                    '" name="' . $name . '" value="' . $value . '"' . $attrString . '>';
                $output .= $errorHTML;
                break;

            case 'search':
                $output .= '<label class="form-label" for="' . $id . '">' . $label . '</label>';
                $output .= '<input type="search" class="' . $class . $errorClass . '" id="' . $id .
                    '" name="' . $name . '" value="' . $value . '"' . $attrString . '>';
                $output .= $errorHTML;
                break;

            case 'date':
                $output .= '<label class="form-label" for="' . $id . '">' . $label . '</label>';
                $output .= '<input type="date" class="' . $class . $errorClass . '" id="' . $id . '" name="' .
                    $name . '" value="' . $value . '"' . $attrString . '>';
                $output .= $errorHTML;
                break;

            case 'datetime':
                $output .= '<label class="form-label" for="' . $id . '">' . $label . '</label>';
                $output .= '<input type="datetime-local" class="' . $class . $errorClass . '" id="' . $id . '" name="' .
                    $name . '" value="' . $value . '"' . $attrString . '>';
                $output .= $errorHTML;
                break;

            case 'month':
                $output .= '<label class="form-label" for="' . $id . '">' . $label . '</label>';
                $output .= '<input type="month" class="' . $class . $errorClass . '" id="' . $id . '" name="' .
                    $name . '" value="' . $value . '"' . $attrString . '>';
                $output .= $errorHTML;
                break;

            case 'number':
            case 'decimal':
                // $fieldOptions = $field->getOptions();
                $output .= '<label class="form-label" for="' . $id . '">' . $label . '</label>';
                $output .= '<input type="number" class="' . $class . $errorClass . '" id="' . $id .
                    '" name="' . $name . '" value="' . $value . '"' . $attrString . '>';
                // Add datalist if provided
                if (isset($fieldOptions['datalist']) && is_array($fieldOptions['datalist'])) {
                    $datalistId = $attributes['list'] ?? $id . '-list';
                    $output .= '<datalist id="' . $datalistId . '">';
                    foreach ($fieldOptions['datalist'] as $option) {
                        $output .= '<option value="' . htmlspecialchars($option) . '">';
                    }
                    $output .= '</datalist>';
                }

                if (!empty($attributes['data-show-value'])) {
                    $output .= '<output for="' . $id . '" id="' . $id . '_output">' . htmlspecialchars($value) . '</output>';
                }
                $output .= $errorHTML;
                break;

            case 'range':
                $output .= '<label class="form-label" for="' . $id . '">' . $label . '</label>';
                $output .= '<input type="range" class="' . $class . ' form-control-range-custom' . $errorClass . '" id="' . $id .
                    '" name="' . $name . '" value="' . $value . '"' . $attrString . '>';
                // Render datalist for tick marks if configured
                 if (!empty($attributes['list']) && !empty($fieldOptions['tickmarks'])) {
                    // if (isset($fieldOptions['datalist']) && is_array($fieldOptions['datalist'])) {
                    $listId = htmlspecialchars($attributes['list']);
                    $output .= '<datalist id="' . $listId . '">';
                    foreach ($fieldOptions['tickmarks'] as $tick) {
                        // $output .= '<option value="' . htmlspecialchars($tick) . '"></option>';
                        // $output .= "<option value=\"' . htmlspecialchars((string)$tick) . '\" label=\"{$tick}\" ></option>";
                        $output .= '<option value="' . htmlspecialchars((string)$tick) . '" label="' . htmlspecialchars((string)$tick) . '"></option>';
                    }
                    $output .= '</datalist>';
                }

                if (!empty($attributes['data-show-value'])) {
                    $output .= '<output for="' . $id . '" id="' . $id . '_output">' . htmlspecialchars($value) . '</output>';
                }
                $output .= $errorHTML;
                break;

            case 'week':
                $output .= '<label class="form-label" for="' . $id . '">' . $label . '</label>';
                $output .= '<input type="week" class="' . $class . $errorClass . '" id="' . $id . '" name="' .
                    $name . '" value="' . $value . '"' . $attrString . '>';
                $output .= $errorHTML;
                break;

            case 'time':
                $output .= '<label class="form-label" for="' . $id . '">' . $label . '</label>';
                $output .= '<input type="time" class="' . $class . $errorClass . '" id="' . $id . '" name="' .
                    $name . '" value="' . $value . '"' . $attrString . '>';
                $output .= $errorHTML;
                break;

            case 'hidden':
                $output .= '<input type="hidden" id="' . $id . '" name="' .
                    $name . '" value="' . $value . '"' . $attrString . '>';
                break;

            default:
                DebugRt::j('1', '', 'BOOOOMMMM');
                $output .= '<label class="form-label" for="' . $id . '">' . $label . '</label>';
                $output .= '<input type="text" class="' . $class . $errorClass . '" id="' . $id . '" name="' .
                    $name . '" value="' . $value . '"' . $attrString . '>';
                $output .= $errorHTML;
                break;
        }

            $output .= '</div>';

        return $output;
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
                // class="btn btn-secondary btn-sm">Discard Draft</button>';
            $output .= '<button type="button" id="discard-draft-btn" style="display:none;"
                class="btn btn-secondary btn-sm">Restore Data from server</button>';
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



        // Add enctype if any field is of type 'file'
        foreach ($form->getFields() as $field) {
            if ($field->getType() === 'file') {
                $attributes['enctype'] = 'multipart/form-data';
                break;
            }
        }



        // Include HTML attributes from options (ADD THIS CODE)
        $htmlAttributes = $options['attributes'] ?? [];
        if (!empty($htmlAttributes)) {
            $attributes = array_merge($attributes, $htmlAttributes);
        }

        // Add auto-save and localStorage flags from render_options
        //$renderOptions = $options['render_options'] ?? [];
        if (!empty($options['auto_save'])) { // jas-feature
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
