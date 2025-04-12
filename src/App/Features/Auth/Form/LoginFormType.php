<?php

declare(strict_types=1);

namespace App\Features\Auth\Form;

use Core\Form\FormBuilderInterface;
use Core\Form\FormTypeInterface;

/**
 * Login form type
 */
class LoginFormType implements FormTypeInterface
{
    private array $options = [];
    private LoginFieldRegistry $fieldRegistry;

    /**
     * Constructor
     */
    public function __construct(LoginFieldRegistry $fieldRegistry)
    {
        $this->fieldRegistry = $fieldRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'login_form';
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        // Define default fields
        $fieldNames = ['username', 'password', 'remember'];
        if ($options['captcha_required'] ?? false) {
            $fieldNames[] = 'captcha';
        }

        // Process each field
        foreach ($fieldNames as $name) {
            // Get definition from registry
            $fieldDef = $this->fieldRegistry->get($name) ?? [];

            // Add field to form
            $builder->add($name, $fieldDef);
        }

        // Login form is simple - just use sequential layout
        $builder->setLayout([
            'sequential' => [
                'fields' => $fieldNames
            ]
        ]);
    }
}
