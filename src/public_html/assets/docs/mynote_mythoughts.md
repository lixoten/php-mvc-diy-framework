
<!-- Testy_edit

We have:
Lists
Forms
and non form/lists - Think of a page that displays data but is neigher a form or a list. like a detail page

we have:
'form' = []
'list' = []

how can we make it generic?
```php
    'title' => [
        'label'         => 'testys.title---localTitle-l&f',
        'formatter'     => 'xxxx',
        'validators'    => 'xxxx',
        'form' => [
            // 'purpose'       => 'form',
            'type'          => 'display',
            'attributes'    => [
                'placeholder' => 'testys.title.placeholder', //.Enter a testy title'
                'required' => true,
                'minlength' => 5,
                'maxlength' => 30,
                'data-char-counter'     => true, // js-feature
                'data-live-validation'  => true  // js-feature
            ],
        ],
        'list' => [
            'sortable' => true,
        ],
    ],
``` -->


My Pages can be composed of List Pages, Edit Pages, Detail Pages etc....
Example of a Edit Page might be Edit Testys Records
--- So Controller would be TestysController ..... >>> AbstractCrudController >>> Controller
--- So editAction  would show a form and let user update for a record
--- So listAction  would show a list of all the record

But i do not wanna be forced to one ot the other

i want to be able to sow maybe multiple forms or multiple lists, or a mix of list and forms in a single page


I am thinking i need some typeResolver



In dependencies.php
```php

    // Section - Form types
    'App\Features\Testys\Form\TestysFormType' => \DI\autowire()
        ->constructorParameter('fieldRegistryService', \DI\get('Core\Services\FieldRegistryService'))
        ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface'))
        ->constructorParameter('captchaService', \DI\get('Core\Security\Captcha\CaptchaServiceInterface')),

    'App\Features\Posts\Form\PostsFormType' => \DI\autowire()
        ->constructorParameter('fieldRegistryService', \DI\get('Core\Services\FieldRegistryService'))
        ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface'))
        ->constructorParameter('captchaService', \DI\get('Core\Security\Captcha\CaptchaServiceInterface')),

    //...


    // Section - List types

    'App\Features\Testys\List\TestysListType' => \DI\autowire()
        ->constructorParameter('fieldRegistryService', \DI\get('Core\Services\FieldRegistryService'))
        ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface')),

    'App\Features\Posts\List\PostsListType' => \DI\autowire()
        ->constructorParameter('fieldRegistryService', \DI\get('Core\Services\FieldRegistryService'))
        ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface')),

    //...
```