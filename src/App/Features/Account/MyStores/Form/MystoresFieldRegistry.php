<?php

declare(strict_types=1);

namespace App\Features\Account\Mystores\Form;

use App\Helpers\DebugRt;
use Core\Form\AbstractFieldRegistry;

/**
 * Registry for mystores form field definitions
 */
class MystoresFieldRegistry extends AbstractFieldRegistry
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
            'minlength' => 2,
            'maxlength' => 100,
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
            'minlength' => 10,
            'maxlength' => 2000,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'content',
                'placeholder' => 'Enter store description',
                'rows' => '6'
            ]
        ];
    }
}
