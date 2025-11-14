# `PhoneNumberFormatter` Documentation

The `PhoneNumberFormatter` is a specialized formatter for displaying phone numbers in various region-aware and region-agnostic styles, leveraging the powerful `libphonenumber` library from Google.

### `PhoneNumberFormatter` Overview

**File:** [`src/Core/Formatters/PhoneNumberFormatter.php`](PhoneNumberFormatter.php)

The `PhoneNumberFormatter` allows you to:
*   Handle `null` or empty phone number values gracefully.
*   Format phone numbers into national, international, or custom delimited styles (dashes, dots, spaces).
*   Parse numbers with an optional region context for more accurate formatting.
*   Ensure the output is HTML-safe for display.
*   Gracefully fall back to sanitized digits if a number cannot be parsed.

This formatter adheres strictly to the **Single Responsibility Principle (SRP)** by focusing solely on preparing phone number strings for display, without concerns for validation, data storage, or form input.

---

### Supported Options

The `PhoneNumberFormatter` supports the following options:

*   **`format`** (type: `string`, default: `'default'`)
    *   Specifies the desired output format for the phone number.
    *   **`'default'`**: (Constant: `PhoneNumberFormatter::FORMAT_DEFAULT`)
        *   Formats the number to `NATIONAL` format if its detected region matches the provided `region` option (or inferred region).
        *   Formats to `INTERNATIONAL` format if the regions do not match.
    *   **`'dashes'`**: (Constant: `PhoneNumberFormatter::FORMAT_DASHES`)
        *   Formats the number into a national style with digits separated by hyphens (e.g., `123-456-7890`). This is a region-agnostic visual style applied after initial national formatting.
    *   **`'dots'`**: (Constant: `PhoneNumberFormatter::FORMAT_DOTS`)
        *   Formats the number into a national style with digits separated by dots (e.g., `123.456.7890`). This is a region-agnostic visual style.
    *   **`'spaces'`**: (Constant: `PhoneNumberFormatter::FORMAT_SPACES`)
        *   Formats the number into a national style with digits separated by spaces (e.g., `123 456 7890`). This is a region-agnostic visual style.
    *   **`'international'`**: (Not a direct constant, handled by default logic)
        *   Equivalent to always formatting to `PhoneNumberFormat::INTERNATIONAL` regardless of region context.
    *   **`'international_dashes'`**: (Not a direct constant)
        *   Formats to international format, then applies dashes. *Not directly implemented as a distinct case in the current formatter, would need custom logic if desired.*

*   **`region`** (type: `string`, default: Inferred from `RegionContextService`)
    *   An optional 2-letter ISO 3166-1 alpha-2 country code (e.g., `'US'`, `'GB'`, `'PT'`).
    *   This region is used by `libphonenumber` to assist in parsing numbers that do not include an explicit country calling code. If not provided, it falls back to the region retrieved from the `RegionContextService`.

---

### Usage in Field Configuration

To use the `PhoneNumberFormatter`, define it in the `formatters` section of your field configuration (e.g., in `src/App/Features/Testy/Config/testy_fields_root.php` or `src/Config/render/field_base.php`).

#### Example: Default Formatting (Region-aware)

The formatter will automatically decide between national and international format based on the number's detected region and the `region` option.

````php
// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\Config\render\field_base.php
// ...existing code...
'telephone' => [
    'label' => 'base.telephone',
    'list' => [
        'sortable' => false,
    ],
    'formatters' => [
        'phone' => [
            // 'format' => 'default', // This is the default, can be omitted
            // 'region' => 'US', // Optional: provide a specific region context
        ],
    ],
    // ...other field configurations...
],
// ...existing code...