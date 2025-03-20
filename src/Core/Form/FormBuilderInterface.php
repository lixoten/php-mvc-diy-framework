<?php

declare(strict_types=1);

namespace Core\Form;

/**
 * Interface for form builders
 */
interface FormBuilderInterface
{
    /**
     * Add a field to the form
     *
     * @param string $name Field name
     * @param array $options Field options
     * @return self
     */
    public function add(string $name, array $options): self;

    /**
     * Get the built form
     *
     * @return FormInterface
     */
    public function getForm(): FormInterface;

    /**
     * Set the form action URL
     *
     * @param string $action
     * @return self
     */
    public function setAction(string $action): self;

    /**
     * Set the form method
     *
     * @param string $method
     * @return self
     */
    public function setMethod(string $method): self;
}
