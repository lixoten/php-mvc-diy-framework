<?php

declare(strict_types=1);

namespace Core\Form;

use Core\Form\Field\FieldInterface;
use Core\Form\Renderer\FormRendererInterface;

/**
 * Interface for forms
 */
interface FormInterface
{
    /**
     * Submit data to the form
     *
     * @param array $data Form data
     * @return self
     */
    public function submit(array $data): self;

    /**
     * Check if form is valid
     *
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Check if the form has any errors
     *
     * @return bool True if the form has errors, false otherwise
     */
    public function hasErrors(): bool;

    /**
     * Get form data
     *
     * @return array
     */
    public function getData(): array;


    /**
     * Get form updatable data
     *
     * Disabled and Readonly Data is unset/removed
     *
     * @return array
     */
    public function getUpdatableData(): array;


    /**
     * Get form errors
     *
     * @param string|null $field If provided, returns errors only for this field
     * @return array
     */
    public function getErrors(?string $field = null): array;

    /**
     * Set initial form data
     *
     * @param mixed $data
     * @return self
     */
    public function setData($data): self;

    // /**
    //  * Render the form
    //  *
    //  * @param array $options Rendering options
    //  * @return string HTML representation of the form
    //  */
    // public function render(array $options = []): string;

    /**
     * Add a field to the form
     *
     * @param FieldInterface $field
     * @return self
     */
    public function addField(FieldInterface $field): self;

    /**
     * Get the form name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the form PageKey
     *
     * @return string
     */
    public function getPageKey(): string;

    /**
     * Get the form PageNme
     *
     * @return string
     */
    public function getPageName(): string;

    /**
     * Add an error to the form
     *
     * @param string $field Field name or '_form' for global errors
     * @param string $message Error message
     * @return self
     */
    public function addError(string $field, string $message): self;

    /**
     * Set a form attribute
     *
     * @param string $name Attribute name
     * @param mixed $value Attribute value
     * @return self
     */
    public function setAttribute(string $name, $value): self;

    /**
     * Get all form attributes
     *
     * @return array
     */
    public function getAttributes(): array;

    /**
     * Get all form fields
     *
     * @return FieldInterface[]
     */
    public function getFields(): array;

    /**
     * Get a specific field by name
     *
     * @param string $name
     * @return FieldInterface|null
     */
    public function getField(string $name): ?FieldInterface;

    /**
     * Set the form renderer.
     *
     * @param FormRendererInterface $renderer
     * @return self
     */
    public function setRenderer(FormRendererInterface $renderer): self;

    /**
     * Validate CSRF token
     *
     * @param string $token
     * @return bool
     */
    public function validateCSRFToken(string $token): bool;

    /**
     * Get CSRF token
     *
     * @return string
     */
    public function getCSRFToken(): string;

    /**
     * Validate the form
     *
     * @return bool True if the form is valid, false otherwise
     */
    public function validate(): bool;

    /**
     * Set form layout configuration
     *
     * @param array $layout Layout configuration
     * @return self
     */
    public function setLayout(array $layout): self;

    /**
     * Get form layout configuration
     *
     * @return array
     */
    public function getLayout(): array;

    /**
     * Check if a field exists
     *
     * @param string $name Field name
     * @return bool
     */
    public function hasField(string $name): bool;

    /**
     * Get the form renderer
     *
     * @return FormRendererInterface
     */
    public function getRenderer(): FormRendererInterface;

    /**
     * Get the form rendering options
     *
     * @return array
     */
    public function getRenderOptions(): array;

    /**
     * Set the form rendering options
     *
     * @return void
     */
    public function setRenderOptions(array $options): void;

    /**
     * Add to Existing render options for the form
     *
     * @param array $options
     * @return void
     */
    public function mergeRenderOptions(array $options): void;


    /**
     * Get the security level of this form
     *
     * @return string 'high', 'medium', or 'low'
     */
    public function getSecurityLevel(): string;

    /**
     * Check if the form has CAPTCHA scripts
     *
     * @return bool
     */
    public function hasCaptchaScripts(): bool;

    /**
     * Get CAPTCHA scripts HTML
     *
     * @return string
     */
    public function getCaptchaScripts(): string;

    /**
     * Check if form requires CAPTCHA
     *
     * @return bool
     */
    public function isCaptchaRequired(): bool;

    /**
     * Check if the form has CSS theme file
     *
     * @return bool
     */
    public function hasCssFormThemeFile(): bool;

    /**
     * Get CSS theme file name
     *
     * @return string
     */
    public function getCssFormThemeFile(): string;
}
