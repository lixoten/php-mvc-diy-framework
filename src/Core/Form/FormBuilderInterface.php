<?php

declare(strict_types=1);

namespace Core\Form;

use Core\Form\Field\FieldInterface;

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
    public function add(string $name, array $options = []): self;

    /**
     * Add an existing field to the form
     *
     * @param FieldInterface $field
     * @return self
     */
    public function addField(FieldInterface $field): self;

    /**
     * Get the built form
     *
     * @return FormInterface
     */
    public function getForm(): FormInterface;

    /**
     * Set form action URL
     *
     * @param string $action
     * @return self
     */
    public function setAction(string $action): self;

    /**
     * Set form method
     *
     * @param string $method
     * @return self
     */
    public function setMethod(string $method): self;


    /**
     * Set form layout configuration
     *
     * @param array $layout Layout configuration
     * @return self
     */
    public function setLayout(array $layout): self;
}
