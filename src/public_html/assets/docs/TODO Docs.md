


need to add all the fields resourced to generate lang.


need to update generate fields... we remove 'testy.' and 'common.'




need to get Status and SuperPowers to work, display and update and insert


need to continue with Abstract logic for view, edit, list and delete







we need to create additional generators
--- xxxx_metadata
--- xxxx_list_view - very generic like ...really just common fields
--- xxxx_edit_view - very generic like ...really just common fields
--- xxxx_list_fields - very generic with dummy fields of each type
--- xxxx_edit_fields - very generic with dummy fields of each type


### Generators
    --- MigrationGenerator - Done        - make:migration xxxx     - *CreateXxxxTable.php
    --- SeederGenerator - Done           - make:seeder xxxx        - XxxxSeeder.php
    --- EntityGenerator - Done           - make:entity xxxx        - Xxxx.php
    --- RepositoryGenerator - Revisit    - make:repository xxxx    - XxxxRepository.php, XxxxRepositoryInterface.php,
    --- ConfigFieldsGenerator - Done     - make:config-fields xxxx - Config\xxxx_fields.php
    --- FeatureGenerator - Done          - feature:move            - moves file to appropriate location
    ---
    --- ConfigListViewGenerator - Done   - make:config-list-view   - Config\xxxx_list_view.php ---- ConfigViewListGenerator
    --- ConfigEditViewGenerator - Done   - make:config-edit-view   - Config\xxxx_edit_view.php
    --- ConfigListFieldsGenerator - Done - make:config-list-fields - Config\xxxx_list_fields.php
    --- ConfigEditFieldsGenerator - Done - make:config-edit-fields - Config\xxxx_edit_fields.php
#### Run migrations
    ---,,,,,

SessionAuthenticationService needs to use `UserService` to get rig of errors


we need to fix `MigrationGenerator.php`
```php
// currently it generated this:
            $table->array('roles')
                    ->nullable(false)
                    ->comment('JSON encoded array of user roles/permissions');
            $table->enum('status')
                    ->default('A')
                    ->comment('P=Pending, A=Active, S=Suspended, B=Banned, D=Deleted');
// INSTEAD OF this:
            $table->json('roles') // CHANGED: Replaced array() with json()
                    ->nullable(false)
                    ->comment('JSON encoded array of user roles/permissions');
            $table->char('status', 1) // This was previously changed from enum()
                    ->default('A')
                    ->comment('P=Pending, A=Active, S=Suspended, B=Banned, D=Deleted');

```


















Add Store


###

### User Table
- Does not have a Foreign key to stores

### Store Table
- A Store a Foreign key to User Table, the store owner


So far
Users:
- We can edit/update a user
- We can add/insert a new user

Testy:
- We can edit/update a user
- We can add/insert a new user









- major problem
    - testy seeder not populating table just one, the check for unique is wrong






if account/
    all testy records for a user
    all user records for a user ----- should always be just 1
    galleries
    comments

if store/ or nothing thus CORE
    all testy records for a store regardless of user
    all user records for a store regardless of user  --- danger danger - only storeOwner and admin
    galleries













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