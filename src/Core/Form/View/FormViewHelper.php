<?php

declare(strict_types=1);

namespace Core\Form\View;

use App\Helpers\DebugRt;
use Core\Form\FormInterface;
use Core\Form\Field\FieldInterface;

/**
 * Enhanced form view helper for fine-grained control over form rendering
 */
class FormViewHelper
{
    private FormInterface $form;
    private array $options = [];
    private array $fieldCustomOptions = [];

    /**
     * Create a new FormViewHelper
     *
     * @param FormInterface $form The form to wrap
     * @param array $options Global rendering options
     */
    public function __construct(FormInterface $form, array $options = [])
    {
        $this->form = $form;
        $this->options = array_merge($form->getRenderOptions(), $options);
    }

    /**
     * Set custom rendering options for a specific field
     *
     * @param string $fieldName Field name
     * @param array $options Field-specific options
     * @return self
     */
    public function setFieldOptions(string $fieldName, array $options): self
    {
        $this->fieldCustomOptions[$fieldName] = $options;
        return $this;
    }

    /**
     * Form start tag with CSRF token
     *
     * @param array $attributes Additional attributes
     * @return string HTML
     */
    public function start(array $attributes = []): string
    {
        // Get theme class from form options to avoid duplication
        $themeClass = $this->form->getRenderOptions()['css_form_theme_class'] ?? '';

        // Make sure we're not adding duplicate theme classes
        if ($themeClass && isset($attributes['class'])) {
            // Only add the theme class if it's not already in the attributes
            if (strpos($attributes['class'], $themeClass) === false) {
                $attributes['class'] .= ' ' . $themeClass;
            }
        }

        return $this->openForm($attributes);
    }

    /**
     * Render form opening tag
     *
     * @param array $attributes Additional HTML attributes
     * @return string HTML
     */
    public function openForm(array $attributes = []): string
    {
        $renderer = $this->form->getRenderer();

        // Merge options for form start
        $mergedOptions = array_merge($this->options, ['attributes' => $attributes]);

        return $renderer->renderStart($this->form, $mergedOptions);
    }

    /**
     * Form end tag
     *
     * @return string HTML
     */
    public function end(): string
    {
        return $this->closeForm();
    }

    /**
     * Render form closing tag
     *
     * @return string HTML
     */
    public function closeForm(): string
    {
        return $this->form->getRenderer()->renderEnd($this->form, $this->options);
    }

    /**
     * Check if form has a field
     *
     * @param string $fieldName Field name
     * @return bool
     */
    public function has(string $fieldName): bool
    {
        return $this->form->hasField($fieldName);
    }

    /**
     * Render a complete form row (label + field + errors)
     *
     * @param string $fieldName Field name
     * @param array $options Rendering options
     * @return string HTML
     */
    public function row(string $fieldName, array $options = []): string
    {
        if (!$this->form->hasField($fieldName)) {
            return '';
        }

        $field = $this->form->getField($fieldName);

        // Merge options: base options < field custom options < method options
        $fieldOptions = array_merge(
            $this->options,
            $this->fieldCustomOptions[$fieldName] ?? [],
            $options,
            ['render_mode' => 'row']
        );

        // If we're using error summary, hide inline errors
        if (isset($this->options['hide_inline_errors']) && $this->options['hide_inline_errors']) {
            $fieldOptions['hide_inline_errors'] = true;
        }

        return $this->form->getRenderer()->renderField($field, $fieldOptions);
    }

    /**
     * Render just the field input (no label or errors)
     *
     * @param string $fieldName Field name
     * @param array $options Rendering options
     * @return string HTML
     */
    public function input(string $fieldName, array $options = []): string
    {
        if (!$this->form->hasField($fieldName)) {
            return '';
        }

        $field = $this->form->getField($fieldName);

        // Merge options for input-only rendering
        $fieldOptions = array_merge(
            $this->options,
            $this->fieldCustomOptions[$fieldName] ?? [],
            $options,
            ['render_mode' => 'input_only']
        );

        return $this->form->getRenderer()->renderField($field, $fieldOptions);
    }

    /**
     * Render field label
     *
     * @param string $fieldName Field name
     * @param array $options Rendering options
     * @return string HTML
     */
    public function label(string $fieldName, array $options = []): string
    {
        if (!$this->form->hasField($fieldName)) {
            return '';
        }

        $field = $this->form->getField($fieldName);

        // Merge options for label-only rendering
        $fieldOptions = array_merge(
            $this->options,
            $this->fieldCustomOptions[$fieldName] ?? [],
            $options,
            ['render_mode' => 'label_only']
        );

        return $this->form->getRenderer()->renderField($field, $fieldOptions);
    }

    /**
     * Render field errors
     *
     * @param string $fieldName Field name
     * @param array $options Rendering options
     * @return string HTML
     */
    public function errors(string $fieldName, array $options = []): string
    {
        if (!$this->form->hasField($fieldName) || !$this->form->hasErrors()) {
            return '';
        }

        $errors = $this->form->getErrors($fieldName);
        if (empty($errors)) {
            return '';
        }

        $errorHtml = '<div class="invalid-feedback d-block">';
        foreach ($errors as $error) {
            $errorHtml .= '<div>' . htmlspecialchars($error) . '</div>';
        }
        $errorHtml .= '</div>';

        return $errorHtml;
    }

    /**
     * Render special CAPTCHA field
     *
     * @param string $fieldName Field name
     * @param array $options Rendering options
     * @return string HTML
     */
    public function captcha(string $fieldName, array $options = []): string
    {
        if (!$this->form->hasField($fieldName)) {
            return '';
        }

        $field = $this->form->getField($fieldName);

        // Merge options with captcha-specific settings
        $fieldOptions = array_merge(
            $this->options,
            $this->fieldCustomOptions[$fieldName] ?? [],
            $options,
            ['render_mode' => 'captcha']
        );

        return $this->form->getRenderer()->renderField($field, $fieldOptions);
    }

    /**
     * Render a field with customized wrapper
     *
     * @param string $fieldName Field name
     * @param array $options Rendering options
     * @param string $wrapperTemplate HTML template for wrapper (use {field} placeholder)
     * @return string HTML
     */
    public function field(string $fieldName, array $options = [], string $wrapperTemplate = '{field}'): string
    {
        if (!$this->form->hasField($fieldName)) {
            return '';
        }

        $field = $this->form->getField($fieldName);

        // Merge options: base options < field custom options < method options
        $fieldOptions = array_merge(
            $this->options,
            $this->fieldCustomOptions[$fieldName] ?? [],
            $options
        );

        $fieldHtml = $this->form->getRenderer()->renderField($field, $fieldOptions);

        // Replace placeholder with field HTML
        return str_replace('{field}', $fieldHtml, $wrapperTemplate);
    }

    /**
     * Render multiple fields with the same wrapper template
     *
     * @param array $fieldNames Array of field names
     * @param array $options Rendering options
     * @param string $wrapperTemplate HTML template for wrapper
     * @return string HTML
     */
    public function fields(array $fieldNames, array $options = [], string $wrapperTemplate = '{field}'): string
    {
        $output = '';
        foreach ($fieldNames as $fieldName) {
            $output .= $this->field($fieldName, $options, $wrapperTemplate);
        }
        return $output;
    }

    /**
     * Render fields in a row layout (e.g., grid columns)
     *
     * @param array $fieldConfig Array of [fieldName => columnClass] pairs
     * @param array $options Rendering options
     * @return string HTML
     */
    public function fieldRow(array $fieldConfig, array $options = []): string
    {
        $output = '<div class="row">';

        foreach ($fieldConfig as $fieldName => $columnClass) {
            $output .= '<div class="' . htmlspecialchars($columnClass) . '">';
            $output .= $this->field($fieldName, $options);
            $output .= '</div>';
        }

        $output .= '</div>';
        return $output;
    }

    /**
     * Render a custom fieldset with specified fields
     *
     * @param array $fields Fields to include
     * @param string $legend Fieldset legend (optional)
     * @param array $options Rendering options
     * @return string HTML
     */
    public function fieldset(array $fields, string $legend = '', array $options = []): string
    {
        $legendHtml = $legend ? '<legend>' . htmlspecialchars($legend) . '</legend>' : '';

        $output = '<fieldset class="mb-4">' . $legendHtml;

        foreach ($fields as $fieldName) {
            $output .= $this->field($fieldName, $options);
        }

        $output .= '</fieldset>';
        return $output;
    }

    /**
     * Render error summary
     *
     * @param array $options Rendering options
     * @return string HTML
     */
    public function errorSummary(array $options = []): string
    {
        if (!$this->form->hasErrors()) {
            return '';
        }
        // DebugRt::j('0', '', $this->form->getErrors());

        // Set the error display mode to summary for the renderer
        $mergedOptions = array_merge($this->options, $options);
        // DebugRt::j('0', '', $mergedOptions);

        // Only set hide_inline_errors if we're using summary display
        if (isset($mergedOptions['error_display']) && $mergedOptions['error_display'] === 'summary') {
            $this->options['hide_inline_errors'] = true;
        }


        // Get the rendered error summary
        $output = $this->form->getRenderer()->renderErrors($this->form, $mergedOptions);

        // Set a flag in our options to hide inline errors when using summary

        return $output;
    }

    /**
     * Render submit button
     *
     * @param string $label Button text
     * @param array $attributes Additional button attributes
     * @return string HTML
     */
    public function submit(string $label = '', array $attributes = []): string
    {
        $buttonText = $label ?: ($this->options['submit_text'] ?? 'Submit');
        $class = $attributes['class'] ?? ($this->options['submit_class'] ?? 'btn btn-primary');

        // Remove class from attributes to avoid duplication
        unset($attributes['class']);

        $attr = '';
        foreach ($attributes as $name => $value) {
            $attr .= ' ' . $name . '="' . htmlspecialchars($value) . '"';
        }

        return sprintf(
            '<button type="submit" class="%s"%s>%s</button>',
            htmlspecialchars($class),
            $attr,
            htmlspecialchars($buttonText)
        );
    }

    /**
     * Add any custom HTML into the form
     *
     * @param string $html Raw HTML to insert
     * @return string The HTML unchanged
     */
    public function html(string $html): string
    {
        return $html;
    }


    /**
     * Get access to the underlying form object
     *
     * @return FormInterface
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * Render the form according to its defined layout
     *
     * @return string HTML
     */
    public function renderLayout(): string
    {
        $output = '';
        $form = $this->getForm();
        $layout = $form->getLayout();

        // Debug line - remove after confirming layout data
        // echo "<!-- DEBUG: " . htmlspecialchars(json_encode($layout)) . " -->";
        // echo "<!-- DEBUG: " . htmlspecialchars(json_encode($this->options)) . " -->";
        // DebugRt::j('1', '',  $this->options);

        // Check for sections layout
        if (!empty($layout['sections'])) {
            foreach ($layout['sections'] as $section) {
                $type = $section['type'] ?? 'fields';

                switch ($type) {
                    case 'header':
                        $title = $section['title'] ?? '';
                        $output .= "<h3 class=\"title-heading mb-3\">" . htmlspecialchars($title) . "</h3>";
                        break;

                    case 'divider':
                        $output .= "<hr class=\"my-4\">";
                        break;

                    case 'fields':
                        $fields = $section['fields'] ?? [];
                        foreach ($fields as $fieldName) {
                            if ($this->has($fieldName)) {
                                $output .= $this->row($fieldName);
                            }
                        }
                        break;
                }
            }
        }
        // Check for fieldsets layout
        elseif (!empty($layout['fieldsets'])) {
            // Determine column class based on layout
            $columns = count($layout['fieldsets']);
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
                    if ($this->has($fieldName)) {
                        $output .= $this->row($fieldName);
                    }
                }

                $output .= '</fieldset>';
                $output .= '</div>';
            }

            $output .= '</div>';
        }
        // Sequential layout
        elseif (!empty($layout['sequential']) && !empty($layout['sequential']['fields'])) {
            foreach ($layout['sequential']['fields'] as $fieldName) {
                if ($this->has($fieldName)) {
                    $output .= $this->row($fieldName);
                }
            }
        }
        // Fallback: render all form fields
        else {
            foreach ($form->getFields() as $field) {
                $output .= $this->row($field->getName());
            }
        }

        return $output;
    }

}
