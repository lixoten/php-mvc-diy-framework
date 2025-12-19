<?php

declare(strict_types=1);

namespace Core\Form;

use Core\Form\CSRF\CSRFToken;
use Core\Form\Field\FieldInterface;
use Core\Form\Validation\Validator;

/**
 * Default form implementation
 */
class Form implements FormInterface
{
    private string $name;
    private string $pageKey;
    private string $pageName;
    private array $fields = [];
    private array $data   = [];
    private array $extraProcessedData   = [];
    private array $errors = [];
    private CSRFToken $csrf;
    private ?Validator $validator = null;
    private array $attributes = [
        'method' => 'POST',
        'action' => '',
        'enctype' => 'multipart/form-data',
    ];
    // private ?FormRendererInterface $renderer = null;
    // private array $layout = [];
    private array $renderOptions = [];
    private array $layout        = [];
    private array $context       = [];

    /**
     * Constructor
     *
     * @param string $name Form name
     * @param CSRFToken $csrf CSRF token service
     */
    public function __construct(string $name, string $pageName, CSRFToken $csrf)
    {
        $this->name     = $name;
        $this->pageKey  = $name;
        $this->pageName = $pageName;
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
    public function getPageKey(): string
    {
        return $this->pageKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageName(): string
    {
        return $this->pageName;
    }

    /** {@inheritdoc} */
    public function addContext(array $context): self
    {
        $this->context = array_merge($this->context, $context);
        return $this;
    }


    /** {@inheritdoc} */
    public function getContext(): array
    {
        return $this->context;
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
    public function setExtraProcessedData($extraProcessedData): self
    {
        $this->extraProcessedData = $extraProcessedData;

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function getExtraProcessedData(): array
    {
        if (empty($this->extraProcessedData)) {
            return [];
        } else {
            return $this->extraProcessedData;
        }
    }

    // public function hasExtraProcessedData(): bool
    // {
    //     return !empty($this->extraProcessedData);
    // }


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
     * @param array<string, mixed> $context Additional context for validation (e.g. 'region' for phone validation).
     *                             Do NOT pass the entire request object; only pass minimal data needed by validators.
     * @return bool True if valid, false otherwise
     */
    public function validate(array $fieldContext = []): bool
    {
        $this->errors = []; // Reset errors before validation
        $isValid = true;
        // Ensure the validator is set
        if ($this->validator) {
            foreach ($this->fields as $name => $field) {
                // Validate the field using the Validator service

                //$rrr = $field->getAttribute('disabled'); // fixme - just remove
                // Disabled and Readonly fields should be skipped during Validation
                if ($field->getAttribute('disabled') ||
                    $field->getAttribute('readonly') ||
                    $field->getType() === 'hidden'
                ) {
                // if ($field['attributes']['readonly']) {
                    continue;
                }


                // FindMe - Validate Entry
                // Pass context to validator
                $fieldErrors = $this->validator->validateField($field, $fieldContext);

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
    public function getUpdatableData(): array
    {
        $data = $this->getData();
        foreach ($this->fields as $name => $field) {
            if ($field->getAttribute('type') === 'hidden' || $field->getAttribute('disabled') || $field->getAttribute('readonly')) {
                unset($data[$name]);
            }
        }
        return $data;
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

    // /**
    //  * {@inheritdoc}
    //  */
    // public function setRenderer(FormRendererInterface $renderer): self
    // {
    //     $this->renderer = $renderer;
    //     return $this;
    // }

    /**
     * Set form layout configuration
     *
     * @param array $layout Layout configuration
     * @return self
     */
    public function setLayout(array $layout): self
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * Get form layout configuration
     *
     * @return array
     */
    public function getLayout(): array
    {
        return $this->layout;
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
     * Add to Existing render options for the form
     *
     * @param array $options
     * @return void
     */
    public function mergeRenderOptions(array $options): void
    {
        $this->renderOptions = array_merge($this->renderOptions, $options);
    }


    // /**
    //  * {@inheritdoc}
    //  */
    // public function getRenderer(): FormRendererInterface
    // {
    //     if (!$this->renderer) {
    //         throw new \RuntimeException('Form renderer is not set');
    //     }
    //     return $this->renderer;
    // }

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
