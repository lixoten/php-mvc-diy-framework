<?php

declare(strict_types=1);

namespace Core\Form;

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
     * Get form data
     *
     * @return array
     */
    public function getData(): array;

    /**
     * Get form errors
     *
     * @return array
     */
    public function getErrors(): array;

    /**
     * Set initial form data
     *
     * @param mixed $data
     * @return self
     */
    public function setData($data): self;

    /**
     * Render the form
     *
     * @return string HTML representation of the form
     */
    public function render(): string;


    public function getName(): string;
    public function addError(string $field, string $message): self;
}
