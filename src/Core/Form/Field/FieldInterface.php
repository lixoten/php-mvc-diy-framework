<?php

declare(strict_types=1);

namespace Core\Form\Field;

/**
 * Interface for form fields
 */
interface FieldInterface
{
    /**
     * Get the field name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the field type
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Get the field value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set the field value
     *
     * @param mixed $value
     * @return self
     */
    public function setValue($value): self;

    /**
     * Get the field label
     *
     * @return string
     */
    public function getLabel(): string;

    /**
     * Get field error messages
     *
     * @return array
     */
    public function getErrors(): array;

    /**
     * Add an error message
     *
     * @param string $message
     * @return self
     */
    public function addError(string $message): self;

    /**
     * Check if field has errors
     *
     * @return bool
     */
    public function hasError(): bool;

    /**
     * Get all field options
     *
     * @return array
     */
    public function getOptions(): array;

    /**
     * Get HTML attributes
     *
     * @return array
     */
    public function getAttributes(): array;

   /**
     * Get a specific attribute value
     *
     * @param string $name Attribute name
     * @param mixed $default Default value if attribute doesn't exist
     * @return mixed
     */
    public function getAttribute(string $name, $default = null);

    /**
     * Get HTML attributes as a string
     *
     * @return string
     */
    public function getAttributesString(): string;


    /**
     * Check if the field is required
     *
     * @return bool
     */
    public function isRequired(): bool;

    public function setOptions(array $options): self;


    /**
     * Render the field as HTML
     *
     * @return string
     */
    public function render(): string;
}
