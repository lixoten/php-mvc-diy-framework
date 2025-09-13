<?php

declare(strict_types=1);

// namespace App\Features\Albums\Form;
// namespace App\Features\Albums\Form;
namespace App\Features\Albums\Form;

use App\Helpers\DebugRt;
use Core\Form\AbstractFieldRegistry;

/**
 * Registry for albums form field definitions
 */
class AlbumsFieldRegistry extends AbstractFieldRegistry
{
    /**
     * Get the message field definition
     */
    public function getDecription(): array
    {
        return [
            'type' => 'textarea',
            'label' => 'Decription',
            'required' => true,
            'minlength' => 10,
            'maxlength' => 2000,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'description',
                'placeholder' => 'Enter album description',
                'rows' => '6'
            ]
        ];
    }
}
