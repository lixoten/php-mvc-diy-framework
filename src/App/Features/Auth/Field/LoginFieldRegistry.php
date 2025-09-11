<?php

declare(strict_types=1);

namespace App\Features\Auth\Field;

use App\Helpers\DebugRt;
use Core\Form\AbstractFormFieldRegistry;
use Core\Form\FormFieldRegistryInterface;
// use Core\List\FieldRegistryInterface;
use Core\Registry\AbstractFieldRegistry;
use Core\Registry\FieldRegistryInterface;
use Core\Services\ConfigService;

/**
 * Registry for login form field definitions
 */
// class LoginFormFieldRegistry extends AbstractFormFieldRegistry
class LoginFieldRegistry extends AbstractFieldRegistry
{
    // public function __construct(?FormFieldRegistryInterface $baseRegistry = null)
    //public function __construct(?FieldRegistryInterface $baseRegistry = null)
    //public function __construct(?FieldRegistryInterface $baseRegistry = null)
    // public function __construct(?\Core\Registry\AbstractFieldRegistry $baseRegistry = null)
    // {
    //     parent::__construct($baseRegistry);
    // }
    /**
     * @var array<string, array>
     */
    protected array $fields;

    // public function __construct(LabelProvider $labelProvider)
    public function __construct(
        ConfigService $configService,
        ?\Core\Registry\AbstractFieldRegistry $baseRegistry = null
    ) {
        $this->configService = $configService;
        parent::__construct($configService, $baseRegistry);

        $this->fields = [
            'id' => [
                'label' => 'Testname2',
                'form' => [
                    'type' => 'text',
                    'required' => true,
                    'minLength' => 3,
                    'maxLength' => 50,
                    'attributes' => [
                        'class' => 'form-control',
                        'id' => 'testname2',
                        'placeholder' => 'testname2 boo',
                        'autofocus' => false
                    ],
                    'validators' => []
                ],
            ],
            'password' => [
                'label' => 'password---local',
                'form' => [
                    'attributes' => [
                        'placeholder' => 'Enter your password22'
                    ],
                    'maxLength' => null,  // Remove maxLength restriction
                    // Remove password complexity requirements for login
                    'validators' => []
                ],
            ],
            'remember' => [
                'label' => 'password--local',
                'form' => [
                    'type' => 'checkbox',
                    'label' => 'Remember me',
                    'required' => false,
                    'value' => false, // set default to false (unchecked)
                    'attributes' => [
                        'class' => 'form-check-input',
                        'id' => 'remember'
                    ]
                ],
            ],
            'usernamexxxx' => [
                'label' => 'posts.author',
                'list' => [
                    'sortable' => true,
                    // 'formatter' => fn($value) => htmlspecialchars($value ?? 'Unknown'),
                    'formatter' => function ($value) {
                        return htmlspecialchars($value ?? 'Unknown');
                    },
                ],
            ],
        ];
    }


    // /**
    //  * Get the username field definition
    //  */
    // public function getTestname2(): array
    // {
    //     return [
    //         'type' => 'text',
    //         'label' => 'Testname2',
    //         'required' => true,
    //         'minLength' => 3,
    //         'maxLength' => 50,
    //         'attributes' => [
    //             'class' => 'form-control',
    //             'id' => 'testname2',
    //             'placeholder' => 'testname2 boo',
    //             'autofocus' => false
    //         ],
    //         'validators' => []
    //     ];
    // }



    // /**
    //  * Get the username field definition
    //  */
    // public function getUsername(): array
    // {
    //     return [
    //         'label' => 'Username or Emailxxx',
    //         'attributes' => [
    //             'placeholder' => 'Enter your username or emailxxx',
    //         ],
    //         'minLength' => null,  // Remove minLength restriction
    //         'maxLength' => null,  // Remove maxLength restriction
    //         // Remove registration-specific validators
    //         'validators' => []
    //     ];
    // }


    // /**
    //  * Get the password field definition
    //  */
    // public function getPassword(): array
    // {
    //     return [
    //         'attributes' => [
    //             'placeholder' => 'Enter your password22'
    //         ],
    //         'maxLength' => null,  // Remove maxLength restriction
    //         // Remove password complexity requirements for login
    //         'validators' => []
    //     ];
    // }

    // /**
    //  * Get the remember me field definition
    //  */
    // public function getRemember(): array
    // {
    //     return [
    //         'type' => 'checkbox',
    //         'label' => 'Remember me',
    //         'required' => false,
    //         'value' => false, // set default to false (unchecked)
    //         'attributes' => [
    //             'class' => 'form-check-input',
    //             'id' => 'remember'
    //         ]
    //     ];
    // }
}
