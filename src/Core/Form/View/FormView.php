<?php

declare(strict_types=1);

namespace Core\Form\View;

use Core\Form\FormInterface;
use App\Helpers\DebugRt;
use Core\Form\Field\FieldInterface;

/**
 * Form view helper for flexible form rendering in templates
 */
class FormView
{
    private FormInterface $form;
    private array $options = [];
    private bool $isErrorDisplaySummary = false; // Add this property

    /**
     * @param FormInterface $form The form to wrap
     * @param array $options Rendering options
     */
    public function __construct(FormInterface $form, array $options = [])
    {
        $this->form = $form;

        // Get the form's render options first
        $formOptions = $form->getRenderOptions();


        // If layout has error_display, add it to form options with priority
        if ($layoutErrorDisplay) {
            $formOptions['error_display'] = $layoutErrorDisplay;
        }


        // Merge options (passed options override form options)
        $this->options = array_merge($formOptions, $options);

        // Check MERGED options
        $this->isErrorDisplaySummary = isset($this->options['error_display']) &&
                                      $this->options['error_display'] === 'summary';

        // Modify MERGED options
        if ($this->isErrorDisplaySummary) {
            $this->options['hide_inline_errors'] = true;
        }


        //$this->options = $options;
    }

    /**
     * Render the form start tag (without errors - use errorSummary separately)
     */
    public function start(array $options = []): string
    {
        $renderer = $this->form->getRenderer();

        // First merge options to create $mergedOptions
        $formOptions = $this->form->getRenderOptions(); // Get original form options
        $mergedOptions = array_merge($formOptions, $this->options, $options); // Merge in correct order

        // THEN check/modify $mergedOptions
        if ($this->form->hasField('captcha') && empty($mergedOptions['onsubmit'])) {
            $mergedOptions['onsubmit'] = 'return validateCaptcha()';
        }

        return $renderer->renderStart($this->form, $mergedOptions);
    }

    /**
     * Display error summary explicitly
     */
    public function errorSummary(array $options = []): string
    {
        // Only proceed if we're in summary mode
        if (!$this->isErrorDisplaySummary) {
            return '';
        }

        $mergedOptions = array_merge($this->options, $options);
        $mergedOptions['error_display'] = 'summary';

        $renderer = $this->form->getRenderer();
        return $renderer->renderErrors($this->form, $mergedOptions);
    }

    public function row(string $fieldName, array $options = []): string
    {
        if (!$this->form->hasField($fieldName)) {
            return '';
        }

        $field = $this->form->getField($fieldName);

        // Merge field-specific options with global options
        $mergedOptions = array_merge($this->options, $field->getOptions(), $options);

        $renderer = $this->form->getRenderer();
        $fieldHtml = $renderer->renderField($field, $mergedOptions);

        // ONLY add error HTML if the renderer isn't already handling it
        $errorHtml = '';
        if (
            !($mergedOptions['hide_inline_errors'] ?? false) &&
            !($mergedOptions['renderer'] ?? '' === 'bootstrap')
        ) {
            // Only add our own error HTML for non-Bootstrap renderers
            $errorMessage = $this->error($fieldName);
            if ($errorMessage) {
                $errorHtml = '<div class="invalid-feedback d-block">' . htmlspecialchars($errorMessage) . '</div>';
            }
        }

        return $fieldHtml . $errorHtml;
    }

    /**
     * Render the form end tag
     */
    public function end(array $options = []): string
    {
        $renderer = $this->form->getRenderer();
        return $renderer->renderEnd($this->form, $options);
    }

    /**
     * Render a submit button
     */
    public function submit(string $label = 'Submit', $options = 'btn btn-primary'): string
    {
        // Handle options as string (class) or array (attributes)
        if (is_array($options)) {
            $class = $options['class'] ?? 'btn btn-primary';
        } else {
            $class = $options;
        }

        return sprintf(
            '<div class="mb-3"><button type="submit" class="%s">%s</button></div>',
            htmlspecialchars($class),
            htmlspecialchars($label)
        );
    }

    /**
     * Render a reset button
     */
    public function reset(string $label = 'Reset', $options = 'btn btn-secondary'): string
    {
        // Handle options as string (class) or array (attributes)
        if (is_array($options)) {
            $class = $options['class'] ?? 'btn btn-secondary';
        } else {
            $class = $options;
        }

        return sprintf(
            '<div class="mb-3"><button type="reset" class="%s">%s</button></div>',
            htmlspecialchars($class),
            htmlspecialchars($label)
        );
    }





    /**
     * Get access to the underlying form object
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * Render all fields sequentially
     */
    public function renderFields(array $options = []): string
    {
        $output = '';
        foreach ($this->form->getFields() as $field) {
            $output .= $this->form->getRenderer()->renderField($field, $options);
        }
        return $output;
    }

    /**
     * Get view options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Check if a field exists in the form
     *
     * @param string $fieldName The field name to check
     * @return bool True if the field exists
     */
    public function has(string $fieldName): bool
    {
        return $this->form->hasField($fieldName);
    }

    /**
     * Get error message for a field
     *
     * @param string $fieldName Field name
     * @return string|null Error message or null
     */
    public function error(string $fieldName): ?string
    {
        // Use the form's getErrors() method to retrieve field errors
        $errors = $this->form->getErrors($fieldName);

        // Return the first error as a string (if multiple errors exist)
        return !empty($errors) ? reset($errors) : null;
    }

    /**
     * Display form errors
     */
    public function errors(array $options = []): string
    {
        $renderer = $this->form->getRenderer();
        return $renderer->renderErrors($this->form, $options);
    }



    /**
     * Get a field from the form
     *
     * @param string $fieldName The field name to get
     * @return mixed The field object or null if not found
     */
    public function getField(string $fieldName): ?FieldInterface
    {
        return $this->form->hasField($fieldName) ? $this->form->getField($fieldName) : null;
    }

    /**
     * Render a CAPTCHA field.
     *
     * @param string $fieldName The name of the CAPTCHA field
     * @param array $options Additional options for rendering
     * @return string The rendered CAPTCHA HTML
     */
    public function captcha(string $fieldName, array $options = []): string
    {
        if (!$this->form->hasField($fieldName)) {
            return '';
        }

        $field = $this->form->getField($fieldName);

        // Get the CAPTCHA service from the field options
        $fieldOptions = $field->getOptions();
        $captchaService = $fieldOptions['captcha_service'] ?? null;

        if (!$captchaService instanceof \Core\Security\Captcha\CaptchaServiceInterface) {
            return $this->row($fieldName, $options);
        }
        //public function row(string $fieldName, array $options = []): string

        // Get theme and size options from field options and override with any passed options
        $theme = $options['theme'] ?? $fieldOptions['theme'] ?? 'light';
        $size = $options['size'] ?? $fieldOptions['size'] ?? 'normal';

        // Render label
        $label = htmlspecialchars($field->getLabel());
        $output = '<div class="mb-3">';
        $output .= '<label class="form-label" for="' . $fieldName . '">' . $label . '</label>';

        // Render the CAPTCHA HTML using the service
        $output .= $captchaService->render($fieldName, ['theme' => $theme, 'size' => $size]);

        // Add error handling
        $errorMessage = $this->error($fieldName);
        if ($errorMessage) {
            $output .= '<div class="invalid-feedback d-block">' . htmlspecialchars($errorMessage) . '</div>';
        }

        $output .= '</div>';

        return $output;
    }
}
