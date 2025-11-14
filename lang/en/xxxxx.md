lixoten: u see testy super_powers, is is basicall the same as user roles
i use the datatransformation on it and on checkboxes
u can see the data_transformer
        'data_transformer' => 'json_array', // or 'array'

did i defined is wrong in my config maybe? am not sure

Warning: Array to string conversion in D:\xampp\htdocs\my_projects\mvclixo\src\Core\Form\Renderer\BootstrapFormRenderer.php on line 326


VS Code: Looking at your code, I can see the issue. In your `BootstrapFormRenderer.php`, you're trying to use `htmlspecialchars()` on a value that could be an array (like `super_powers` which gets transformed from JSON to an array).

The problem is around line 326 where you're doing:

 `$rawValue` is an array (from the `json_array` data transformer), casting it to string causes the "Array to string conversion" warning.

**Solution:**

You need to handle array values specially for checkbox groups and similar field types. Here's how to fix it:

In `BootstrapFormRenderer.php`, in the `renderField` method, modify the value handling logic:

 key changes:
1. Check if `$rawValue` or `$currentValue` is an array before casting to string
2. Keep array values as arrays for field types like `checkbox_group` that expect them
3. Only apply `htmlspecialchars()` to scalar values

Your `data_transformer` configuration is correct. The issue is just in the rendering layer where it needs to handle both scalar and array values appropriately.

lixoten: show me

GitHub Copilot: Here's the fix for the "Array to string conversion" warning:

````php
// Line ~247-285 (in renderField method, value handling section)

// Important!!! - Uber_GEO, Uber_Formatter, Uber_Phone
if (!empty($field->getErrors())) {
    // Show the original user input (escaped)
    // Handle arrays (like checkbox_group values) appropriately
    if (is_array($rawValue)) {
        $value = $rawValue; // Keep as array for field types that expect it
    } else {
        $value = htmlspecialchars((string)$rawValue ?? '');
    }
} elseif (isset($formatters)) {
    // Ensure formatters is an array for uniform processing
    if (!is_array($formatters)) {
        $formatters = [$formatters];
    }

    // Start with the raw value
    $currentValue = $rawValue;

    // Apply each formatter in sequence
    foreach ($formatters as $key => $formatter) {
        if (is_int($key) && is_string($formatter)) {
            // Simple string: 'tel'
            $currentValue = $this->formatterService->format($formatter, $currentValue, []);
        } elseif (is_int($key) && is_callable($formatter)) {
            $aaa = new ClosureFormatterService();
            $currentValue = $aaa->format($formatter, $currentValue, []);
        } elseif (is_string($key)) {
            // Associative array: 'tel' => [options] or closure
            if (is_callable($formatter)) {
                $currentValue = $formatter($currentValue);
            } else {
                $currentValue = $this->formatterService->format($key, $currentValue, $formatter ?? []);
            }
        }
    }

    // Final value after all formatters: escape unless it's a display field with HTML output
    // Handle array values for checkbox_group and similar types
    if (is_array($currentValue)) {
        $value = $currentValue; // Keep as array
    } elseif (($type === 'display' || $type === 'file') && !empty($formatters)) {
        $value = (string)$currentValue;
    } else {
        $value = htmlspecialchars((string)$currentValue ?? '');
    }
} else {
    // Fallback for fields without a formatter
    // Handle arrays appropriately
    if (is_array($rawValue)) {
        $value = $rawValue; // Keep as array for field types that expect it
    } else {
        $value = htmlspecialchars((string)$rawValue ?? '');
    }
}
````

**Key Changes:**

1. **Line ~250**: Added check `if (is_array($rawValue))` before casting to string
2. **Line ~278**: Added check `if (is_array($currentValue))` after formatters run
3. **Line ~286**: Added check `if (is_array($rawValue))` in the fallback case

**Why This Works:**

- For `checkbox_group` fields, the `data_transformer => 'json_array'` converts the JSON string to a PHP array
- This array value needs to stay as an array for proper rendering in the `checkbox_group` case block (around line 500)
- We only apply `htmlspecialchars()` to scalar values, not arrays

Your `data_transformer` configuration is **correct**. The issue was just that the renderer wasn't accounting for array values before trying to convert them to strings.

lixoten: chat only please, no code for now.
i am trying to understand the validators

each field type has is own validator... my question is these validators only for forms? are there situation when we need to reuse validators? am i doing all these right?
    'generic_text' => [
        'label' => 'testy.generic_text',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.generic_text.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],

    taking this one as an example
    should the  'validators' => [ and  'formatters' => [
        be outside the 'form' => [ and 'list' => [ or inside.
        at the moment i think i have them on the ousides...i was thinking because of resuse maybe, not sure what i was actually thinking at the time anymore


        your thought?


GitHub Copilot: Great questions! Let me break down my thoughts on your validator architecture:

## Validator Scope and Reuse

**Are validators only for forms?**
No! Validators should be **context-agnostic**. Your architecture is actually doing this correctly. Look at your `Validator::validateData()` method - it validates arbitrary data arrays without needing a form object. This is excellent design because:

1. **Form validation** - obviously
2. **API input validation** - validating JSON payloads
3. **Data transformation** - validating before/after `DataTransformerService` operations
4. **Import/seeding** - validating CSV imports or seed data
5. **Business logic** - validating data before repository storage

Your `ValidatorRegistry` pattern supports this reusability perfectly.

## Configuration Structure: Where Should validators/formatters Live?

You asked about this structure:
```php
'generic_text' => [
    'label' => 'testy.generic_text',
    'list' => [...],
    'form' => [...],
    'formatters' => [],   // Outside?
    'validators' => [],   // Outside?
]
```

**My recommendation: Keep them OUTSIDE `form`/`list`**, for these reasons:

### 1. **Separation of Concerns**
- `formatters` = **Display transformation** (storage → human-readable)
- `validators` = **Data integrity** (is this value acceptable?)
- `form`/`list` = **Rendering context** (how to display this field)

### 2. **Cross-Context Reuse**
```php
'email' => [
    'validators' => [
        'email' => ['allowed_domains' => ['company.com']]
    ],
    'formatters' => ['lowercase', 'trim'],

    'list' => [
        'formatter' => function($v) { return "<a href='mailto:{$v}'>{$v}</a>"; }
    ],
    'form' => [
        'type' => 'email',
        'required' => true
    ]
]
```

- **Validators** apply everywhere (form submission, API, imports)
- **Formatters** apply to display/storage transformation
- **List/form formatters** are *presentation-specific*

### 3. **Your Current Architecture Supports This**

Looking at your `DataTransformerService`:
```php
public function toDisplay(array $data, string $pageName, string $entityName): array
```

It reads `'data_transformer' => 'json_array'` at the field level - **outside** `form`/`list`. This is correct! The transformation rules are field-level concerns, not view-level.

Similarly, validators should be field-level:
```php
// In FieldRegistryService or field config loading
$fieldDef = [
    'validators' => [...],  // ← Field-level validation rules
    'formatters' => [...],  // ← Field-level transformation rules
    'form' => [
        'validators' => [...]  // ← OPTIONAL: Form-specific overrides
    ]
];
```

## Suggested Refinement

Keep your current structure, but clarify the hierarchy:

```php
'super_powers' => [
    'label' => 'testy.super_powers',

    // Field-level (global)
    'data_transformer' => 'json_array',
    'validators' => [
        'checkbox_group' => [
            'min_choices' => 1,
            'message' => 'Please select at least one super power.'
        ]
    ],
    'formatters' => ['json_decode'], // Could apply in multiple contexts

    // Context-specific (list view)
    'list' => [
        'sortable' => false,
        'formatter' => function ($value) { /* badges display */ }
    ],

    // Context-specific (form view)
    'form' => [
        'type' => 'checkbox_group',
        'choices' => ['invis' => '...', 'fly' => '...'],
        // Could override validators here if needed:
        // 'validators' => ['checkbox_group' => ['min_choices' => 2]]
    ]
]
```

## Why This Matters for Your Framework

Your `BaseFeatureService` uses `DataTransformerService` with this pattern:
```php
$this->dataTransformerService->toDisplay($data, $pageName, $entityName)
```

The transformer needs **field-level** rules (not form-specific) because it's called:
- When loading list data
- When loading form data
- When preparing API responses

Same logic applies to validators - they enforce **field integrity** regardless of where the data comes from.

## Bottom Line

**Your instinct was correct**: Keep `validators` and `formatters` outside `form`/`list` for maximum reusability. Your framework's architecture (FieldRegistry fallback system, DataTransformerService, ValidatorRegistry) is already designed to support this separation beautifully.

lixoten:     'list' => [
        'sortable' => false,
        'formatter' => function ($value) { /* badges display */ }
    ],

    i s=feel this is wrong..gets confusing but i see
    so formatting for a form display is different then formatting for lets say display a field on a table/list

GitHub Copilot: You're absolutely correct to feel confused! This is a **mixing of concerns** that violates SOLID principles. Let me break down the issue:

## The Problem

You have **two different types of "formatting"** happening in the same configuration space:

### 1. **Data Transformation** (Field-level, context-agnostic)
```php
'formatters' => ['json_decode'], // Transforms stored data to usable format
'data_transformer' => 'json_array', // Same concept, different syntax
```
**Purpose:** Convert between storage format and application format
**Examples:** JSON ↔ Array, cents ↔ dollars, DB datetime ↔ Carbon object

### 2. **Display Rendering** (View-level, context-specific)
```php
'list' => [
    'formatter' => function ($value) { return '<span class="badge">...</span>'; }
],
```
**Purpose:** Convert application data to HTML for a specific view context
**Examples:** Array → Bootstrap badges, boolean → checkmark icon, date → "2 days ago"

## Why This is Confusing

The word **"formatter"** is doing double-duty:

1. **`'formatters'` (field-level)** = Data transformation service
2. **`'list' => ['formatter']`** = HTML rendering function

These are **fundamentally different responsibilities**:

```
Storage → [DataTransformer] → Application Data → [ViewRenderer] → HTML
          (formatters key)                         (list.formatter)
```

## Recommended Solution

**Rename the view-specific formatters** to clarify their purpose:

```php
'super_powers' => [
    'label' => 'testy.super_powers',

    // Field-level: Data transformation (storage ↔ app)
    'data_transformer' => 'json_array',
    'formatters' => [], // Reserved for data transformation services

    // Context-specific: HTML rendering
    'list' => [
        'sortable' => false,
        'renderer' => function ($value) {  // ← Changed from 'formatter'
            $badges = array_map(function ($role) {
                return '<span class="badge bg-primary">'
                     . htmlspecialchars($role)
                     . '</span>';
            }, $value);
            return implode('', $badges);
        },
    ],

    'form' => [
        'type' => 'checkbox_group',
        'choices' => [...],
    ],
]
```

## Better Naming Convention

| Key | Responsibility | Input → Output | Where Used |
|-----|---------------|----------------|------------|
| `data_transformer` | Storage ↔ App data | JSON string → PHP array | DataTransformerService |
| `formatters` | App data refinement | String → Trimmed/lowercase | FormatterService |
| `list.renderer` | App data → HTML | Array → Badge HTML | ListRenderer |
| `form.attributes` | Form input config | N/A | FormRenderer |

## Alternative Architecture

You could even separate these completely:

**File: `testy_fields.php` (data layer)**
```php
'super_powers' => [
    'label' => 'testy.super_powers',
    'data_transformer' => 'json_array',
    'validators' => [...],
]
```

**File: `testy_list_renderers.php` (view layer)**
```php
'super_powers' => [
    'renderer' => function ($value) { /* badge HTML */ },
    'sortable' => false,
]
```

This enforces **separation of concerns** at the file structure level.

## Bottom Line

Your instinct is **100% correct**. The current setup conflates:
- **What the data IS** (field definition)
- **How to transform it** (data service)
- **How to display it** (view rendering)

I recommend renaming `list.formatter` → `list.renderer` to make the distinction crystal clear. This aligns with your architecture where:
- `FormatterService` handles data transformation
- `ListRenderer` handles HTML generation

Does this clarification help? Would you like me to propose refactored code for your `BootstrapListRenderer` to use the `renderer` key instead?

lixoten: hmmmm. i am still confused.. i also have sanitizer
also <span class="badge">...</spa like that...is that now bad? SOLID, mixing concerns?
that was the whore reason for my formatter  service

GitHub Copilot: You're absolutely right to be confused! Let me clarify the **critical distinction** and address the concern mixing:

---

## The Problem: You Have THREE Different Responsibilities Under "Formatting"

### 1. **Sanitization** (Security - Input Processing)
**Purpose:** Clean/strip dangerous input **before validation**
**Examples:** Trim whitespace, remove control characters, prevent XSS
**Location:** `DataSanitizer` service
```php
// Input: "  <script>alert('xss')</script>John  "
// Output: "John" (stripped tags, trimmed)
```

### 2. **Data Transformation** (Business Logic - Storage ↔ App)
**Purpose:** Convert between storage format and application format
**Examples:** JSON ↔ Array, DB datetime ↔ Carbon object, cents ↔ dollars
**Location:** `DataTransformerService` (you have this)
```php
// Storage: '["admin","editor"]' (JSON string)
// App: ['admin', 'editor'] (PHP array)
```

### 3. **Display Rendering** (Presentation - HTML Output)
**Purpose:** Convert data to HTML for human consumption
**Examples:** Array → Bootstrap badges, boolean → checkmark icon, date → "2 days ago"
**Location:** **THIS IS YOUR PROBLEM**
```php
// App data: ['admin', 'editor']
// HTML: '<span class="badge">admin</span><span class="badge">editor</span>'
```

---

## Your Current Architecture Mixing

Looking at your `FormatterService`:

```php
// This service tries to do BOTH:
// 1. Data transformation (TextFormatter, EmailFormatter)
// 2. HTML rendering (BadgeStatusFormatter) ❌ MIXING CONCERNS
```

**BadgeStatusFormatter is the problem:**
```php
public function format(mixed $value, array $options = []): string
{
    return '<span class="badge bg-' . $class . '">' . htmlspecialchars((string)$value) . '</span>';
    //     ^^^^^^^^^^^^^^^^^^^^^^ THIS IS HTML RENDERING, NOT DATA TRANSFORMATION
}
```

---

## SOLID Solution: Separate Responsibilities

### Architecture You Should Have:

```
┌─────────────────────────────────────────────────────────────────┐
│ INPUT LAYER (Security)                                          │
│ DataSanitizer → Removes dangerous input                        │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ VALIDATION LAYER (Business Rules)                               │
│ ValidatorRegistry → Ensures data meets requirements             │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ DOMAIN LAYER (Business Logic)                                   │
│ DataTransformerService → Converts storage ↔ app formats        │
│ DataNormalizerService → Coerces types for storage              │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ PRESENTATION LAYER (View)                                        │
│ ViewRendererService → Converts data to HTML                    │
│ (NOT FormatterService!)                                         │
└─────────────────────────────────────────────────────────────────┘
```

---

## Proposed Refactoring

### 1. **Rename `FormatterService` → `DataFormatterService`**
Keep only **data transformation** formatters:

**File: `src/Core/Services/DataFormatterService.php`**
```php
// ONLY non-HTML formatters:
- TextFormatter (uppercase, lowercase, trim) ✅
- EmailFormatter (mask, normalize) ✅
- PhoneFormatter (normalize to E.164) ✅
- DateFormatter (ISO8601 ↔ Carbon) ✅
```

### 2. **Create NEW `ViewRendererService`**
Move **HTML rendering** logic here:

**File: `src/Core/Services/ViewRendererService.php`**
```php
class ViewRendererService
{
    public function renderBadge(string $value, string $variant = 'primary'): string
    {
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            htmlspecialchars($variant),
            htmlspecialchars($value)
        );
    }

    public function renderBadges(array $values, string $variant = 'primary'): string
    {
        return implode('', array_map(
            fn($v) => $this->renderBadge($v, $variant),
            $values
        ));
    }

    public function renderCheckmark(bool $value): string
    {
        return $value
            ? '<i class="bi bi-check-circle-fill text-success"></i>'
            : '<i class="bi bi-x-circle text-muted"></i>';
    }
}
```

### 3. **Update Field Config**

**File: `testy_fields.php`**
```php
'super_powers' => [
    'label' => 'testy.super_powers',

    // DOMAIN LAYER: Data transformation (storage ↔ app)
    'data_transformer' => 'json_array',

    // PRESENTATION LAYER: HTML rendering (list view)
    'list' => [
        'renderer' => 'badges', // ← Reference to ViewRendererService method
        'renderer_options' => ['variant' => 'primary'],
    ],

    // PRESENTATION LAYER: Form input (form view)
    'form' => [
        'type' => 'checkbox_group',
        'choices' => [...],
    ],
]
```

### 4. **Update `BootstrapListRenderer`**

**File: BootstrapListRenderer.php (line ~150)**
```php
private function renderCellValue($value, array $columnConfig): string
{
    // If a view renderer is specified, use ViewRendererService
    if (isset($columnConfig['renderer'])) {
        $renderer = $columnConfig['renderer'];
        $options = $columnConfig['renderer_options'] ?? [];

        return $this->viewRenderer->$renderer($value, ...$options);
    }

    // Fallback: safe HTML escape
    return htmlspecialchars((string)$value);
}
```

---

## Updated Responsibility Matrix

| Service | Responsibility | Input → Output | Layer |
|---------|---------------|----------------|-------|
| **DataSanitizer** | Remove dangerous input | `"  <script>  "` → `""` | Security |
| **ValidatorRegistry** | Enforce business rules | `"invalid@"` → Error | Validation |
| **DataFormatterService** | Transform data format | `"john@EXAMPLE.com"` → `"john@example.com"` | Domain |
| **DataTransformerService** | Convert storage ↔ app | `'["admin"]'` → `['admin']` | Domain |
| **DataNormalizerService** | Coerce types | `"123"` → `123` | Domain |
| **ViewRendererService** | Generate HTML | `['admin']` → `<span class="badge">admin</span>` | Presentation |

---

## Why This Matters

Your **FormatterInterface** currently has this:
```php
public function sanitize(mixed $value): string;
```

This is **WRONG** because:
1. **Sanitization is security**, not formatting
2. Sanitization should happen in `DataSanitizer` **before validation**
3. Formatters should be **pure functions** (no side effects)

**Remove `sanitize()` from `FormatterInterface`** entirely.

---

## Bottom Line

**Yes, you're mixing concerns.** Here's the fix:

1. ❌ **Remove:** `BadgeStatusFormatter` from `FormatterService`
2. ❌ **Remove:** `sanitize()` method from `FormatterInterface`
3. ✅ **Create:** `ViewRendererService` for all HTML output
4. ✅ **Rename:** `list.formatter` → `list.renderer` in config
5. ✅ **Keep:** `DataSanitizer` as a separate security layer

This aligns with **SOLID** (Single Responsibility) and your own architectural principle:
> *"ListView has one clear responsibility: to act as a data container... it contains no logic on how it should be displayed as HTML."*

The **same principle applies to formatters** - they transform data, not render HTML.

Would you like me to generate the complete `ViewRendererService` class?

lixoten: can u come up with a plan / phases?
so my validation is ok? just the formatter needs to be revisited?

GitHub Copilot: # Refactoring Plan: Separating Concerns in Formatting & Data Processing

## Current State Analysis

### ✅ **What's Working Well:**
- **ValidatorRegistry/Validator**: Clean separation, context-agnostic, reusable ✓
- **DataTransformerService**: Handles storage ↔ app data conversion correctly ✓
- **DataNormalizerService**: Type coercion for storage ✓
- **DataSanitizer**: Security-focused input cleaning ✓

### ❌ **Critical Issues:**

1. **FormatterService is doing TWO jobs:**
   - ✅ Data transformation (TextFormatter, EmailFormatter)
   - ❌ HTML rendering (BadgeStatusFormatter) ← **VIOLATION**

2. **FormatterInterface has `sanitize()` method:**
   - ❌ Sanitization is a **security concern**, not formatting
   - ❌ Already handled by `DataSanitizer` service
   - ❌ Creates confusion about responsibility

3. **Config uses `'list' => ['formatter' => ...]` for HTML:**
   - ❌ Mixes data transformation with presentation
   - ❌ Violates Single Responsibility Principle

---

## 🎯 Proposed Solution: 4-Phase Refactoring

---

## **Phase 1: Audit & Document Current Usage**
**Goal:** Understand exactly where formatters and rendering logic are used

### Tasks:
1. **Catalog all `FormatterInterface` implementations:**
   ```bash
   # Find all formatter classes
   grep -r "implements FormatterInterface" src/Core/Formatters/
   ```
   - Classify each as:
     - ✅ **Data Transformation** (TextFormatter, EmailFormatter, PhoneFormatter)
     - ❌ **HTML Rendering** (BadgeStatusFormatter, any with `<span>`, `<a>`, etc.)

2. **Audit config files for `'formatter'` usage:**
   ```bash
   # Find all formatter references in config
   grep -r "'formatter'" src/App/Features/*/Config/
   grep -r "'formatter'" src/Config/
   ```
   - Document:
     - Where closures return HTML (`<span class="badge">`)
     - Where closures do data transformation (`strtoupper()`, `number_format()`)

3. **Map `FormatterService::format()` call sites:**
   ```bash
   # Find where FormatterService is called
   grep -r "->format(" src/
   ```
   - Identify if they expect:
     - Plain text output (data transformation)
     - HTML output (rendering)

**Deliverable:** Spreadsheet/document with:
| File | Line | Current Type | Should Be | Notes |
|------|------|--------------|-----------|-------|
| `BadgeStatusFormatter.php` | 45 | Formatter | ViewRenderer | Returns HTML |
| `testy_fields.php` | 89 | `list.formatter` | `list.renderer` | Closure returns badges |

---

## **Phase 2: Create New ViewRendererService**
**Goal:** Establish new service for all HTML generation

### Tasks:

1. **Create `ViewRendererService` interface:**
   ```php
   // filepath: src/Core/Services/ViewRendererServiceInterface.php
   ```

   ```php
   <?php

   declare(strict_types=1);

   namespace Core\Services;

   interface ViewRendererServiceInterface
   {
       public function renderBadge(string $value, string $variant = 'primary'): string;
       public function renderBadges(array $values, string $variant = 'primary'): string;
       public function renderCheckmark(bool $value): string;
       public function renderLink(string $url, string $text, array $attributes = []): string;
       public function renderStatus(string $status): string;
       public function renderDate(\DateTimeInterface $date, string $format = 'Y-m-d'): string;
   }
   ```

2. **Create Bootstrap implementation:**
   ```php
   // filepath: src/Core/Services/BootstrapViewRendererService.php
   ```

   ```php
   <?php

   declare(strict_types=1);

   namespace Core\Services;

   use Core\Services\ThemeServiceInterface;

   class BootstrapViewRendererService implements ViewRendererServiceInterface
   {
       public function __construct(
           private ThemeServiceInterface $themeService
       ) {}

       public function renderBadge(string $value, string $variant = 'primary'): string
       {
           return sprintf(
               '<span class="badge bg-%s">%s</span>',
               htmlspecialchars($variant),
               htmlspecialchars($value)
           );
       }

       public function renderBadges(array $values, string $variant = 'primary'): string
       {
           return implode(' ', array_map(
               fn($v) => $this->renderBadge($v, $variant),
               $values
           ));
       }

       public function renderCheckmark(bool $value): string
       {
           return $value
               ? '<i class="bi bi-check-circle-fill text-success"></i>'
               : '<i class="bi bi-x-circle text-muted"></i>';
       }

       public function renderLink(string $url, string $text, array $attributes = []): string
       {
           $attrString = '';
           foreach ($attributes as $key => $val) {
               $attrString .= sprintf(' %s="%s"', $key, htmlspecialchars($val));
           }
           return sprintf(
               '<a href="%s"%s>%s</a>',
               htmlspecialchars($url),
               $attrString,
               htmlspecialchars($text)
           );
       }

       public function renderStatus(string $status): string
       {
           $variant = match(strtolower($status)) {
               'published', 'active' => 'success',
               'draft', 'pending' => 'warning',
               'archived', 'inactive' => 'secondary',
               default => 'primary'
           };
           return $this->renderBadge($status, $variant);
       }

       public function renderDate(\DateTimeInterface $date, string $format = 'Y-m-d'): string
       {
           return htmlspecialchars($date->format($format));
       }
   }
   ```

3. **Register in DI container:**
   ```php
   // filepath: src/dependencies.php (add to existing file)
   // Line: ~150 (after FormatterService registration)
   ```

   ```php
   // View Rendering Service
   Core\Services\ViewRendererServiceInterface::class => DI\autowire(
       Core\Services\BootstrapViewRendererService::class
   ),
   ```

**Deliverable:**
- New `ViewRendererService` with Bootstrap implementation
- Registered in DI container
- ✅ All HTML generation centralized

---

## **Phase 3: Refactor FormatterService (Data Only)**
**Goal:** Remove HTML rendering & sanitization from formatters

### Tasks:

1. **Update `FormatterInterface` - REMOVE `sanitize()`:**
   ```php
   // filepath: src/Core/Formatters/FormatterInterface.php
   ```

   ```php
   <?php

   declare(strict_types=1);

   namespace Core\Formatters;

   /**
    * Contract for data transformation formatters (NOT HTML rendering)
    *
    * This interface is for pure data transformations like:
    * - Case conversion (uppercase, lowercase)
    * - Normalization (trim, remove special chars)
    * - Format conversion (phone number formats, date formats)
    *
    * For HTML rendering, use ViewRendererService instead.
    */
   interface FormatterInterface
   {
       /**
        * Transform the given value according to the formatter's strategy
        *
        * @param mixed $value The value to transform
        * @param array<string, mixed> $options Additional formatting options
        * @return mixed The transformed value (usually string, but can be other types)
        */
       public function format(mixed $value, array $options = []): mixed;

       /**
        * Get the unique name identifier for this formatter
        *
        * @return string The formatter name
        */
       public function getName(): string;

       /**
        * Check if this formatter supports the given value type
        *
        * @param mixed $value The value to check
        * @return bool True if the formatter can handle this value
        */
       public function supports(mixed $value): bool;
   }
   ```

2. **DELETE `BadgeStatusFormatter.php`:**
   ```bash
   # This is now ViewRendererService::renderStatus()
   rm src/Core/Formatters/BadgeStatusFormatter.php
   ```

3. **Update `FormatterRegistry` to remove BadgeStatusFormatter:**
   ```php
   // filepath: src/dependencies.php
   // Remove BadgeStatusFormatter from FormatterRegistry constructor
   ```

4. **Update all remaining formatters to remove `sanitize()`:**
   - `TextFormatter.php`
   - `EmailFormatter.php`
   - etc.

   Just delete the `sanitize()` method from each class.

**Deliverable:**
- ✅ `FormatterInterface` is now **pure data transformation**
- ❌ No HTML in formatters
- ❌ No `sanitize()` method

---

## **Phase 4: Update Config & Renderer Integration**
**Goal:** Use `ViewRendererService` in list/form rendering

### Tasks:

1. **Update field config naming convention:**
   ```php
   // filepath: src/App/Features/Testy/Config/testy_fields.php
   // OLD (Phase 4 migration):
   ```

   **BEFORE:**
   ```php
   'super_powers' => [
       'label' => 'testy.super_powers',
       'data_transformer' => 'json_array',

       'list' => [
           'sortable' => false,
           'formatter' => function ($value) {  // ❌ OLD NAME
               $badges = array_map(function ($role) {
                   return '<span class="badge bg-primary">'
                        . htmlspecialchars($role) . '</span>';
               }, $value);
               return implode('', $badges);
           },
       ],
   ]
   ```

   **AFTER:**
   ```php
   'super_powers' => [
       'label' => 'testy.super_powers',
       'data_transformer' => 'json_array',

       'list' => [
           'sortable' => false,
           'renderer' => 'badges',  // ✅ NEW: Reference to ViewRendererService method
           'renderer_options' => [
               'variant' => 'primary'
           ],
           // OR use closure for custom rendering:
           // 'renderer' => fn($value, $renderer) => $renderer->renderBadges($value, 'info'),
       ],
   ]
   ```

2. **Update `BootstrapListRenderer` to use `ViewRendererService`:**
   ```php
   // filepath: src/Core/List/Renderer/BootstrapListRenderer.php
   // Line: ~40 (constructor)
   ```

   ```php
   public function __construct(
       protected ThemeServiceInterface $themeService,
       protected FormatterService $formatterService,
       protected ViewRendererServiceInterface $viewRenderer, // ← ADD THIS
       protected ConfigService $configService,
       protected LoggerInterface $logger
   ) {
       // ...existing code...
   }
   ```

   **Line: ~150 (renderCellValue method):**
   ```php
   private function renderCellValue($value, array $columnConfig): string
   {
       // Priority 1: View renderer (HTML output)
       if (isset($columnConfig['renderer'])) {
           $renderer = $columnConfig['renderer'];

           // Option A: String reference to ViewRendererService method
           if (is_string($renderer) && method_exists($this->viewRenderer, $renderer)) {
               $options = $columnConfig['renderer_options'] ?? [];
               return $this->viewRenderer->$renderer($value, ...$options);
           }

           // Option B: Closure that receives ViewRendererService
           if (is_callable($renderer)) {
               return $renderer($value, $this->viewRenderer);
           }
       }

       // Priority 2: Data formatter (non-HTML transformation)
       if (isset($columnConfig['formatter'])) {
           $formatter = $columnConfig['formatter'];
           if (is_callable($formatter)) {
               $value = $formatter($value);
           }
       }

       // Fallback: Safe HTML escape
       if (is_array($value)) {
           return htmlspecialchars(json_encode($value));
       }

       return htmlspecialchars((string)$value);
   }
   ```

3. **Update DI registration for BootstrapListRenderer:**
   ```php
   // filepath: src/dependencies.php
   // Line: ~XXX (find BootstrapListRenderer registration)
   ```

   ```php
   Core\List\Renderer\BootstrapListRenderer::class => DI\autowire()
       ->constructorParameter('viewRenderer', DI\get(Core\Services\ViewRendererServiceInterface::class)),
   ```

**Deliverable:**
- ✅ Config uses `'renderer'` for HTML, `'formatter'` for data
- ✅ `BootstrapListRenderer` integrates `ViewRendererService`
- ✅ All HTML generation goes through dedicated service

---

## **Phase 5: Testing & Validation**
**Goal:** Ensure no regressions, verify separation of concerns

### Tasks:

1. **Unit tests for `ViewRendererService`:**
   ```php
   // filepath: tests/Unit/Services/BootstrapViewRendererServiceTest.php
   ```

   ```php
   public function testRenderBadges(): void
   {
       $renderer = new BootstrapViewRendererService($this->themeService);
       $result = $renderer->renderBadges(['admin', 'editor'], 'primary');

       $this->assertStringContainsString('<span class="badge bg-primary">admin</span>', $result);
       $this->assertStringContainsString('<span class="badge bg-primary">editor</span>', $result);
   }
   ```

2. **Integration tests for list rendering:**
   - Create `TestyListType` test
   - Verify `super_powers` field renders badges correctly
   - Verify data transformers still work

3. **Manual verification:**
   - [ ] Visit `/testy` list page
   - [ ] Verify `super_powers` column shows badges
   - [ ] Verify no HTML in data layer
   - [ ] Check browser console for errors

4. **Code review checklist:**
   - [ ] No `<span>`, `<a>`, or HTML tags in `FormatterService`
   - [ ] No `sanitize()` method in `FormatterInterface`
   - [ ] All config files use `'renderer'` for HTML
   - [ ] `DataSanitizer` is only place for input sanitization
   - [ ] `ViewRendererService` registered in DI container

**Deliverable:**
- ✅ Test suite passes
- ✅ Manual QA complete
- ✅ Architecture review approved

---

## **Phase 6: Documentation & Migration Guide**
**Goal:** Help future developers understand the new architecture

### Tasks:

1. **Update Copilot instructions:**
   ```markdown
   // filepath: .github/copilot-instructions.md
   // Add section:

   ### Formatting vs Rendering

   **FormatterService** (Data transformation):
   - Use for: uppercase, lowercase, trim, phone number formatting
   - Returns: Plain text or primitives
   - Examples: `TextFormatter`, `EmailFormatter`

   **ViewRendererService** (HTML generation):
   - Use for: badges, links, icons, status indicators
   - Returns: HTML strings
   - Examples: `renderBadge()`, `renderCheckmark()`

   **Config Convention:**
   ```php
   'field_name' => [
       'formatters' => ['trim', 'lowercase'], // Data transformation
       'list' => [
           'renderer' => 'badges',            // HTML rendering
       ]
   ]
   ```
   ```

2. **Create migration guide for existing features:**
   ```markdown
   // filepath: docs/migrations/formatter-to-renderer.md

   # Migrating from Formatter to Renderer

   ## Before:
   ```php
   'list' => [
       'formatter' => function($v) { return '<span class="badge">'.$v.'</span>'; }
   ]
   ```

   ## After:
   ```php
   'list' => [
       'renderer' => 'badge',
       'renderer_options' => ['variant' => 'primary']
   ]
   ```
   ```

3. **Add architecture diagram:**
   ```
   ┌──────────────────────────────────────────────────────────────┐
   │ INPUT LAYER                                                  │
   │ DataSanitizer → Security (XSS prevention, trim, strip tags) │
   └──────────────────────────────────────────────────────────────┘
                            ↓
   ┌──────────────────────────────────────────────────────────────┐
   │ VALIDATION LAYER                                             │
   │ ValidatorRegistry → Business rules enforcement               │
   └──────────────────────────────────────────────────────────────┘
                            ↓
   ┌──────────────────────────────────────────────────────────────┐
   │ DOMAIN LAYER                                                 │
   │ DataTransformerService → Storage ↔ App format                │
   │ FormatterService → Data transformation (case, trim)         │
   │ DataNormalizerService → Type coercion                       │
   └──────────────────────────────────────────────────────────────┘
                            ↓
   ┌──────────────────────────────────────────────────────────────┐
   │ PRESENTATION LAYER                                           │
   │ ViewRendererService → HTML generation (badges, links)       │
   └──────────────────────────────────────────────────────────────┘
   ```

**Deliverable:**
- ✅ Updated documentation
- ✅ Migration guide for developers
- ✅ Architecture diagrams

---

## Timeline Estimate

| Phase | Duration | Complexity |
|-------|----------|------------|
| Phase 1: Audit | 2-4 hours | Low |
| Phase 2: Create ViewRenderer | 3-5 hours | Medium |
| Phase 3: Refactor Formatters | 2-3 hours | Low |
| Phase 4: Update Config/Renderer | 4-6 hours | High |
| Phase 5: Testing | 3-5 hours | Medium |
| Phase 6: Documentation | 2-3 hours | Low |
| **TOTAL** | **16-26 hours** | **2-3 days** |

---

## Summary: What's Changing?

### ✅ **Your Validation Is Perfect - No Changes Needed**
- `ValidatorRegistry` ✓
- `Validator` ✓
- All validator classes ✓

### 🔧 **FormatterService Needs Surgery:**

**BEFORE (Mixed Concerns):**
```php
FormatterService {
    - TextFormatter ✅ (data transformation)
    - EmailFormatter ✅ (data transformation)
    - BadgeStatusFormatter ❌ (HTML rendering)
}
```

**AFTER (Single Responsibility):**
```php
FormatterService {
    - TextFormatter ✅ (data transformation ONLY)
    - EmailFormatter ✅ (data transformation ONLY)
}

ViewRendererService {
    - renderBadge() ✅ (HTML generation)
    - renderCheckmark() ✅ (HTML generation)
    - renderStatus() ✅ (HTML generation)
}
```

---

Ready to start with **Phase 1: Audit**? I can help generate the grep commands and spreadsheet template.