<?php

declare(strict_types=1);

namespace App\Features\Stores\Profile\Form;

use App\Helpers\DebugRt;
use Core\Form\AbstractFieldRegistry;

/**
 * Registry for profile form field definitions
 */
class ProfileFieldRegistry extends AbstractFieldRegistry
{
    /**
     * Get the name field definition
     */
    public function getName(): array
    {
        return [
            'type' => 'text',
            'label' => 'Store Name',
            'required' => true,
            'minLength' => 2,
            'maxLength' => 100,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'name',
                'placeholder' => 'Enter a store title'
            ]
        ];
    }

        /**
     * Get the Description field definition
     */
    public function getDescription(): array
    {
        return [
            'type' => 'textarea',
            'label' => 'Description',
            'required' => true,
            'minLength' => 10,
            'maxLength' => 2000,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'content',
                'placeholder' => 'Enter store description',
                'rows' => '6'
            ]
        ];
    }
}
