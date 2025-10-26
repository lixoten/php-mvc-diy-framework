# How Forms Are Built in MVC LIXO Framework

This document explains the step-by-step process of how forms are constructed in the MVC LIXO framework, focusing on the "edit" action for a feature like "testys". The process involves configuration files, form types, factories, and services to ensure fields are resolved, validated, and rendered correctly.

## 1. Configuration Setup

Forms rely on two main configuration files to define available fields and their layout.

### 1.1 View Options Configuration (`src/Config/view_options/{feature}_{action}.php`)

This file defines page-specific settings, including the form layout and render options. For example, testys_edit.php:

```php
return [
    'render_options' => [
        'ajax_save' => true,
        'layout_type' => 'sequential',
        'submit_text' => 'Save',
    ],
    'form_layout' => [
        [
            'title' => 'Your Title',
            'fields' => ['title', 'profile_picture'],
        ],
    ],
    'form_hidden_fields' => ['testy_id', 'testy_user_id'],
];
```

- **form_layout**: Defines sections with titles and lists of fields to display.
- **form_hidden_fields**: Fields that are part of the form but not visible in the layout.
- **render_options**: Global form settings like AJAX saving, themes, and validation.

### 1.2 List Fields Configuration (`src/Config/list_fields/{feature}_{action}.php`)

This file defines the properties of each field, including labels, formatters, validators, and form attributes. It supports fallback hierarchies (page-specific → entity-specific → base). For example, testys_edit.php:

```php
return [
    'title' => [
        'label' => 'testys.title',
        'form' => [
            'type' => 'text',
            'attributes' => [
                'required' => true,
                'minlength' => 5,
                'maxlength' => 30,
            ],
        ],
    ],
    'profile_picture' => [
        'label' => 'Profile Picture',
        'form' => [
            'type' => 'file',
            'upload' => ['max_size' => 2097152],
        ],
    ],
];
```

- Each field has keys like `label`, `formatter`, `validators`, and `form` (for form-specific options).
- Fields are resolved with fallbacks: page config → entity config → base config.

## 2. Form Type Initialization

Form types (e.g., `TestysFormType`) extend `AbstractFormType` and handle merging configs and validating fields.

### 2.1 Constructor and Init

In `AbstractFormType::__construct()`, dependencies like `FieldRegistryService` and `ConfigService` are injected. The `init()` method:

- Loads default configs from `view.form`.
- Loads view-specific configs from `view_options/{viewName}`.
- Merges render options and sets layout/hidden fields.
- Calls `filterValidateFormFields()` to validate and filter fields.

### 2.2 Field Validation and Filtering

`filterValidateFormFields()`:

- Extracts fields from layout and hidden fields.
- Uses `FieldRegistryService::filterAndValidateFields()` to ensure fields exist in configs.
- Validates layout to remove invalid sections/fields.
- Sets final `fields`, `layout`, and `render_options` in the form type.

## 3. Form Creation via FormFactory

The controller (e.g., `TestysController`) calls `FormFactory::create()` with the form type and initial data.

### 3.1 FormFactory Process

- Creates a `Form` instance with CSRF token.
- Instantiates `FormBuilder` to build the form.
- Calls `formType->buildForm($builder)` to add fields.
- Sets validator, renderer, and initial data.

### 3.2 Building the Form

In `AbstractFormType::buildForm()`:

- Sets render options and layout on the builder.
- Retrieves fields from the form type.
- For each field, uses `FieldRegistryService::getFieldWithFallbacks()` to resolve the field definition.
- Adds fields to the form via `builder->add($name, $options)`.

## 4. Field Resolution with FieldRegistryService

`FieldRegistryService` resolves fields using a layered fallback:

1. **Page-specific**: `list_fields/{pageName}` (e.g., `testys_edit`).
2. **Entity-specific**: `list_fields/{entityName}` (e.g., `testys`).
3. **Base**: `list_fields/base`.

- `getFieldWithFallbacks($fieldName, $pageName, $entityName)` checks each level.
- Invalid fields are logged and filtered out in development mode.

## 5. Form Processing and Rendering

- **Controller**: Handles requests, fetches data, processes form via `FormHandler`, and renders the view.
- **Rendering**: The form uses a renderer (e.g., Bootstrap) to output HTML, applying themes, layouts, and field attributes.
- **Validation**: Occurs on submission; errors are displayed based on `error_display` setting.

## Example Flow for Testys Edit

1. User accesses `/testys/edit/14`.
2. `TestysController::editAction()` overrides form options if needed.
3. Resolves `TestysFormType` via `TypeResolverService`.
4. Fetches record data with required fields.
5. `FormFactory::create()` builds the form with resolved fields.
6. Form is rendered in the view, ready for user interaction.

This process ensures forms are configurable, validated, and extensible while following SOLID principles. For issues, check logs for invalid fields or config mismatches.