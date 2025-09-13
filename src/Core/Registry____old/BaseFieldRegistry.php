<?php

declare(strict_types=1);

namespace Core\Registry;

use App\Helpers\DebugRt;
use Core\Form\AbstractFormFieldRegistry;

/**
 * Registry for registration form field definitions
 */
// class BaseFieldRegistry extends AbstractFormFieldRegistry
class BaseFieldRegistry extends AbstractFieldRegistry
{

    protected string $wth = "wtfxxxx";
    protected array $fields;

    public function __construct()
    {
        $this->wth = "wtf";

        DebugRt::j('1', '', 'BOOM on Config File');
        $this->fields = [
            'id' => [
                'label' => 'acrID',
                'list' => [
                    'sortable' => true,
                    'formatter' => null,
                ],
            ],
            'user_id' => [
                'label' => 'acrUserId',
                'list' => [
                    'sortable' => true,
                    'formatter' => null,
                ],
            ],
            'title' => [
                'label' => 'acrTitle',
                'list' => [
                    'sortable' => true,
                    'formatter' => function ($value) {
                        return htmlspecialchars((string)$value ?? '');
                    },
                ],
                'form' => [
                    'type' => 'text',
                    'required' => true,
                    'minlength' => 2,
                    'maxlength' => 10,
                    'attributes' => [
                        'class' => 'form-control',
                        'id' => 'title',
                        'placeholder' => 'Enter a post titlezzzz'
                    ]
                ]
            ],
            'name' => [
                'label' => 'acrName',
                'list' => [
                    'sortable' => true,
                    'formatter' => function ($value) {
                        return htmlspecialchars((string)$value ?? '');
                    },
                ],
            ],
            'status' => [
                'label' => 'acrStatus',
                'list' => [
                    'sortable' => true,
                    'formatter' => function ($value) {
                        if ($value === null || $value === '') {
                            return '';
                        }
                        $statusClass = ($value == 'Published' || $value == 'Active' || $value === true || $value === 1) ? 'success' : 'warning';
                        return '<span class="badge bg-' . $statusClass . '">' . htmlspecialchars((string)$value) . '</span>';
                    },
                ],
            ],
            'created_at' => [
                'label' => 'posts.created_at---BaseField',
                'list' => [
                    'sortable' => true,
                    'formatter' => function ($value) {
                        if ($value instanceof \DateTimeInterface) {
                            return $value->format('Y-m-d H:i:s');
                        }
                        if (is_string($value) && strtotime($value) !== false) {
                            $dateTime = new \DateTime($value);
                            return $dateTime->format('Y-m-d H:i:s');
                        }
                        return htmlspecialchars((string)$value);
                    },
                ],
            ],
            'updated_at' => [
                'label' => 'acrUpdated At',
                'list' => [
                    'sortable' => true,
                    'formatter' => function ($value) {
                        if ($value instanceof \DateTimeInterface) {
                            return $value->format('Y-m-d H:i:s');
                        }
                        return htmlspecialchars((string)$value);
                    },
                ],
            ],
            'testname1' => [
                'type' => 'text',
                'label' => 'Testname1',
                'required' => true,
                'minlength' => 3,
                'maxlength' => 50,
                'attributes' => [
                    'class' => 'form-control',
                    'id' => 'testname1',
                    'placeholder' => 'Testname1 boo',
                    'autofocus' => false
                ],
                'validators' => []
            ],
            'username' => [
                'label' => 'Username.username222eeeee',
                // 'label' => 'Username or Emailxxx',
                'form' => [
                    'type' => 'text',
                    'attributes' => [
                        'placeholder' => 'Enter your username or emailxxx',
                    ],
                    'minlength' => null,  // Remove minlength restriction
                    'maxlength' => null,  // Remove maxlength restriction
                    // Remove registration-specific validators
                    'validators' => []
                ]
            ],
            'usernamexxx' => [
                'label' => 'Username.username',
                'list' => [
                    'sortable' => true,
                    'formatter' => null,
                ],
                'form' => [
                    'type' => 'text',
                    'required' => true,
                    'minlength' => 3,
                    'maxlength' => 50,
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
                    ],
                ]
            ],
            'password' => [
                'label' => 'Password',
                'form' => [
                    'type' => 'password',
                    'required' => true,
                    'minlength' => 4,
                    'attributes' => [
                        'class' => 'form-control',
                        'id' => 'password',
                        'placeholder' => 'Choose a strong password'
                    ],
                    'validators' => [
                        'regex' => [
                            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{4,}$/',
                            'message' => 'Password must include at least one uppercase letter, one lowercase letter, one number, and one special character...'
                        ]
                    ]
                ]
            ],
            'captcha' => [
                'label' => 'xxxSecurity Verification',
                'form' => [
                    'type' => 'captcha',
                    'required' => true,
                    'help_text' => 'Please complete the security check',
                    'attributes' => [
                        'class' => 'g-recaptcha'
                    ],
                    'options' => [
                        'theme' => 'light',
                        'size' => 'normal'
                    ],
                    'validators' => [
                        'captcha' => [
                            'message' => 'xxxFailed security verification. Please try again.'
                        ]
                    ]
                ]
            ],
        ];
    }

    // /**
    //  * Get a field definition by name
    //  */
    // public function get(string $fieldName): ?array
    // {
    //     $method = 'get' . ucfirst($fieldName);

    //     if (method_exists($this, $method)) {
    //         return $this->$method();
    //     }

    //     return null;
    // }




    /**
     * Provides definitions for common fields.
     * Override or extend in child classes if needed.
     *
     * @param string $fieldName
     * @return array|null
     */
    public function getComxxxxxxxmonFieldxxxxxxxxxxxxxxxxxxx(string $fieldName): ?array
    {
        // TAG: albumtag1
        switch ($fieldName) {
            case 'id':
                return [
                    'label' => 'acrID',
                    'list' => [
                        'sortable' => true,
                        'formatter' => null, // Default, no formatting
                        ]
                    ];
            case 'user_id':
                return [
                    'label' => 'acrUserId',
                    'list' => [
                        'sortable' => true,
                        'formatter' => null, // Default, no formatting
                    ]
                ];
            case 'username':
                return [
                    'label' => 'acrCreated by',
                    'list' => [
                        'sortable' => true,
                        //'formatter' => null, // Default, no formatting
                        'formatter' => function ($value) {
                            return htmlspecialchars("acrFoi " . $value ?? 'Unknown');
                        },
                    ]
                ];
            case 'titlxe':
                return [
                    'label' => 'acrTitle',
                    'list' => [
                        'sortable' => true,
                        'formatter' => function ($value) {
                            return htmlspecialchars((string)$value ?? '');
                        },
                    ]
                ];
            case 'name':
                return [
                    'label' => 'acrName',
                    'list' => [
                        'sortable' => true,
                        'formatter' => function ($value) {
                            return htmlspecialchars((string)$value ?? '');
                        },
                    ]
                ];
            case 'status':
                return [
                    'label' => 'acrStatus',
                    'list' => [
                        'sortable' => true,
                        'formatter' => function ($value) {
                            // Basic status badge - adjust as needed
                            if ($value === null || $value === '') {
                                return '';
                            }
                            $statusClass = ($value == 'Published' || $value == 'Active' || $value === true || $value === 1) ? 'success' : 'warning';
                            return '<span class="badge bg-' . $statusClass . '">' . htmlspecialchars((string)$value) . '</span>';
                        },
                    ]
                ];
            case 'created_at':
                return [
                    'label' => 'acrCreated At',
                    'list' => [
                        'sortable' => true,
                        'formatter' => function ($value) {
                            // Basic date formatting - adjust as needed
                            if ($value instanceof \DateTimeInterface) {
                                return $value->format('Y-m-d H:i:s');
                            }
                            // Try to convert string dates to DateTime objects
                            if (is_string($value) && strtotime($value) !== false) {
                                $dateTime = new \DateTime($value);
                                return $dateTime->format('Y-m-d H:i:s'); // Format however you want
                            }
                            return htmlspecialchars((string)$value);
                        },
                    ]
                    // TODO this formatter requires us to Converting dates when you retrieve them.
                    // updated_at formatter only handles DateTime objects
                    // 'formatter' => function ($value) {
                    //     if ($value instanceof \DateTimeInterface) {
                    //         return $value->format('Y-m-d H:i:s');
                    //     }
                    //     return htmlspecialchars((string)$value);
                    // }
                    // TODO another example...this formatter requires us to Converting dates when you retrieve them.
                    // 'formatter' => function ($value) {
                    //     // Since we know it's a DateTime object now
                    //     return $value instanceof \DateTimeInterface
                    //         ? $value->format('Y-m-d H:i:s')
                    //         : htmlspecialchars((string)$value);
                    // },
                ];
            case 'updated_at':
                return [
                    'label' => 'acrUpdated At',
                    'list' => [
                        'sortable' => true,
                        'formatter' => function ($value) {
                            // Basic date formatting - adjust as needed
                            if ($value instanceof \DateTimeInterface) {
                                return $value->format('Y-m-d H:i:s');
                            }
                            return htmlspecialchars((string)$value);
                        },
                    ]
                ];
            default:
                return null;
        }
    }



    /**
     * Get the username field definition
     */
    public function getTestname1(): array
    {
        return [
            'type' => 'text',
            'label' => 'Testname1',
            'required' => true,
            'minlength' => 3,
            'maxlength' => 50,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'testname1',
                'placeholder' => 'Testname1 boo',
                'autofocus' => false
            ],
            'validators' => []
        ];
    }



    /**
     * Get the username field definition
     */
    public function getUsernamexxxxxx(): array
    {
        return [
            'type' => 'text',
            'label' => 'Username.usernamezzzzzzzzGET',
            'required' => true,
            'minlength' => 3,
            'maxlength' => 50,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'username',
                'placeholder' => 'Choose a unique username',
                'autofocus' => true
            ],
            'validators' => [
                // 'unique_username' => [
                //     'message' => 'This username is already taken.'
                // ]
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
            'minlength' => 4,
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
     * Get the CAPTCHA field definition
     */
    public function getCaptcha(): array
    {
        return [
            'type' => 'captcha',
            'label' => 'xxxSecurity Verification',
            'required' => true,
            'help_text' => 'Please complete the security check',
            'attributes' => [
                'class' => 'g-recaptcha'
            ],
            'options' => [
                'theme' => 'light',
                'size' => 'normal'
            ],
            'validators' => [
                'captcha' => [
                    'message' => 'xxxFailed security verification. Please try again.'
                ]
            ]
        ];
    }
}
