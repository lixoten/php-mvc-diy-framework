
# eeeeee

## Error$ ERR-DEV85 =
    Message:
        Form Warning: No Fields/Columns found. - ERR-DEV85
    File:
        - AbstractFormType->validateFormFields(array $fields)
    Reason:
        You need to define a minimum of one field.
```php
        'render_options' => [
            'form_fields' => [
                'title',
            ],
        ],
```
        - Check Config File in `view_options`
          - D:\xampp\htdocs\my_projects\mvclixo\src\Config\`view_options`\_______edit.php


## Error$ ERR-DEV88 =
    Message:
        Removed invalid fields. "Page: _____, Entity: ______"
    File:
        FieldRegistryService->filterAndValidateFields($fieldArray)
    Reason:
        Odds are the Field in question does not exist in Page or in Entity or in base CONFIG file
        example:
        Page : D:\xampp\htdocs\my_projects\mvclixo\src\Config\list_fields\posts_list.php
        Entity: D:\xampp\htdocs\my_projects\mvclixo\src\Config\list_fields\posts.php
        Base : D:\xampp\htdocs\my_projects\mvclixo\src\Config\list_fields\base.php
    Fix:
        in `form_layout` array or `form_hidden_fields` array in 'Page', but check others('Entity`, `Base`) too.
````php
        'form_layout'        => [
            [
                'title' => 'Your Message',
                'fields' => ['title', 'favorite_ccword'], // Fix: SEE the TYPO in 'favorite_ccword'
                'divider' => true,
            ],
        ],
```
or
```php
    'form_hidden_fields' => [
        'testy_id',
        'testy_user_id',
        'testyddddddddddddddddd_user_id',
    ]
```




Error$ ERR-DEV90 =
    Message:
        Warning: Form Warning: Removed empty section at index 1 - ERR-DEV90
    File:
        AbstractFormType->validateAndFixLayoutFields($array $layout, array $availableFields)
    Reason:
        An empty section refers to fields missing. You either are missing it. Or it as a single value that was invalid and got removed by ERR-DEV89, thus causing this error.
     - Check:
          - Controller $options if you are using it. ELSE
          - Check Config File in `view_options`
          - D:\xampp\htdocs\my_projects\mvclixo\src\Config\`view_options`\_______edit.php
````php
        'layout'        => [
            [
                'title' => 'Your Message',
                'fields' => ['favorite_ccword'], // SEE the TYPO in 'favorite_ccword'
                'divider' => true,
            ],
        ],
```


Error$ ERR-DEV91 =
    Message:
        Warning: Form Warning: Removed empty section at index 1 - ERR-DEV91
    File:
        'AbstractFormType->validateAndFixLayoutFields($array $layout, array $availableFields)'
    Reason:
        An empty section refers to fields missing. You are missing it, thus causing this error.
    - Check:
          - Controller $options if you are using it. ELSE
          - Check Config File in `view_options`
          - D:\xampp\htdocs\my_projects\mvclixo\src\Config\`view_options`\_______edit.php
````php
        // this is empty
        'layout'        => [
        ],
        // this is empty TOO
        'layout'        => [
            [
                'title' => 'Your Message',
                'divider' => true,
            ],
        ],
```