# Form Layout Customization

## Overview

The MVCLixo form system provides powerful layout customization options to structure your forms in visually appealing and user-friendly ways. You can organize fields in columns, tabs, fieldsets, and more.

## Layout Types

There are three main layout types available:

### 1. Sequential Layout (`'none'`)

The default layout that renders fields one after another with no special grouping.

```php
$form = $this->formFactory->create(
    $this->contactFormType,
    [],
    [
        'layout_type' => 'none'  // Default
    ]
);
```

### 2. Fieldset Layout (`'fieldsets'`)

Groups fields into HTML `<fieldset>` elements with optional legends, useful for logical grouping of related fields.

```php
$form = $this->formFactory->create(
    $this->contactFormType,
    [],
    [
        'layout_type' => 'fieldsets',
        'layout' => [
            'fieldsets' => [
                'personal' => [
                    'legend' => 'Personal Information',
                    'fields' => ['firstName', 'lastName', 'email']
                ],
                'account' => [
                    'legend' => 'Account Details',
                    'fields' => ['username', 'password', 'confirmPassword'] 
                ]
            ]
        ]
    ]
);
```

### 3. Section Layout (`'sections'`)

The most flexible layout system with support for headers, dividers, and custom field groupings.

```php
$form = $this->formFactory->create(
    $this->contactFormType,
    [],
    [
        'layout_type' => 'sections',
        'layout' => [
            'sections' => [
                [
                    'type' => 'header',
                    'title' => 'Contact Information'
                ],
                [
                    'type' => 'fields',
                    'fields' => ['name', 'email', 'phone']
                ],
                [
                    'type' => 'divider'
                ],
                [
                    'type' => 'header',
                    'title' => 'Message Details'
                ],
                [
                    'type' => 'fields',
                    'fields' => ['subject', 'message']
                ]
            ]
        ]
    ]
);
```

## Column Layouts

For both fieldset and section layouts, you can create multi-column layouts:

```php
// Two-column fieldset layout
'layout_type' => 'fieldsets',
'layout' => [
    'columns' => 2,  // Creates two columns
    'fieldsets' => [
        // Fieldset definitions...
    ]
]
```

With section layouts, you can control columns more granularly:

```php
'layout_type' => 'sections',
'layout' => [
    'sections' => [
        [
            'type' => 'header',
            'title' => 'Personal Information'
        ],
        [
            'type' => 'row',  // Start a new row with columns
            'columns' => [
                [
                    // First column
                    'width' => 6,  // Bootstrap column width (out of 12)
                    'fields' => ['firstName', 'lastName']
                ],
                [
                    // Second column
                    'width' => 6,
                    'fields' => ['email', 'phone']
                ]
            ]
        ],
        // More sections...
    ]
]
```

## Tabbed Forms

You can create tabbed interfaces using section layouts:

```php
'layout_type' => 'sections',
'layout' => [
    'tabs' => true,  // Enable tabbed interface
    'sections' => [
        [
            'type' => 'tab',
            'title' => 'Basic Information',
            'fields' => ['name', 'email', 'phone']
        ],
        [
            'type' => 'tab',
            'title' => 'Address',
            'fields' => ['street', 'city', 'state', 'zip']
        ],
        [
            'type' => 'tab',
            'title' => 'Additional Information',
            'fields' => ['comments', 'howDidYouHear']
        ]
    ]
]
```

## Conditional Fields

You can show/hide fields based on other field values:

```php
'layout_type' => 'sections',
'layout' => [
    'sections' => [
        [
            'type' => 'fields',
            'fields' => ['contactMethod']  // Radio button: 'email' or 'phone'
        ],
        [
            'type' => 'fields',
            'fields' => ['email'],
            'condition' => [
                'field' => 'contactMethod',
                'value' => 'email'
            ]
        ],
        [
            'type' => 'fields',
            'fields' => ['phone'],
            'condition' => [
                'field' => 'contactMethod', 
                'value' => 'phone'
            ]
        ]
    ]
]
```

## Field Groups

For repeating groups of fields (like multiple addresses):

```php
// In your form type
public function buildForm(FormBuilderInterface $builder, array $options = []): void
{
    // Add a normal field
    $builder->add('name', ['type' => 'text']);
    
    // Add a group of fields that repeats
    $builder->addGroup('addresses', function(FormGroupBuilder $group) {
        $group->add('street', ['type' => 'text']);
        $group->add('city', ['type' => 'text']);
        $group->add('state', ['type' => 'select', 'choices' => $this->getStates()]);
        $group->add('zip', ['type' => 'text']);
    }, [
        'repeatable' => true,
        'min_entries' => 1,
        'max_entries' => 5,
        'add_button_text' => 'Add Another Address'
    ]);
}
```

## FormView with Custom Layouts

When using FormView with custom layouts:

```php
// In controller
$formView = new \Core\Form\View\FormView($form);

// In template
<?= $formView->start() ?>
<?= $formView->errorSummary() ?>

<!-- Custom layout with Bootstrap columns -->
<div class="row">
    <div class="col-md-6">
        <?= $formView->row('firstName') ?>
    </div>
    <div class="col-md-6">
        <?= $formView->row('lastName') ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $formView->row('email') ?>
    </div>
    <div class="col-md-6">
        <?= $formView->row('phone') ?>
    </div>
</div>

<!-- Rendered group -->
<?php foreach ($formView->group('addresses') as $i => $addressView): ?>
    <div class="card mb-3">
        <div class="card-header">Address <?= $i + 1 ?></div>
        <div class="card-body">
            <?= $addressView->row('street') ?>
            <div class="row">
                <div class="col-md-6"><?= $addressView->row('city') ?></div>
                <div class="col-md-3"><?= $addressView->row('state') ?></div>
                <div class="col-md-3"><?= $addressView->row('zip') ?></div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Controls for the group -->
<?= $formView->groupControls('addresses') ?>

<?= $formView->submit('Save') ?>
<?= $formView->end() ?>
```

## Reusing Layouts

You can define reusable layout templates:

```php
// In a service or configuration file
$layouts = [
    'address' => [
        'layout_type' => 'fieldsets',
        'layout' => [
            'fieldsets' => [
                'address' => [
                    'legend' => 'Address',
                    'fields' => ['street', 'city', 'state', 'zip']
                ]
            ]
        ]
    ]
];

// In your controller
$form = $this->formFactory->create(
    $this->addressFormType,
    [],
    $this->layoutManager->getLayout('address')
);
```

## Best Practices for Form Layouts

1. **Group related fields**: Use fieldsets or sections to group logically related fields
2. **Use columns wisely**: Multi-column layouts save space but can be harder to follow
3. **Consider form length**: Break very long forms into tabbed sections or multi-step processes
4. **Be consistent**: Use the same layout patterns throughout your application
5. **Mobile-friendly**: Ensure your layouts work well on smaller screens
6. **Test with real data**: Forms look different when filled with actual data

## Examples

### Registration Form with Sections

```php
'layout_type' => 'sections',
'layout' => [
    'sections' => [
        [
            'type' => 'header',
            'title' => 'Account Information'
        ],
        [
            'type' => 'row',
            'columns' => [
                [
                    'width' => 6,
                    'fields' => ['username']
                ],
                [
                    'width' => 6,
                    'fields' => ['email']
                ]
            ]
        ],
        [
            'type' => 'row',
            'columns' => [
                [
                    'width' => 6,
                    'fields' => ['password']
                ],
                [
                    'width' => 6,
                    'fields' => ['confirmPassword']
                ]
            ]
        ],
        [
            'type' => 'divider'
        ],
        [
            'type' => 'header',
            'title' => 'Profile Information'
        ],
        [
            'type' => 'row',
            'columns' => [
                [
                    'width' => 6,
                    'fields' => ['firstName']
                ],
                [
                    'width' => 6,
                    'fields' => ['lastName']
                ]
            ]
        ],
        [
            'type' => 'fields',
            'fields' => ['bio', 'profileImage']
        ]
    ]
]
```

This documentation provides comprehensive guidance on the form layout options available in your MVCLixo form system, helping developers create structured, user-friendly forms across your application.