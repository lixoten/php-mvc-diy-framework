# Creating Custom Renderers

## Overview

The MVCLixo form system allows you to create custom renderers to control exactly how your forms are displayed. This is useful when:

- You need to integrate with a different CSS framework (e.g., Tailwind, Foundation)
- You have specific design requirements that Bootstrap doesn't satisfy
- You want to ensure consistent form styling across your application

## How Renderers Work

Renderers implement the `FormRendererInterface` which requires methods for rendering different parts of a form:

```php
interface FormRendererInterface
{
    public function renderForm(FormInterface $form, array $options = []): string;
    public function renderStart(FormInterface $form, array $options = []): string;
    public function renderErrors(FormInterface $form, array $options = []): string;
    public function renderField(FieldInterface $field, array $options = []): string;
    public function renderEnd(FormInterface $form, array $options = []): string;
}
```

## Creating a Custom Renderer

### Step 1: Create the Renderer Class

```php
// src/Core/Form/Renderer/TailwindRenderer.php
<?php

declare(strict_types=1);

namespace Core\Form\Renderer;

use Core\Form\FormInterface;
use Core\Form\Field\FieldInterface;

class TailwindRenderer implements FormRendererInterface
{
    /**
     * Render the entire form
     */
    public function renderForm(FormInterface $form, array $options = []): string
    {
        $output = $this->renderStart($form, $options);
        $output .= $this->renderErrors($form, $options);
        $output .= $this->renderFields($form, $options);
        $output .= $this->renderEnd($form, $options);

        return $output;
    }

    /**
     * Render form start tag
     */
    public function renderStart(FormInterface $form, array $options = []): string
    {
        // Get form attributes
        $attributes = $form->getAttributes();

        // Add custom class if provided
        if (!empty($options['css_form_theme_class'])) {
            $attributes['class'] = ($attributes['class'] ?? '') . ' ' . $options['css_form_theme_class'];
        }

        // Add Tailwind validation class
        $attributes['class'] = ($attributes['class'] ?? '') . ' tailwind-form';
        $attributes['novalidate'] = '';

        // Build HTML attributes
        $attrString = $this->buildAttributeString($attributes);

        return "<form{$attrString}>";
    }

    /**
     * Render form errors section
     */
    public function renderErrors(FormInterface $form, array $options = []): string
    {
        // Only show error summary if requested
        if (($options['error_display'] ?? 'inline') !== 'summary') {
            return '';
        }

        $errors = [];
        foreach ($form->getFields() as $field) {
            $fieldErrors = $field->getErrors();
            if (!empty($fieldErrors)) {
                foreach ($fieldErrors as $error) {
                    $errors[] = "<li class=\"text-sm\">
                        <span class=\"font-medium\">{$field->getLabel()}:</span> {$error}
                    </li>";
                }
            }
        }

        if (empty($errors)) {
            return '';
        }

        return "<div class=\"bg-red-50 border border-red-400 text-red-700 px-4 py-3 mb-6 rounded\">
            <p class=\"font-semibold mb-2\">Please correct the following errors:</p>
            <ul class=\"ml-5 list-disc\">" . implode('', $errors) . "</ul>
        </div>";
    }

    /**
     * Render a single field
     */
    public function renderField(FieldInterface $field, array $options = []): string
    {
        // Get field type and properties
        $type = $field->getType();
        $name = $field->getName();
        $value = $field->getValue();
        $label = $field->getLabel();
        $errors = $field->getErrors();
        $required = $field->isRequired();
        $attributes = $field->getAttributes();

        // Add Tailwind CSS classes
        $attributes['class'] = ($attributes['class'] ?? '') . ' mt-1 block w-full rounded-md';

        // Add error classes
        if (!empty($errors) && empty($options['hide_inline_errors'])) {
            $attributes['class'] .= ' border-red-500';
        } else {
            $attributes['class'] .= ' border-gray-300';
        }

        // Required attribute
        if ($required) {
            $attributes['required'] = '';
        }

        // Generate input field
        $input = $this->renderInput($type, $name, $value, $attributes);

        // Assemble complete field markup
        $output = "<div class=\"mb-4\">";
        $output .= "<label class=\"block text-gray-700 text-sm font-bold mb-2\" for=\"$name\">";
        $output .= htmlspecialchars($label) . ($required ? ' <span class="text-red-500">*</span>' : '');
        $output .= "</label>";
        $output .= $input;

        // Add error message if any (and not hidden)
        if (!empty($errors) && empty($options['hide_inline_errors'])) {
            $output .= "<p class=\"text-red-500 text-xs italic mt-1\">" . htmlspecialchars($errors[0]) . "</p>";
        }

        // Add help text if provided
        if (!empty($attributes['help'])) {
            $output .= "<p class=\"text-gray-500 text-xs mt-1\">" . htmlspecialchars($attributes['help']) . "</p>";
        }

        $output .= "</div>";

        return $output;
    }

    /**
     * Render form end tag
     */
    public function renderEnd(FormInterface $form, array $options = []): string
    {
        $output = '';

        // Add submit button unless disabled
        if (empty($options['no_submit_button'])) {
            $submitText = $options['submit_text'] ?? 'Submit';
            $submitClass = $options['submit_class'] ?? 'bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded';

            $output .= "<div class=\"mt-6\">
                <button type=\"submit\" class=\"{$submitClass}\">{$submitText}</button>
            </div>";
        }

        $output .= "</form>";

        return $output;
    }

    /**
     * Helper to render fields
     */
    protected function renderFields(FormInterface $form, array $options = []): string
    {
        $output = '';

        // Get layout type and structure
        $layoutType = $options['layout_type'] ?? 'none';

        switch ($layoutType) {
            case 'fieldsets':
                $output .= $this->renderFieldsetLayout($form, $options);
                break;
            case 'sections':
                $output .= $this->renderSectionLayout($form, $options);
                break;
            default:
                // Sequential rendering of all fields
                foreach ($form->getFields() as $field) {
                    $output .= $this->renderField($field, $options);
                }
        }

        return $output;
    }

    /**
     * Helper to render fieldset layout
     */
    protected function renderFieldsetLayout(FormInterface $form, array $options = []): string
    {
        $output = '';
        $layout = $options['layout'] ?? [];
        $fieldsets = $layout['fieldsets'] ?? [];

        foreach ($fieldsets as $id => $fieldset) {
            $legend = $fieldset['legend'] ?? '';
            $fields = $fieldset['fields'] ?? [];

            $output .= "<fieldset class=\"border border-gray-300 p-4 rounded mb-6\">";

            if (!empty($legend)) {
                $output .= "<legend class=\"text-lg font-medium px-2\">" . htmlspecialchars($legend) . "</legend>";
            }

            // Render fields in this fieldset
            foreach ($fields as $fieldName) {
                if ($form->hasField($fieldName)) {
                    $output .= $this->renderField($form->getField($fieldName), $options);
                }
            }

            $output .= "</fieldset>";
        }

        return $output;
    }

    /**
     * Helper to render section layout
     */
    protected function renderSectionLayout(FormInterface $form, array $options = []): string
    {
        $output = '';
        $layout = $options['layout'] ?? [];
        $sections = $layout['sections'] ?? [];

        foreach ($sections as $section) {
            $type = $section['type'] ?? 'fields';

            switch ($type) {
                case 'header':
                    $title = $section['title'] ?? '';
                    $output .= "<h3 class=\"text-lg font-medium mb-3\">" . htmlspecialchars($title) . "</h3>";
                    break;

                case 'divider':
                    $output .= "<hr class=\"my-4 border-t border-gray-300\">";
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

        return $output;
    }

    /**
     * Helper to render input field based on type
     */
    protected function renderInput(string $type, string $name, $value, array $attributes): string
    {
        $attrString = $this->buildAttributeString($attributes);
        $value = htmlspecialchars((string)$value);

        switch ($type) {
            case 'textarea':
                return "<textarea name=\"{$name}\"{$attrString}>{$value}</textarea>";

            case 'select':
                $output = "<select name=\"{$name}\"{$attrString}>";
                $choices = $attributes['choices'] ?? [];

                // Add placeholder option if specified
                if (!empty($attributes['placeholder'])) {
                    $output .= "<option value=\"\">" . htmlspecialchars($attributes['placeholder']) . "</option>";
                }

                // Add options
                foreach ($choices as $optValue => $label) {
                    $selected = ((string)$optValue === (string)$value) ? ' selected' : '';
                    $output .= "<option value=\"" . htmlspecialchars((string)$optValue) . "\"{$selected}>" .
                               htmlspecialchars($label) . "</option>";
                }

                $output .= "</select>";
                return $output;

            case 'checkbox':
                $checked = $value ? ' checked' : '';
                return "<input type=\"checkbox\" name=\"{$name}\" value=\"1\"{$attrString}{$checked}>";

            case 'radio':
                // Radio groups are handled specially
                $output = "<div class=\"mt-2\">";
                $choices = $attributes['choices'] ?? [];

                foreach ($choices as $optValue => $label) {
                    $checked = ((string)$optValue === (string)$value) ? ' checked' : '';
                    $id = "{$name}_{$optValue}";
                    $output .= "<div class=\"flex items-center mb-1\">";
                    $output .= "<input type=\"radio\" id=\"{$id}\" name=\"{$name}\" value=\"" .
                               htmlspecialchars((string)$optValue) . "\"{$checked} class=\"mr-2\">";
                    $output .= "<label for=\"{$id}\">" . htmlspecialchars($label) . "</label>";
                    $output .= "</div>";
                }

                $output .= "</div>";
                return $output;

            default:
                // Default is a text input
                return "<input type=\"{$type}\" name=\"{$name}\" value=\"{$value}\"{$attrString}>";
        }
    }

    /**
     * Helper to build HTML attributes string
     */
    protected function buildAttributeString(array $attributes): string
    {
        $result = '';

        foreach ($attributes as $name => $value) {
            // Skip special attributes used internally
            if ($name === 'choices' || $name === 'help') {
                continue;
            }

            // Boolean attribute
            if ($value === true || $value === '') {
                $result .= " {$name}";
            } elseif ($value !== false && $value !== null) {
                $result .= " {$name}=\"" . htmlspecialchars((string)$value) . "\"";
            }
        }

        return $result;
    }
}
```

### Step 2: Register Your Custom Renderer

You need to register your renderer with the renderer registry:

```php
// In your application bootstrap or service configuration
$formRendererRegistry = new FormRendererRegistry();
$formRendererRegistry->addRenderer('tailwind', new TailwindRenderer());
```

### Step 3: Use Your Custom Renderer

```php
// In your controller
$form = $this->formFactory->create(
    $this->contactFormType,
    [],
    [
        'renderer' => 'tailwind',  // Use your custom renderer
        'layout_type' => 'fieldsets'
    ]
);
```

## Extending Existing Renderers

Instead of creating a renderer from scratch, you can extend an existing one:

```php
// src/Core/Form/Renderer/CustomBootstrapFormRenderer.php
class CustomBootstrapRenderer extends BootstrapFormRenderer
{
    public function renderField(FieldInterface $field, array $options = []): string
    {
        // Add custom logic before or after calling parent
        $output = parent::renderField($field, $options);

        // Add extra HTML
        if ($field->getName() === 'email') {
            $output .= '<div class="text-muted">We will never share your email</div>';
        }

        return $output;
    }
}
```

## Renderer Best Practices

1. **Use consistent CSS frameworks**: Don't mix different framework renderers in the same app
2. **Keep renderers focused**: Renderers should handle presentation only, not validation logic
3. **Support all field types**: Ensure your renderer supports all field types your app uses
4. **Handle accessibility**: Include proper ARIA attributes and focus states
5. **Support all layout types**: Implement all three layout types (none, fieldsets, sections)
6. **Test with complex forms**: Verify your renderer works with nested fields and collections

## Renderer Options Reference

| Option | Description | Used In |
|--------|-------------|---------|
| `css_form_theme_class` | Extra CSS class for theming | renderStart() |
| `error_display` | 'inline' or 'summary' | renderErrors(), renderField() |
| `hide_inline_errors` | Whether to hide inline errors | renderField() |
| `layout_type` | 'none', 'fieldsets', or 'sections' | renderFields() |
| `layout` | Layout structure configuration | renderFields() |
| `no_submit_button` | Whether to omit the submit button | renderEnd() |
| `submit_text` | Text for the submit button | renderEnd() |
| `submit_class` | CSS class for submit button | renderEnd() |

## Advanced: Creating Framework-Agnostic Forms

For applications that might change CSS frameworks in the future:

```php
// Use a configuration setting to determine which renderer to use
$rendererName = $this->config->get('app.forms.renderer', 'bootstrap');

$form = $this->formFactory->create(
    $this->contactFormType,
    [],
    [
        'renderer' => $rendererName
    ]
);
```

This allows you to switch the entire application's form styling by changing a single configuration value.