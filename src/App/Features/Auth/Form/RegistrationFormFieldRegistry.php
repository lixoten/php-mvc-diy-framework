<?php

declare(strict_types=1);

namespace App\Features\Auth\Form;

use App\Helpers\DebugRt;
use Core\Form\AbstractFormFieldRegistry;

/**
 * Registry for registration form field definitions
 */
class RegistrationFormFieldRegistry extends AbstractFormFieldRegistry
{
    /**
     * Get the email field definition
     */
    public function getEmail(): array
    {
        return [
            'type' => 'email',
            'label' => 'Email Address',
            'required' => true,
            'maxlength' => 255,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'email',
                'placeholder' => 'Your email address'
            ],
            'validators' => [
                'unique_email' => [
                    'message' => 'This email address is already registered.'
                ]
            ]
        ];
    }



    /**
     * Get the confirm password field definition
     */
    public function getConfirmPassword(): array
    {
        return [
            'type' => 'password',
            'label' => 'Confirm Password',
            'required' => true,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'confirm_password',
                'placeholder' => 'Confirm your password'
            ],
            // No validators section - validation happens in the controller
        ];
    }
}
