<?php

declare(strict_types=1);

namespace App\Features\Testy\Form;

use Core\Form\FieldRegistryInterface;

/**
 * Registry for contact form field definitions
 */
class ContactFieldRegistry implements FieldRegistryInterface
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
     * Get the name field definition
     */
    public function getName(): array
    {
        return [
            'type' => 'text',
            'label' => 'Your Name',
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
     * Get the subject field definition
     */
    public function getSubject(): array
    {
        return [
            'type' => 'text',
            'label' => 'Subject',
            'required' => true,
            'maxLength' => 200,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'subject',
                'placeholder' => 'Enter message subject'
            ]
        ];
    }

    /**
     * Get the message field definition
     */
    public function getMessage(): array
    {
        return [
            'type' => 'textarea',
            'label' => 'Message',
            'required' => true,
            'minLength' => 10,
            'maxLength' => 2000,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'message',
                'placeholder' => 'Enter your message',
                'rows' => '6'
            ]
        ];
    }


        /**
     * Get the message field definition
     */
    public function getMessage2(): array
    {
        return [
            'type' => 'textarea',
            'label' => 'Message2',
            'required' => true,
            'minLength' => 2,
            'maxLength' => 2000,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'message',
                'placeholder' => 'Enter your message2',
                'rows' => '6'
            ]
        ];
    }
}
