<?php

declare(strict_types=1);

namespace App\Features\Contact\Form;

use App\Helpers\DebugRt;
use Core\Form\AbstractFieldRegistry;
use Core\Form\FieldRegistryInterface;

/**
 * Registry for contact form field definitions
 */
class ContactFieldRegistry extends AbstractFieldRegistry
{
    public function __construct(?FieldRegistryInterface $baseRegistry = null)
    {
        parent::__construct($baseRegistry);
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
            'minlength' => 2,
            'maxlength' => 100,
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
            'maxlength' => 255,
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
            'minlength' => 10,
            'maxlength' => 200,
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
            'minlength' => 10,
            'maxlength' => 2000,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'message',
                'placeholder' => 'Enter your message',
                'rows' => '6'
            ]
        ];
    }
}
