<?php

declare(strict_types=1);

namespace Core\Form;

/**
 * Interface for form types
 */
interface FormTypeInterface
{
    /**
     * Get the render options array for this form type.
     *
     * @return array Associative array of render options.
     */
    public function getRenderOptions(): array;

    /**
     * Set the render options for the form.
     *
     * @param array $renderOptions Associative array of render options.
     * @return void
     */
    public function setRenderOptions(array $renderOptions): void;


    /**
     * Get the fields for this form type.
     *
     * @return array .
     */
    public function getFields(): array;

    /**
     * set the fields for this form type
     *
     * @param array $fields Associative array of form fields.
     * @return void
     */
    public function setFields(array $fields): void;


    public function mergeRenderOptions(array $renderOptions): void;



    /**
     * set the fields for this form type
     *
     * param array $fields Associative array of form fields.
     * return void
     */
    public function setFocus(
        string $pageKey,
        string $pageName,
        string $pageAction,
        string $pageFeature,
        string $pageEntity,
    ): void;


    /**
     * Get the layout for this form type.
     *
     * @return array .
     */
    public function getLayout(): array;




    /**
     * Validate an array of form field names against the known schema for this form type.
     * Sets entity and page context before validation.
     * Logs and triggers warnings for any invalid fields.
     *
     * @param array $fields Array of field names to validate.
     * @return array Array of valid field names.
     */
    public function validateFields(array $fields): array;


    /**
     * Build the form using the provided builder.
     * Adds validated fields and layout, applies CAPTCHA if needed.
     *
     * @param FormBuilderInterface $builder The form builder instance.
     * @return void
     */
    // public function buildForm(FormBuilderInterface $builder, array $options = []): void;
    public function buildForm(FormBuilderInterface $builder): void;
}
