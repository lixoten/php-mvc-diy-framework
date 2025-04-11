<?php

declare(strict_types=1);

namespace App\Features\Auth\Form;

use Core\Form\AbstractFormType;
use Core\Form\FormBuilderInterface;

/**
 * Registration form type
 */
class RegistrationFormType extends AbstractFormType
{
    private RegistrationFieldRegistry $fieldRegistry;

    /**
     * Constructor
     */
    public function __construct(RegistrationFieldRegistry $fieldRegistry)
    {
        $this->fieldRegistry = $fieldRegistry;
    }

    /**
     * Override the default name
     */
    public function getName(): string
    {
        return 'register_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        // Define registration fields
        $fieldNames = ['username', 'email', 'password', 'confirm_password'];

        // Process each field
        foreach ($fieldNames as $name) {
            // If confirm_password, use confirmPassword method
            $registryName = ($name === 'confirm_password') ? 'confirmPassword' : $name;
            $fieldDef = $this->fieldRegistry->get($registryName) ?? [];

            // Add field to form
            $builder->add($name, $fieldDef);
        }

        // Sequential layout
        $builder->setLayout([
            'sequential' => [
                'fields' => $fieldNames
            ]
        ]);
    }

    /**
     * Provide default data for the form
     */
    public function getDefaultData(): array
    {
        return [
            'username' => '',
            'email' => '',
            'password' => '',
            'confirm_password' => ''
        ];
    }
}
