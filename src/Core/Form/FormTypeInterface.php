<?php

declare(strict_types=1);

namespace Core\Form;

/**
 * Interface for form types
 */
interface FormTypeInterface
{
    /**
     * Get form name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get form options
     *
     * @return array
     */
    public function getOptions(): array;

    /**
     * Build the form
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options = []): void;
}
