<?php

declare(strict_types=1);

namespace Core\Form;

/**
 * Interface for form factory
 */
interface FormFactoryInterface
{
    /**
     * Create a form using the provided form type and data
     *
     * @param FormTypeInterface $formType The form type defining the fields
     * @param array $data Initial data to populate the form
     * @return FormInterface The created form
     */
    public function create(FormTypeInterface $formType, array $data = [], array $options = []): FormInterface;
}
