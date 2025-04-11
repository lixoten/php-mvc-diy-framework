# Advanced Form System Examples

## Combining Features for Complex Forms

Let's look at some advanced examples that combine various features of the form system:

## 1. Multi-Step Registration Form

A multi-step registration form with validation between steps:

```php
// RegistrationController.php
public function registrationStep1Action(ServerRequestInterface $request): ResponseInterface
{
    // Create step 1 form
    $form = $this->formFactory->create(
        $this->accountDetailsFormType,
        $this->session->get('registration_data', []),
        [
            'css_theme_class' => 'form-themed',
            'error_display' => 'inline'
        ]
    );
    
    // Handle submission
    $formHandled = $this->formHandler->handle($form, $request);
    
    if ($formHandled) {
        // Store data in session
        $this->session->set('registration_data', $form->getData());
        
        // Move to next step
        return $this->redirect('/registration/step2');
    }
    
    // Render step 1
    return $this->view('registration/step1', [
        'form' => $form,
        'currentStep' => 1,
        'totalSteps' => 3
    ]);
}

// Step 2 and 3 would follow similar patterns...

// Final completion step
public function registrationCompleteAction(ServerRequestInterface $request): ResponseInterface
{
    // Retrieve all data from session
    $data = $this->session->get('registration_data', []);
    
    if (empty($data)) {
        return $this->redirect('/registration/step1');
    }
    
    // Process registration
    $success = $this->userService->registerUser($data);
    
    if ($success) {
        // Clear session data
        $this->session->remove('registration_data');
        
        // Flash success message
        $this->flash->add('Your account has been created successfully!', 'success');
        
        // Redirect to login
        return $this->redirect('/login');
    }
    
    // Handle failure
    $this->flash->add('There was a problem creating your account.', 'error');
    return $this->redirect('/registration/step1');
}
```

## 2. Dynamic Form with Conditional Logic

A form that changes based on user selections:

```php
// In ProductFormType.php
public function buildForm(FormBuilderInterface $builder, array $options = []): void
{
    $builder->add('productType', [
        'type' => 'select',
        'choices' => [
            'physical' => 'Physical Product',
            'digital' => 'Digital Product',
            'subscription' => 'Subscription'
        ],
        'attr' => [
            'data-conditional' => 'true',
            'class' => 'form-control form-select'
        ]
    ]);
    
    $builder->add('name', [
        'type' => 'text',
        'required' => true
    ]);
    
    $builder->add('price', [
        'type' => 'number',
        'attr' => ['step' => '0.01']
    ]);
    
    // Physical product fields
    $builder->add('weight', [
        'type' => 'number',
        'attr' => [
            'data-condition-field' => 'productType',
            'data-condition-value' => 'physical'
        ]
    ]);
    
    $builder->add('dimensions', [
        'type' => 'text',
        'attr' => [
            'data-condition-field' => 'productType',
            'data-condition-value' => 'physical',
            'placeholder' => 'LxWxH in inches'
        ]
    ]);
    
    // Digital product fields
    $builder->add('downloadLink', [
        'type' => 'url',
        'attr' => [
            'data-condition-field' => 'productType',
            'data-condition-value' => 'digital'
        ]
    ]);
    
    $builder->add('fileSize', [
        'type' => 'text',
        'attr' => [
            'data-condition-field' => 'productType',
            'data-condition-value' => 'digital'
        ]
    ]);
    
    // Subscription fields
    $builder->add('billingCycle', [
        'type' => 'select',
        'choices' => [
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'annual' => 'Annual'
        ],
        'attr' => [
            'data-condition-field' => 'productType',
            'data-condition-value' => 'subscription'
        ]
    ]);
}

// In controller
public function createProductAction(ServerRequestInterface $request): ResponseInterface
{
    $form = $this->formFactory->create(
        $this->productFormType,
        [],
        [
            'layout_type' => 'sections',
            'layout' => [
                'sections' => [
                    [
                        'type' => 'header',
                        'title' => 'Basic Information'
                    ],
                    [
                        'type' => 'fields',
                        'fields' => ['productType', 'name', 'price']
                    ],
                    [
                        'type' => 'header',
                        'title' => 'Product Details',
                        'attr' => ['id' => 'details-header']
                    ],
                    [
                        'type' => 'fields',
                        'fields' => ['weight', 'dimensions'],
                        'condition' => [
                            'field' => 'productType',
                            'value' => 'physical'
                        ]
                    ],
                    [
                        'type' => 'fields',
                        'fields' => ['downloadLink', 'fileSize'],
                        'condition' => [
                            'field' => 'productType',
                            'value' => 'digital'
                        ]
                    ],
                    [
                        'type' => 'fields',
                        'fields' => ['billingCycle'],
                        'condition' => [
                            'field' => 'productType',
                            'value' => 'subscription'
                        ]
                    ]
                ]
            ],
            'js_validation' => true,
            'js_conditional_fields' => true
        ]
    );
    
    // Handle form submission and rendering...
}
```

## 3. Form with Custom Field Types and Validators

```php
// In custom form type
public function buildForm(FormBuilderInterface $builder, array $options = []): void
{
    // Custom validator function
    $validatePassword = function($value, array $context) {
        // At least 8 chars, uppercase, lowercase, number
        if (strlen($value) < 8) {
            return false;
        }
        
        if (!preg_match('/[A-Z]/', $value)) {
            return false;
        }
        
        if (!preg_match('/[a-z]/', $value)) {
            return false;
        }
        
        if (!preg_match('/[0-9]/', $value)) {
            return false;
        }
        
        return true;
    };
    
    // Custom field type
    $builder->add('password', [
        'type' => 'password',
        'validators' => [
            'required' => [],
            'callback' => [
                'callback' => $validatePassword,
                'message' => 'Password must be at least 8 characters and include uppercase, lowercase, and numbers.'
            ]
        ],
        'attr' => [
            'class' => 'password-field',
            'data-strength-meter' => 'true'
        ]
    ]);
    
    // Password confirmation with equality validation
    $builder->add('passwordConfirm', [
        'type' => 'password',
        'validators' => [
            'required' => [],
            'callback' => [
                'callback' => function($value, array $context) {
                    return $value === $context['data']['password'];
                },
                'message' => 'Passwords must match.'
            ]
        ]
    ]);
    
    // Custom tag input field with data transformation
    $builder->add('tags', [
        'type' => 'text',
        'attr' => [
            'class' => 'tag-input',
            'data-role' => 'tagsinput'
        ],
        'data_transformer' => [
            'toView' => function($data) {
                // Transform array to comma-separated string
                if (is_array($data)) {
                    return implode(',', $data);
                }
                return '';
            },
            'fromView' => function($data) {
                // Transform comma-separated string to array
                if (is_string($data)) {
                    $tags = explode(',', $data);
                    return array_map('trim', $tags);
                }
                return [];
            }
        ]
    ]);
}
```

## 4. Form with File Uploads and Preview

```php
// In controller
public function profileAction(ServerRequestInterface $request): ResponseInterface
{
    $userData = $this->userService->getCurrentUserData();
    
    $form = $this->formFactory->create(
        $this->profileFormType,
        $userData,
        [
            'layout_type' => 'fieldsets',
            'multipart' => true,  // Required for file uploads
            'layout' => [
                'fieldsets' => [
                    'profile' => [
                        'legend' => 'Profile Details',
                        'fields' => ['name', 'email', 'bio']
                    ],
                    'avatar' => [
                        'legend' => 'Profile Picture',
                        'fields' => ['avatar', 'removeAvatar']
                    ],
                    'preferences' => [
                        'legend' => 'Preferences',
                        'fields' => ['receiveEmails', 'theme', 'timezone']
                    ]
                ]
            ]
        ]
    );
    
    $formHandled = $this->formHandler->handle($form, $request);
    
    if ($formHandled) {
        $data = $form->getData();
        
        // Process avatar upload if present
        if (!empty($data['avatar']) && $data['avatar']->isValid()) {
            // Process file upload
            $fileName = $this->fileUploader->upload($data['avatar'], 'avatars');
            $data['avatarPath'] = $fileName;
        } elseif (!empty($data['removeAvatar']) && $data['removeAvatar']) {
            // Remove avatar
            $this->fileUploader->remove($userData['avatarPath'] ?? null);
            $data['avatarPath'] = null;
        } else {
            // Keep existing avatar
            $data['avatarPath'] = $userData['avatarPath'] ?? null;
        }
        
        // Remove file objects before saving to database
        unset($data['avatar'], $data['removeAvatar']);
        
        // Update user data
        $this->userService->updateUser($data);
        
        $this->flash->add('Profile updated successfully!', 'success');
        return $this->redirect('/profile');
    }
    
    // Create FormView with avatar preview
    $formView = new \Core\Form\View\FormView($form);
    
    return $this->view('users/profile', [
        'formView' => $formView,
        'avatarPath' => $userData['avatarPath'] ?? null
    ]);
}

// In form type
public function buildForm(FormBuilderInterface $builder, array $options = []): void
{
    // Regular fields...
    
    // File upload field
    $builder->add('avatar', [
        'type' => 'file',
        'label' => 'Profile Picture',
        'required' => false,
        'attr' => [
            'accept' => 'image/*',
            'data-preview' => 'avatar-preview'
        ],
        'validators' => [
            'file' => [
                'max_size' => 2048000,  // 2MB
                'mime_types' => ['image/jpeg', 'image/png', 'image/gif'],
                'message' => 'Please upload a valid image (JPG, PNG or GIF) no larger than 2MB.'
            ]
        ]
    ]);
    
    // Checkbox to remove existing avatar
    $builder->add('removeAvatar', [
        'type' => 'checkbox',
        'label' => 'Remove existing profile picture',
        'required' => false
    ]);
}

// In view template
<div id="avatar-preview" class="mb-3">
    <?php if (!empty($avatarPath)): ?>
        <img src="<?= $this->escape('/uploads/avatars/' . $avatarPath) ?>" 
             alt="Current profile picture" class="img-thumbnail mb-2">
    <?php endif; ?>
</div>

<?= $formView->row('avatar') ?>
<?= $formView->row('removeAvatar') ?>
```

These advanced examples demonstrate how to leverage the full power of the MVCLixo form system for complex use cases that go beyond basic forms.

Similar code found with 1 license type