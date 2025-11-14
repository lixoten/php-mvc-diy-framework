# `EmailFormatter` Documentation

The `EmailFormatter` is designed for the display-oriented transformation of email addresses within your framework, offering an option to mask parts of the email for privacy.

### `EmailFormatter` Overview

**File:** [`src/Core/Formatters/EmailFormatter.php`](EmailFormatter.php)

The `EmailFormatter` allows you to:
*   Handle `null` or empty email values gracefully.
*   Mask email addresses to obscure sensitive information (e.g., `u***@d***.com`).
*   Ensure the output is HTML-safe for display.

This formatter adheres strictly to the **Single Responsibility Principle (SRP)** by focusing solely on preparing email strings for display, without concerns for validation, data storage, or form input.

---

### Supported Options

The `EmailFormatter` currently supports the following option:

*   **`mask`** (type: `bool`, default: `false`)
    *   If set to `true`, the formatter will mask the local part (username) and the domain part of the email address. The first character of the local part and the first character of the top-level domain will remain visible, with the rest replaced by asterisks.
    *   Example: `rudyten@gmail.com` becomes `r******@g****.com`

---

### Usage in Field Configuration

To use the `EmailFormatter`, define it in the `formatters` section of your field configuration (e.g., in `src/App/Features/Testy/Config/testy_fields_root.php` or `src/Config/render/field_base.php`).

#### Example: Basic Email Display (no masking)

When `mask` is `false` (or omitted, as `false` is the default), the email is displayed as is, but still HTML-escaped.

````php
// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\App\Features\Testy\Config\testy_fields_root.php
// ...existing code...
'primary_email' => [
    'label' => 'testy.primary_email',
    'list' => [
        'sortable' => true,
    ],
    'formatters' => [
        'email' => [
            // 'mask' => false, // This is the default, can be omitted
        ],
    ],
    // ...other field configurations...
],
// ...existing code...
````

**Result for rudyten@gmail.com:** `rudyten@gmail.com`

### Example: Masked Email Display
To mask the email address for privacy in list views:

````php
<?php
// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\App\Features\Testy\Config\testy_fields_root.php
// ...existing code...
'primary_email' => [
    'label' => 'testy.primary_email',
    'list' => [
        'sortable' => true,
    ],
    'formatters' => [
        'email' => [
            'mask' => true, // Enable email masking
        ],
    ],
    // ...other field configurations...
],
// ...existing code...
````

**Result for rudyten@gmail.com:** `r******@g****.com`

##### How it Handles Values
- `null` **or empty values:** If the input value is `null`, an empty string, or evaluated as empty, the formatter will return an empty string.
- **Valid email strings:**
    - If `mask` is `false` (default), the original email string is returned after being cast to a string.
    - If `mask` is `true`, the email is transformed into its masked representation (e.g., `rudyten@gmail.com` becomes `r******@g****.com`).
- **HTML Safety:** The `AbstractFormatter` base class ensures that the final output from the `transform` method is automatically passed through `htmlspecialchars()` before being returned to the renderer, protecting against XSS vulnerabilities in the displayed content.