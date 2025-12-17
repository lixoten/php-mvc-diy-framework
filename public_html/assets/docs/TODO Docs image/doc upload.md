## "Field Attribute Validation"
In layman's term validate `Config\list_fields\testys_edit.php` and remove invalid and for Dev env, display an error and log it too.
At the moment we are allowed to have attributes that make no sense in a field type. ex. Placeholder attr in a checkbox.

- Enhancement: Add validation to ensure field config (as in: src\Config\list_fields\testys_edit.php)
- attributes are compatible with field type
Feature: Field attribute/type compatibility check in config validation
Task: Validate field config attributes against allowed attributes for each field type

Reuse existing FieldType files and src\Core\Form\Field\Type\AbstractFieldType.php to validate




## **conditional required or dependent validation**
classic **business rule**:
> “If a specific option is selected (e.g., a checkbox or radio), then another field (e.g., a reason input or textarea) becomes required.”
>
### 1. **Config-Driven Approach**

Add a `conditional_required` validator to your config for the dependent field:

```php
'reason' => [
    'label' => 'Reason (required if Special Item is checked)',
    'form' => [
        'type' => 'textarea',
        'required' => false, // Not always required
        'validators' => [
            'conditional_required' => [
                'depends_on' => 'special_item_checkbox', // The controlling field
                'value' => true, // Or the value that triggers the requirement
                'message' => 'Please provide a reason if Special Item is selected.',
            ],
        ],
    ],
],
```

---

### 2. **Custom Validator: ConditionalRequiredValidator**

Implement a validator that checks the value of another field:

```php
<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

use Core\Form\Validation\Rules\AbstractValidator;

/**
 * Requires a field if another field has a specific value.
 */
class ConditionalRequiredValidator extends AbstractValidator
{
    /**
     * @param mixed $value
     * @param array<string, mixed> $options
     * @param array<string, mixed> $allData
     * @return bool
     */
    public function validate($value, array $options = [], array $allData = []): bool
    {
        $dependsOn = $options['depends_on'] ?? null;
        $triggerValue = $options['value'] ?? true;

        if ($dependsOn && ($allData[$dependsOn] ?? null) == $triggerValue) {
            return !empty($value);
        }

        return true; // Not required if condition not met
    }

    public function getName(): string
    {
        return 'conditional_required';
    }
}
```
- Make sure your validation system passes the full form data (`$allData`) to the validator.

---

### 3. **Frontend (Optional, for UX)**

- Use JavaScript to show/hide or mark the dependent field as required in real time for better user experience.
- But always enforce the rule on the server side as well.

---

### 4. **Summary Table**

| Step                | What to Do                                    |
|---------------------|-----------------------------------------------|
| Config              | Add `conditional_required` validator          |
| Validator           | Implement logic to check another field’s value|
| Frontend (optional) | JS to show/hide or require field dynamically  |

---

**Summary:**
- Define the dependency in your config with a `conditional_required` validator.
- Implement a validator class that checks the controlling field’s value.
- (Optional) Add JS for live feedback, but always enforce on the server.

Let me know if you want a ready-to-paste validator or config snippet for your setup!




## Administrative Dashboard // TODO
    - See: Login Brute Force Protection - 3. TODO Future Brute Force Protection Enhancements
## Advanced Security Features  // TODO
    - See: Login Brute Force Protection - 3. TODO Future Brute Force Protection Enhancements
    --See: TODO - Rate Limiting Enhancements
## Reporting and Analytics  // TODO
    - See: Login Brute Force Protection - 3. TODO Future Brute Force Protection Enhancements

Login Brute Force Protection - 3. TODO Future Brute Force Protection Enhancements



# ViewAs - User Impersonation Feature - TODO Future Implementation Guide // TODO
    - See - ViewAs - User Impersonation Feature - TODO Future Implementation Guide