<?php

declare(strict_types=1);

namespace Core\Form;

/**
 * Interface for form types
 */
interface FormTypeInterface
{
    // /**
    //  * Validate and set the form fields for rendering.
    //  *
    //  * @param array $listFields Array of field names to set.
    //  * @return void
    //  */
    // public function setFormFields(array $listFields): void;


    // // todo
    // /**
    //  * Merge the given options into the current list options.
    //  *
    //  * @param array $options Associative array of options to merge.
    //  * @return void
    //  */
    // public function mergeFormOptions(array $options): void;


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


    /**
     * set the fields for this form type
     *
     * param array $fields Associative array of form fields.
     * return void
     */
    public function setFocus(string $viewFocus2, string $viewName2 ): void;


    /**
     * Get the layout for this form type.
     *
     * @return array .
     */
    public function getLayout(): array;




    // /**
    //  * Get all options for the form.
    //  *
    //  * @return array Options array.
    //  */
    // public function getOptions(): array;



    // /**
    //  * Set all options for the form.
    //  *
    //  * @param array $options Associative array of options.
    //  * @return void
    //  */
    // public function setFormOptions(array $options): void;


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
