<?php

declare(strict_types=1);

namespace App\Features\Testy\Form;

use App\Core\Form\FieldRegistryInterface;

/**
 * Registry for user-related field definitions
 */
class UserFieldRegistry implements FieldRegistryInterface
{
    /**
     * Get a field definition by name
     *
     * @param string $fieldName
     * @return array|null
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
     * Get the name field definition
     */
    public function getName(): array
    {
        return [
            'type' => 'text',
            'label' => 'Name',
            'required' => true,
            'minLength' => 2,
            'maxLength' => 100,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'name',
                'placeholder' => 'Enter your name'
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
                'placeholder' => 'Enter your email address'
            ]
        ];
    }

    /**
     * Get the address field definition
     */
    public function getAddress(): array
    {
        return [
            'type' => 'text',
            'label' => 'Address',
            'required' => true,
            'maxLength' => 200,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'address',
                'placeholder' => 'Enter your address'
            ]
        ];
    }

    /**
     * Get the phone field definition
     */
    public function getPhone(): array
    {
        return [
            'type' => 'tel',
            'label' => 'Phone Number',
            'required' => false,
            'maxLength' => 20,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'phone',
                'placeholder' => 'Enter your phone number'
            ]
        ];
    }
}
