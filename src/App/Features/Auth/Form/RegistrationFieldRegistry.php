<?php

declare(strict_types=1);

namespace App\Features\Auth\Form;

use App\Helpers\DebugRt as Debug;
use Core\Form\FieldRegistryInterface;

/**
 * Registry for registration form field definitions
 */
class RegistrationFieldRegistry implements FieldRegistryInterface
{
    /**
     * Get a field definition by name
     */
    public function get(string $fieldName): ?array
    {
        $method = 'get' . ucfirst($fieldName);

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return null;
    }

    /**
     * Get the username field definition
     */
    public function getUsername(): array
    {
        return [
            'type' => 'text',
            'label' => 'Username',
            'required' => true,
            'minLength' => 3,
            'maxLength' => 50,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'username',
                'placeholder' => 'Choose a unique username',
                'autofocus' => true
            ],
            'validators' => [
                'unique_username' => [
                    'message' => 'This username is already taken.'
                ]
            ]
        ];
    }

    /**
     * Get the email field definition
     */
    public function getEmail(): array
    {
        return [
            'type' => 'email',
            'label' => 'Email Address',
            'required' => true,
            'maxLength' => 255,
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
     * Get the password field definition
     */
    public function getPassword(): array
    {
        return [
            'type' => 'password',
            'label' => 'Password',
            'required' => true,
            'minLength' => 4,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'password',
                'placeholder' => 'Choose a strong password'
            ],
            'validators' => [ // Important!!! // TODONOW
                'regex' => [
                    'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{4,}$/',
                    'message' => 'Password must include at least one uppercase letter, one lowercase letter, ' .
                                'one number, and one special character...'
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
