<?php

declare(strict_types=1);

namespace Core\Form\View;

use Core\Form\FormInterface;
use App\Helpers\DebugRt as Debug;

/**
 * Form view helper for flexible form rendering in templates
 */
class FormView
{
    private FormInterface $form;
    private array $options = [];

    /**
     * @param FormInterface $form The form to wrap
     * @param array $options Rendering options
     */
    public function __construct(FormInterface $form, array $options = [])
    {
        $this->form = $form;

        // CRUCIAL FIX: Explicitly set hide_inline_errors when using summary display
        if (isset($options['error_display']) && $options['error_display'] === 'summary') {
            $options['hide_inline_errors'] = true;
        }


        $this->options = $options;
    }

    /**
     * Render the form start tag (without errors - use errorSummary separately)
     */
    public function start(array $options = []): string
    {
        $renderer = $this->form->getRenderer();
        $formOptions = $this->form->getRenderOptions(); // Get original form options
        $mergedOptions = array_merge($formOptions, $this->options, $options); // Merge in correct order
        return $renderer->renderStart($this->form, $mergedOptions);
    }


    /**
     * Display error summary explicitly
     */
    public function errorSummary(array $options = []): string
    {
        // Only proceed if we're in summary mode
        if ($this->options['error_display'] !== 'summary') {
            return '';
        }

        $mergedOptions = array_merge($this->options, $options);
        $mergedOptions['error_display'] = 'summary';

        $renderer = $this->form->getRenderer();
        return $renderer->renderErrors($this->form, $mergedOptions);
    }

    /**
     * Render a single form row (label + field + error)
     */
    public function row(string $fieldName, array $options = []): string
    {
        if (!$this->form->hasField($fieldName)) {
            return '';
        }

        // Merge form options with field-specific options
        $mergedOptions = array_merge($this->options, $options);

        $renderer = $this->form->getRenderer();
        return $renderer->renderField($this->form->getField($fieldName), $mergedOptions);
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
     * Display form errors
     */
    public function errors(array $options = []): string
    {
        $renderer = $this->form->getRenderer();
        return $renderer->renderErrors($this->form, $options);
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
}
