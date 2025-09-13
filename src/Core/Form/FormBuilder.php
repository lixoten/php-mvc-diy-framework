<?php

declare(strict_types=1);

namespace Core\Form;

use Core\Form\Field\FieldInterface;
use Core\Form\Field\Type\FieldTypeRegistry;

/**
 * Default form builder implementation
 */
class FormBuilder implements FormBuilderInterface
{
    private FormInterface $form;
    private FieldTypeRegistry $fieldTypeRegistry;

    /**
     * Constructor
     *
     * @param FormInterface $form
     * @param FieldTypeRegistry $fieldTypeRegistry
     */
    public function __construct(
        FormInterface $form,
        FieldTypeRegistry $fieldTypeRegistry
    ) {
        $this->form = $form;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
    }


    /**
     * {@inheritdoc}
     */
    public function setRenderOptions(array $renderOptions): void
    {
        $this->form->setRenderOptions($renderOptions);
    }


    /**
     * {@inheritdoc}
     */
    public function add(string $name, array $options = []): self
    {
        // Determine field type
        $type = $options['attributes']['type'] ?? 'text';

        // Create field using registry
        $field = $this->fieldTypeRegistry->createField($name, $type, $options);

        // Add field to form
        $this->form->addField($field);

        return $this;
    }


    /**
     * Add an existing field to the form
     *
     * @param FieldInterface $field
     * @return self
     */
    public function addField(FieldInterface $field): self
    {
        $this->form->addField($field);
        return $this;
    }


    /**
     * Set form action
     *
     * @param string $action
     * @return self
     */
    public function setAction(string $action): self
    {
        $this->form->setAttribute('action', $action);
        return $this;
    }


    /**
     * Set form method
     *
     * @param string $method
     * @return self
     */
    public function setMethod(string $method): self
    {
        $this->form->setAttribute('method', $method);
        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }


    /**
     * Set form layout configuration
     *
     * @param array $layout Layout configuration
     * @return self
     */
    public function setLayout(array $layout): self
    {
        $this->form->setLayout($layout);
        return $this;
    }
}
