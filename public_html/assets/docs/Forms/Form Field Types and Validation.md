# Form Field Types and Validation

## Available Field Types

The MVCLixo form system provides a variety of field types to handle different input needs:

### Text Input Types

| Type | Description | HTML Equivalent |
|------|-------------|----------------|
| `text` | Basic text input | `<input type="text">` |
| `email` | Email address input with validation | `<input type="email">` |
| `password` | Password field that masks input | `<input type="password">` |
| `number` | Numeric input with optional min/max | `<input type="number">` |
| `tel` | Telephone number input | `<input type="tel">` |
| `url` | URL address input | `<input type="url">` |
| `search` | Search input field | `<input type="search">` |
| `color` | Color picker | `<input type="color">` |

### Date and Time Types

| Type | Description | HTML Equivalent |
|------|-------------|----------------|
| `date` | Date picker | `<input type="date">` |
| `time` | Time picker | `<input type="time">` |
| `datetime-local` | Date and time picker | `<input type="datetime-local">` |
| `month` | Month and year picker | `<input type="month">` |
| `week` | Week picker | `<input type="week">` |

### Selection Types

| Type | Description | HTML Equivalent |
|------|-------------|----------------|
| `select` | Dropdown select menu | `<select>` |
| `multiselect` | Multiple selection list | `<select multiple>` |
| `radio` | Radio button group | `<input type="radio">` |
| `checkbox` | Single checkbox | `<input type="checkbox">` |
| `checkbox_group` | Group of checkboxes | Multiple `<input type="checkbox">` |

### Complex Types

| Type | Description | HTML Equivalent |
|------|-------------|----------------|
| `textarea` | Multi-line text input | `<textarea>` |
| `file` | File upload | `<input type="file">` |
| `range` | Slider control | `<input type="range">` |
| `hidden` | Hidden input field | `<input type="hidden">` |

## Field Configuration Options

When adding fields to your form, you can configure various options:

```php
$builder->add('email', [
    'type' => 'email',
    'label' => 'Your Email Address',
    'required' => true,
    'attr' => [
        'class' => 'form-control custom-email-input',
        'placeholder' => 'example@domain.com'
    ],
    'help' => 'We will never share your email address',
    'validators' => [
        'email' => [],
        'length' => ['max' => 255]
    ]
]);
```

### Common Field Options

| Option | Description | Default |
|--------|-------------|---------|
| `type` | Field type (text, email, etc.) | `'text'` |
| `label` | Field label text | Field name with spaces |
| `required` | Whether field is required | `false` |
| `attr` | HTML attributes for the field | `[]` |
| `help` | Help text shown below the field | `null` |
| `placeholder` | Placeholder text | `null` |
| `default` | Default value | `null` |
| `choices` | Options for select/radio/checkbox fields | `[]` |
| `validators` | Validation rules | `[]` |
| `error_messages` | Custom error messages | `[]` |
| `wrapper_class` | CSS class for the field wrapper | `null` |
| `label_attr` | HTML attributes for the label | `[]` |

## Selection Field Options

For select, multiselect, radio, and checkbox_group fields:

```php
$builder->add('country', [
    'type' => 'select',
    'label' => 'Country',
    'choices' => [
        'us' => 'United States',
        'ca' => 'Canada',
        'mx' => 'Mexico'
    ],
    'placeholder' => 'Select your country'
]);
```

## File Upload Fields

```php
$builder->add('profileImage', [
    'type' => 'file',
    'label' => 'Profile Image',
    'accept' => 'image/*',
    'max_size' => 2048, // KB
    'validators' => [
        'file' => [
            'max_size' => 2048000, // bytes
            'mime_types' => ['image/jpeg', 'image/png', 'image/gif']
        ]
    ]
]);
```

## Field Validation

### Built-in Validators

| Validator | Description | Options |
|-----------|-------------|---------|
| `required` | Field cannot be empty | `message` |
| `email` | Valid email address | `message` |
| `length` | Text length constraints | `min`, `max`, `message` |
| `range` | Numeric range constraints | `min`, `max`, `message` |
| `regex` | Pattern matching | `pattern`, `message` |
| `choice` | Value must be in choices | `choices`, `message` |
| `file` | File validation | `max_size`, `mime_types`, `message` |
| `callback` | Custom validation function | `callback` |

### Adding Validators

```php
$builder->add('password', [
    'type' => 'password',
    'validators' => [
        'required' => [],
        'length' => [
            'min' => 8,
            'max' => 50,
            'min_message' => 'Password must be at least 8 characters long',
            'max_message' => 'Password cannot exceed 50 characters'
        ],
        'regex' => [
            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
            'message' => 'Password must include uppercase, lowercase, and numbers'
        ]
    ]
]);
```

### Custom Validators

```php
$builder->add('username', [
    'type' => 'text',
    'validators' => [
        'callback' => [
            'callback' => [$this, 'validateUniqueUsername'],
            'message' => 'This username is already taken'
        ]
    ]
]);

// In your form type class:
public function validateUniqueUsername($value, array $context): bool
{
    // Check if username exists in database
    return !$this->userRepository->usernameExists($value);
}
```

## Handling Validation Errors

When form validation fails, error messages are associated with each field:

```php
// In controller
$formHandled = $this->formHandler->handle($form, $request);
if (!$formHandled) {
    // Form has validation errors
    // Errors are automatically stored with each field
}

// In view with inline errors
<?= $form->render() ?>

// In view with summary errors
<?= $formView->errorSummary() ?>
<?= $formView->row('username') ?>
```

## Dependent Fields and Conditional Validation

For advanced validation that depends on other field values:

```php
$builder->add('password', [
    'type' => 'password'
]);

$builder->add('confirmPassword', [
    'type' => 'password',
    'validators' => [
        'callback' => [
            'callback' => function($value, array $context) {
                return $value === $context['data']['password'];
            },
            'message' => 'Passwords do not match'
        ]
    ]
]);
```

## Field Groups and Collections

For repeating field collections or complex field structures:

```php
$builder->addGroup('address', function($group) {
    $group->add('street', ['type' => 'text']);
    $group->add('city', ['type' => 'text']);
    $group->add('state', ['type' => 'select', 'choices' => $this->getStates()]);
    $group->add('zip', ['type' => 'text']);
});
```

Access grouped data with dot notation:

```php
$data = $form->getData();
$street = $data['address']['street'];
