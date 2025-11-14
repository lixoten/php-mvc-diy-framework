The `TextFormatter` is a versatile tool for manipulating and displaying text within your framework, offering several options to control how a string is presented.

### `TextFormatter` Overview

**File:** [`src/Core/Formatters/TextFormatter.php`]TextFormatter.php )

The `TextFormatter` allows you to:
*   Handle `null` values gracefully.
*   Truncate long strings.
The `TextFormatter` is a versatile tool for manipulating and displaying text within your framework, offering several options to control how a string is presented.

### `TextFormatter` Overview

**File:** [`src/Core/Formatters/TextFormatter.php`]TextFormatter.php )

The `TextFormatter` allows you to:
*   Handle `null` values gracefully.
*   Truncate long strings.
*   Apply various text transformations (uppercase, lowercase, capitalize, etc.).
*   Append custom suffixes.

It supports `string`, `numeric` values, and `null`.

### Available Options for `TextFormatter::transform()`

You can pass an `$options` array to the `transform` method to customize its behavior. Here are the available options:

1.  **`null_value` (string, default: `''`)**
    *   **Purpose:** Specifies what string to return if the input `$value` is `null`.
    *   **Example:** `['null_value' => 'N/A']`

2.  **`max_length` (int, default: `null`)**
    *   **Purpose:** If set, truncates the string to this maximum length.
    *   **Example:** `['max_length' => 50]`

3.  **`truncate_suffix` (string, default: `'...'`)**
    *   **Purpose:** The suffix to append when a string is truncated due to `max_length`.
    *   **Example:** `['max_length' => 50, 'truncate_suffix' => '[...]']`

4.  **`transform` (string, default: `null`)**
    *   **Purpose:** Applies a specific string transformation. Only one transformation can be active.
    *   **Available Values:**
        *   `'uppercase'`: Converts the entire string to uppercase.
        *   `'lowercase'`: Converts the entire string to lowercase.
        *   `'capitalize'`: Converts the first character of the string to uppercase, and the rest to lowercase.
        *   `'title'`: Converts the first character of each word to uppercase.
        *   `'trim'`: Removes whitespace from both ends of the string.
        *   `'last2char_upper'`: Converts the last two characters of the string to uppercase.
    *   **Example:** `['transform' => 'uppercase']`

5.  **`suffix` (string, default: `null`)**
    *   **Purpose:** Appends a custom string to the end of the transformed text, regardless of truncation. This is applied *after* `max_length` and `transform`.
    *   **Example:** `['suffix' => ' USD']`

### Usage Example

```php
<?php

declare(strict_types=1);

namespace App\Examples;

use Core\Formatters\TextFormatter;

// Assume TextFormatter is instantiated, e.g., via DI
$textFormatter = new TextFormatter();

// Basic usage
echo $textFormatter->transform('hello world'); // Output: hello world

// With null value
echo $textFormatter->transform(null, ['null_value' => 'No Content']); // Output: No Content

// Truncation
echo $textFormatter->transform('This is a very long text that needs to be truncated.', ['max_length' => 20]);
// Output: This is a very lon...

// Truncation with custom suffix
echo $textFormatter->transform('Another long sentence.', ['max_length' => 10, 'truncate_suffix' => '...read more']);
// Output: Another lo...read more

// Uppercase transformation
echo $textFormatter->transform('hello world', ['transform' => 'uppercase']); // Output: HELLO WORLD

// Title case transformation
echo $textFormatter->transform('this is a title', ['transform' => 'title']); // Output: This Is A Title

// Custom suffix
echo $textFormatter->transform('Price', ['suffix' => ': $100']); // Output: Price: $100

// Combination (max_length, transform, suffix)
echo $textFormatter->transform(
    'super important secret message',
    [
        'max_length' => 15,
        'truncate_suffix' => '...',
        'transform' => 'uppercase',
        'suffix' => ' (CONFIDENTIAL)'
    ]
);
// Output: SUPER IMPOR... (CONFIDENTIAL)

?>
```