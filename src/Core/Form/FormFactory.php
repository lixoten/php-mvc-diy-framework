<?php

declare(strict_types=1);

namespace Core\Form;

/**
 * Form factory that creates forms from form types
 */
class FormFactory implements FormFactoryInterface
{
    private FormBuilderInterface $formBuilder;

    /**
     * Constructor
     *
     * @param FormBuilderInterface $formBuilder
     */
    public function __construct(FormBuilderInterface $formBuilder)
    {
        $this->formBuilder = $formBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function create(FormTypeInterface $formType, $data = null): FormInterface
    {
        // Create a new form builder
        $builder = clone $this->formBuilder;

        // Get fields from the form type
        $fields = $formType->getFields();

        // // DEBUG: Log field types
        // error_log("FormType class: " . get_class($formType));
        // // Check if fields is actually an array
        // if (!is_array($fields)) {
        //     error_log("ERROR: FormType::getFields() did not return an array, got: " . gettype($fields));
        //     throw new \RuntimeException("FormType::getFields() must return an array, got: " . gettype($fields));
        // }

        // Add each field to the form
        foreach ($fields as $name => $field) {
            $builder->add($name, $field);
        }

        // Build the form
        $form = $builder->getForm();

        // Set initial data if provided
        if ($data !== null) {
            $form->setData($data);
        }

        return $form;
    }
}
