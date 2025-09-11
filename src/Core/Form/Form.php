<?php

declare(strict_types=1);

namespace Core\Form;

use App\Helpers\DebugRt;
use Core\Form\CSRF\CSRFToken;
use Core\Form\Field\FieldInterface;
use Core\Form\Validation\Validator;
use Core\Form\Renderer\FormRendererInterface;

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
    private ?Validator $validator = null;
    private array $attributes = [
        'method' => 'POST',
        'action' => '',
        'enctype' => 'multipart/form-data',
    ];
    private ?FormRendererInterface $renderer = null;
    // private array $layout = [];
    private array $renderOptions = [];

    /**
     * Constructor
     *
     * @param string $name Form name
     * @param CSRFToken $csrf CSRF token service
     */
    public function __construct(string $name, CSRFToken $csrf)
    {
        $this->name = $name;
        $this->csrf = $csrf;
    }

    /**
     * Set validator
     *
     * @param Validator $validator
     * @return self
     */
    public function setValidator(Validator $validator): self
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function addField(FieldInterface $field): self
    {
        $this->fields[$field->getName()] = $field;

        // If we already have data for this field, set it
        if (isset($this->data[$field->getName()])) {
            $field->setValue($this->data[$field->getName()]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getField(string $name): ?FieldInterface
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    public function hasField(string $name): bool
    {
        return isset($this->fields[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function removeField(string $name): self
    {
        if (isset($this->fields[$name])) {
            unset($this->fields[$name]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setData($data): self
    {
        $this->data = $data;

        // Set data to fields
        foreach ($this->fields as $name => $field) {
            if (array_key_exists($name, $data)) {
                $field->setValue($data[$name]);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        $data = [];

        foreach ($this->fields as $name => $field) {
            $data[$name] = $field->getValue();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function submit(array $data): self
    {
        $this->setData($data);
        $this->validate();
        return $this;
    }


    /**
     * Validate the form
     *
     * @param array $context Additional context for validation (e.g. request object)
     * @return bool True if valid, false otherwise
     */
    public function validate(array $context = []): bool
    {
        $this->errors = []; // Reset errors before validation
        $isValid = true;
        // Ensure the validator is set
        if ($this->validator) {
            foreach ($this->fields as $name => $field) {
                // Validate the field using the Validator service

                // Pass context to validator
                $fieldErrors = $this->validator->validateField($field, $context);

                if (!empty($fieldErrors)) {
                    // Add errors to the form's error list
                    $this->errors[$name] = $fieldErrors;

                    // Add errors to the field itself
                    foreach ($fieldErrors as $error) {
                        $field->addError($error);
                    }

                    // Mark the form as invalid
                    $isValid = false;
                }
            }
        } else {
            // If no validator is set, throw an exception
            throw new \RuntimeException('Validator service is not set for the form.');
        }

        return $isValid;
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
    public function getErrors(?string $field = null): array
    {
        if ($field === null) {
            return $this->errors;
        }

        return $this->errors[$field] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute(string $name, $value): self
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Add an error message to a field or to the form
     *
     * @param string $field Field name or '_form' for form-level errors
     * @param string $message Error message
     * @return self
     */
    public function addError(string $field, string $message): self
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;

        // If it's a field error, also add it to the field
        if ($field !== '_form' && isset($this->fields[$field])) {
            $this->fields[$field]->addError($message);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCSRFToken(): string
    {
        return $this->csrf->getToken();
    }

    /**
     * {@inheritdoc}
     */
    public function validateCSRFToken(string $token): bool
    {
        return $this->csrf->validate($token);
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $options = []): string
    {
        // Merge stored options with any provided now (new ones override stored ones)
        $mergedOptions = array_merge($this->renderOptions, $options);

        if ($this->renderer) {
            return $this->renderer->renderForm($this, $mergedOptions);
        }

        // if ($this->renderer) {
        //     return $this->renderer->renderForm($this, $options);
        // }

        $output = '<form';

        // Add form attributes
        foreach ($this->attributes as $name => $value) {
            $output .= ' ' . $name . '="' . htmlspecialchars($value) . '"';
        }

        $output .= '>';

        $token = $this->getCSRFToken();
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
     * Render a field
     *
     * @param string $name Field name
     * @param FieldInterface $field Field
     * @return string HTML
     */
    private function renderField(string $name, FieldInterface $field): string
    {
        $type = $field->getType();
        $label = $field->getLabel();
        $value = $field->getValue();
        $attributes = $field->getAttributes();
        $hasError = $field->hasError();

        $output = '<div class="form-group' . ($hasError ? ' has-error' : '') . '">';

        // Label
        $output .= '<label for="' . $name . '">' . htmlspecialchars($label) . '</label>';

        // Input
        if ($type === 'textarea') {
            $output .= '<textarea name="' . $name . '" id="' . $name . '"';

            // Add attributes
            foreach ($attributes as $attrName => $attrValue) {
                $output .= ' ' . $attrName . '="' . htmlspecialchars((string)$attrValue) . '"';
            }

            $output .= '>' . htmlspecialchars((string)$value) . '</textarea>';
        } else {
            $output .= '<input type="' . $type . '"';
            $output .= ' name="' . $name . '"';
            $output .= ' id="' . $name . '"';
            $output .= ' value="' . htmlspecialchars((string)$value) . '"';

            // Add attributes
            foreach ($attributes as $attrName => $attrValue) {
                $output .= ' ' . $attrName . '="' . htmlspecialchars((string)$attrValue) . '"';
            }

            $output .= '>';
        }

        // Error messages
        if ($hasError) {
            $output .= '<div class="invalid-feedback d-block">';
            foreach ($field->getErrors() as $error) {
                $output .= '<div>' . htmlspecialchars($error) . '</div>';
            }
            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function setRenderer(FormRendererInterface $renderer): self
    {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * Set form layout configuration
     *
     * @param array $layout Layout configuration
     * @return self
     */
    public function setLayout(array $layout): self
    {
        // $this->layout = $layout;
        $this->renderOptions['layout'] = $layout;
        return $this;
    }

    /**
     * Get form layout configuration
     *
     * @return array
     */
    public function getLayout(): array
    {
        // return $this->layout;
        return $this->renderOptions['layout'];
    }


    /**
     * Set the render options for the form
     *
     * @param array $options
     * @return void
     */
    public function setRenderOptions(array $options): void
    {
        $this->renderOptions = $options;
    }


    /**
     * {@inheritdoc}
     */
    public function getRenderer(): FormRendererInterface
    {
        if (!$this->renderer) {
            throw new \RuntimeException('Form renderer is not set');
        }
        return $this->renderer;
    }

    /**
     * Get the form rendering options
     *
     * @return array
     */
    public function getRenderOptions(): array
    {
        return $this->renderOptions;
    }

    ## HELPERS
    ## HELPERS
    ## HELPERS
    ## HELPERS
    ## HELPERS
    ## HELPERS
    ## HELPERS

    /** {@inheritdoc} */
    public function getSecurityLevel(): string
    {
        //DebugRt::j('0', '', 'in Form');
        // First check render options
        if (isset($this->renderOptions['security_level'])) {
            return $this->renderOptions['security_level'];
        }

        // Default
        return 'medium';
    }


    /** {@inheritdoc} */
    public function hasCaptchaScripts(): bool
    {
        return !empty($this->renderOptions['captcha_scripts']);
    }

    /** {@inheritdoc} */
    public function getCaptchaScripts(): string
    {
        return $this->renderOptions['captcha_scripts'] ?? '';
    }

    /** {@inheritdoc} */
    public function isCaptchaRequired(): bool
    {
        return $this->renderOptions['captcha_required'] ?? false;
    }

    /** {@inheritdoc} */
    public function hasCssFormThemeFile(): bool
    {
        return !empty($this->renderOptions['css_form_theme_file']);
    }

    /** {@inheritdoc} */
    public function getCssFormThemeFile(): string
    {
        return $this->renderOptions['css_form_theme_file'] ?? '';
    }
}
