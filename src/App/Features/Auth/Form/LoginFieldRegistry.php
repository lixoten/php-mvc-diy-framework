<?php

declare(strict_types=1);

namespace App\Features\Auth\Form;

use Core\Form\FieldRegistryInterface;

/**
 * Registry for login form field definitions
 */
class LoginFieldRegistry implements FieldRegistryInterface
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
            'label' => 'Username or Email',
            'required' => true,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'username',
                'placeholder' => 'Enter your username or email',
                'autofocus' => true
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
            'attributes' => [
                'class' => 'form-control',
                'id' => 'password',
                'placeholder' => 'Enter your password'
            ]
        ];
    }

    /**
     * Get the remember me field definition
     */
    public function getRemember(): array
    {
        return [
            'type' => 'checkbox',
            'label' => 'Remember me',
            'required' => false,
            'attributes' => [
                'class' => 'form-check-input',
                'id' => 'remember'
            ]
        ];
    }
}
