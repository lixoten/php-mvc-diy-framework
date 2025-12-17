# Form System Guide

## Overview

The MVCLixo form system provides a powerful and flexible way to create, validate, and render forms in your application. It supports different rendering approaches, layout options, themes, and error display modes.

## Key Features

- **Form Types**: Define form structure and behavior
- **Component-Based Rendering**: Choose between direct rendering or component-based approach
- **Layout Options**: Sequential, fieldsets, or section-based layouts
- **Theming Support**: Apply CSS themes to forms
- **Error Handling**: Display validation errors inline or in a summary
- **Form Builder**: Fluent API for form construction

## Creating a Form

### 1. Define a Form Type

Create a class that implements `FormTypeInterface`:

```php
// src/App/Features/MyFeature/Form/ContactFormType.php
class ContactFormType implements FormTypeInterface
{
    public function getName(): string
    {
        return 'contact_form';
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        // Add fields to your form
        $builder->add('name', [
            'type' => 'text',
            'label' => 'Your Name',
            'required' => true
        ]);
        
        $builder->add('email', [
            'type' => 'email',
            'label' => 'Email Address'
        ]);
        
        // Add more fields...
    }
}
```

### 2. Create the Form in a Controller

Use the form factory to create your form:

```php
public function contactAction(ServerRequestInterface $request): ResponseInterface
{
    // Create the form
    $form = $this->formFactory->create(
        $this->contactFormType,
        [],  // Initial data (empty)
        [
            'renderer' => 'bootstrap',
            'layout_type' => 'fieldsets',
            'error_display' => 'inline',
            'css_form_theme_class' => 'form-theme-dotted'
        ]
    );
    
    // Handle form submission
    $formHandled = $this->formHandler->handle($form, $request);
    if ($formHandled) {
        // Process form data
        $data = $form->getData();
        
        // Redirect or return response
        return $this->redirect('/thank-you');
    }
    
    // Render the form
    return $this->view('contact', [
        'form' => $form
    ]);
}
```

## Rendering Options

The system provides two approaches for rendering forms:

### 1. Direct Form Rendering

Renders the entire form at once with a single method call:

```php
<!-- In your view template -->
<?= $form->render() ?>
```

### 2. Component-Based Rendering with FormView

Provides more control over form structure and layout:

```php
// In your controller
$formView = new \Core\Form\View\FormView($form, [
    'error_display' => 'summary'
]);

// In your view template
<div class="card">
  <div class="card-body">
    <?= $formView->start() ?>
    
    <!-- Show error summary at top -->
    <?= $formView->errorSummary() ?>
    
    <!-- Render fields individually or in groups -->
    <div class="row">
      <div class="col-md-6"><?= $formView->row('name') ?></div>
      <div class="col-md-6"><?= $formView->row('email') ?></div>
    </div>
    
    <?= $formView->row('message') ?>
    
    <!-- Custom submit button -->
    <?= $formView->submit('Send Message', 'btn btn-primary') ?>
    
    <?= $formView->end() ?>
  </div>
</div>
```

## Layout Options

The form system supports three layout types:

### 1. Sequential Layout (`'none'`)

Renders fields in sequence with no special grouping.

```php
'layout_type' => 'none'
```

### 2. Fieldset Layout (`'fieldsets'`)

Groups fields into fieldsets with legends:

```php
'layout_type' => 'fieldsets',
'layout' => [
    'columns' => 2,  // Two-column layout
    'fieldsets' => [
        'personal' => [
            'legend' => 'Personal Information',
            'fields' => ['name', 'email']
        ],
        'message' => [
            'legend' => 'Your Message',
            'fields' => ['subject', 'message']
        ]
    ]
]
```

### 3. Section Layout (`'sections'`)

Creates a more flexible structure with headers, dividers, and field groups:

```php
'layout_type' => 'sections',
'layout' => [
    'sections' => [
        [
            'type' => 'header',
            'title' => 'Personal Information'
        ],
        [
            'type' => 'fields',
            'fields' => ['name', 'email']
        ],
        [
            'type' => 'divider'
        ],
        [
            'type' => 'header',
            'title' => 'Your Message'
        ],
        [
            'type' => 'fields',
            'fields' => ['subject', 'message']
        ]
    ]
]
```

## Error Display Modes

Choose how validation errors are displayed:

### 1. Inline Errors (`'inline'`)

Displays error messages below each field.

```php
'error_display' => 'inline'
```

### 2. Summary Errors (`'summary'`)

Shows all errors in a summary block at the top of the form.

```php
'error_display' => 'summary'
```

## Theming Support

Apply visual themes to your forms:

```php
// Apply a theme to your form
$formTheme = "dotted";
$themeClass = $this->config->getConfigValue('view', "form.themes.$formTheme.class", 'form-theme-dotted');

// In controller
$form = $this->formFactory->create(
    $this->contactFormType,
    [],
    [
        'css_form_theme_class' => $themeClass
    ]
);

// In layout template
<?php if (!empty($formTheme)) : ?>
    <?php $formThemeCss = $this->config->getConfigValue('view', "form.themes.{$formTheme}.css", ''); ?>
    <?php if (!empty($formThemeCss)) : ?>
        <link rel="stylesheet" href="<?= $formThemeCss ?>">
    <?php endif; ?>
<?php endif; ?>
```

## Form Options Reference

| Option | Description | Default |
|--------|-------------|---------|
| `renderer` | CSS framework renderer to use | `'bootstrap'` |
| `layout_type` | Form layout structure | `'none'` |
| `error_display` | How errors are displayed | `'inline'` |
| `css_form_theme_class` | CSS class for theming | `''` |
| `fields` | Field-specific overrides | `[]` |
| `layout` | Custom layout structure | `[]` |
| `no_submit_button` | Disable auto submit button | `false` |
| `submit_text` | Text for submit button | `'Submit'` |
| `submit_class` | CSS class for submit button | `'btn btn-primary'` |

## Next Steps

See additional documentation on:
- Field Types and Validation
- Creating Custom Renderers
- Form Security and CSRF Protection