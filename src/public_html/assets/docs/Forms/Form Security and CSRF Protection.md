# Form Security and CSRF Protection

## CSRF Protection

Cross-Site Request Forgery (CSRF) protection is built into the MVCLixo form system to prevent malicious attacks.

### How CSRF Protection Works

1. A unique token is generated for each form
2. The token is included as a hidden field
3. When the form is submitted, the token is validated
4. If the token is invalid or missing, the form submission is rejected

### Automatic CSRF Protection

CSRF protection is enabled by default for all forms:

```php
// The form factory automatically adds CSRF protection
$form = $this->formFactory->create($this->contactFormType);
```

The hidden CSRF token field will be included automatically in your rendered form:

```html
<input type="hidden" name="csrf_token" value="c7ad44cbad762a5da0a452f9e854fdc1e0e7a52a38015f23f3eab1d80b931dd472634dfac71cd34ebc35d16ab7fb8a90c81f975113d6c7538dc69dd8de9077ec">
```

### Customizing CSRF Protection

You can customize CSRF behavior with these options:

```php
$form = $this->formFactory->create(
    $this->contactFormType,
    [],
    [
        'csrf_protection' => true,        // Enable/disable protection
        'csrf_field_name' => 'my_token',  // Custom field name
        'csrf_token_id' => 'contact_form' // Token ID for this form
    ]
);
```

### CSRF in AJAX Forms

For AJAX forms, include the CSRF token in your request:

```javascript
// JavaScript example
const token = document.querySelector('input[name="csrf_token"]').value;

fetch('/api/contact', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': token
    },
    body: JSON.stringify(formData)
});
```

## Input Sanitization

The form system automatically sanitizes user input to prevent XSS attacks and other security issues.

### Default Sanitization

By default, the following sanitization is applied:

- HTML tags are stripped from text inputs
- Special characters are escaped
- Whitespace is trimmed

### Custom Sanitization

You can define custom sanitization for specific fields:

```php
$builder->add('bio', [
    'type' => 'textarea',
    'sanitize' => function($value) {
        // Allow basic HTML but remove scripts
        return strip_tags($value, '<p><br><strong><em><ul><li>');
    }
]);
```

### Raw Input Fields

For fields where you need to preserve the exact input:

```php
$builder->add('secureCode', [
    'type' => 'text',
    'raw' => true  // Disable automatic sanitization
]);
```

## File Upload Security

File uploads require special security considerations:

```php
$builder->add('document', [
    'type' => 'file',
    'max_size' => 5120,  // 5MB in KB
    'allowed_types' => ['application/pdf', 'image/jpeg', 'image/png'],
    'validators' => [
        'file' => [
            'max_size' => 5120000,  // 5MB in bytes
            'mime_types' => ['application/pdf', 'image/jpeg', 'image/png'],
            'extension_whitelist' => ['pdf', 'jpg', 'jpeg', 'png']
        ]
    ]
]);
```

### File Upload Processing

```php
if ($formHandled) {
    $data = $form->getData();
    $file = $data['document'];
    
    // $file is a normalized upload object with security information
    if ($file->isValid()) {
        // Safe to process the file
        $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
    }
}
```

## Rate Limiting

Protect against form spam and brute force attacks:

```php
// In your controller
public function contactAction(ServerRequestInterface $request): ResponseInterface
{
    // Check submission rate limit
    $ipAddress = $request->getServerParams()['REMOTE_ADDR'];
    if ($this->rateLimit->isLimitExceeded('contact_form', $ipAddress, 5, 60)) {
        // More than 5 submissions in 60 seconds
        $this->flash->add('Too many submissions. Please try again later.', 'error');
        return $this->view('contact', ['form' => $form]);
    }
    
    // Process form normally...
}
```

## Form Honeypots

Detect bots with invisible honeypot fields:

```php
$builder->add('website', [
    'type' => 'text',
    'honeypot' => true,  // Marks as honeypot field
    'attr' => [
        'style' => 'display: none;'  // Hide from real users
    ]
]);
```

The form handler will automatically reject submissions with filled honeypot fields.

## Security Best Practices

1. **Always validate on the server**: Never rely solely on client-side validation
2. **Set field constraints**: Define data types and length limits for all fields
3. **Use PRG pattern**: Post-Redirect-Get to prevent duplicate submissions
4. **Set proper permissions**: Ensure upload directories have proper permissions
5. **Validate file content**: Check uploaded files beyond just MIME type
6. **Log suspicious activity**: Monitor and log unusual form submissions
7. **Implement rate limiting**: Prevent form flooding and brute force attacks

## Example: Secure Contact Form

```php
// In ContactFormType
public function buildForm(FormBuilderInterface $builder, array $options = []): void
{
    $builder->add('name', [
        'type' => 'text',
        'required' => true,
        'validators' => [
            'length' => ['max' => 100]
        ]
    ]);
    
    $builder->add('email', [
        'type' => 'email',
        'required' => true,
        'validators' => [
            'email' => []
        ]
    ]);
    
    $builder->add('message', [
        'type' => 'textarea',
        'required' => true,
        'validators' => [
            'length' => ['max' => 1000]
        ],
        'sanitize' => function($value) {
            return htmlspecialchars(trim($value), ENT_QUOTES);
        }
    ]);
    
    // Honeypot field
    $builder->add('website', [
        'type' => 'text',
        'honeypot' => true,
        'attr' => ['style' => 'display:none;']
    ]);
}
```

This form includes:
- Input validation and constraints
- Custom sanitization
- A honeypot field to catch bots

When combined with CSRF protection and rate limiting in your controller, this creates a secure form handling system.