<?php

declare(strict_types=1);

namespace Core\Form;

/**
 * Interface for form types
 */
interface FormTypeInterface
{
    /**
     * Set the focus for the FormType, which also sets the context for field resolution.
     *
     * @param string $pageKey The unique key for the current page/view.
     * @param string $pageName The user-friendly name of the page.
     * @param string $pageAction The action being performed (e.g., 'create', 'edit').
     * @param string $pageFeature The name of the feature (e.g., 'Testy', 'Users').
     * @param string $pageEntity The entity name (e.g., 'testy', 'user').
     * @return void
     */
    public function setFocus(
        string $pageKey,
        string $pageName,
        string $pageAction,
        string $pageFeature,
        string $pageEntity,
    ): void;

    /**
     * Get the general options array for this form type.
     *
     * @return array<string, mixed> Associative array of general options.
     */
    public function getOptions(): array;

    /**
     * Get the render options array for this form type.
     *
     * @return array<string, mixed> Associative array of render options.
     */
    public function getRenderOptions(): array;

    /**
     * Set the render options for the form.
     *
     * @param array<string, mixed> $renderOptions Associative array of render options.
     * @return void
     */
    public function setRenderOptions(array $renderOptions): void;

    /**
     * Merge additional render options with the existing ones.
     *
     * This method allows for incrementally adding or overriding rendering options,
     * typically for specific display adjustments.
     *
     * @param array<string, mixed> $renderOptions Associative array of render options to merge.
     * @return void
     */
    public function mergeRenderOptions(array $renderOptions): void;

    /**
     * Get the fields for this form type.
     *
     * Should return an array of field names that are configured for this form.
     *
     * @return array<string> List of field names.
     */
    public function getFields(): array;

    /**
     * Set the fields for this form type.
     *
     * This method is typically used internally after field validation and filtering.
     *
     * @param array<string> $fields An array of valid field names.
     * @return void
     */
    public function setFields(array $fields): void;

    /**
     * Get the layout for this form type.
     *
     * Should return the structured layout definition for the form.
     *
     * @return array<array<string, mixed>> The form layout structure.
     */
    public function getLayout(): array;

    /**
     * Set the layout for this form type.
     *
     * This method is typically used internally after layout validation and fixing.
     *
     * @param array<array<string, mixed>> $layout The form layout structure.
     * @return void
     */
    public function setLayout(array $layout): void;

    /**
     * Get the hidden fields for this form type.
     *
     * Should return an array of field names that are hidden in the form.
     *
     * @return array<string> List of hidden field names.
     */
    public function getHiddenFields(): array;

    /**
     * Set the hidden fields for this form type.
     *
     * This method is typically used internally after hidden field processing.
     *
     * @param array<string> $hiddenFields An array of hidden field names.
     * @return void
     */
    public function setHiddenFields(array $hiddenFields): void;

    /**
     * Builds the form structure using a FormBuilder.
     *
     * @param FormBuilderInterface $builder The form builder instance.
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder): void;

    /**
     * Overrides parts of the form's configuration with new values.
     *
     * This method allows controllers or other components to dynamically adjust
     * form options, render options, layout, or hidden fields after initial setup.
     * The provided $options array should contain keys that correspond to the
     * form's configuration segments (e.g., 'options', 'render_options', 'layout', 'hidden_fields').
     *
     * @param array<string, mixed> $options An associative array of configuration overrides.
     * @return void
     */
    public function overrideConfig(array $options): void;
}
