<?php

declare(strict_types=1);

return [
    'forms' => [
        'testy' => 'App\Features\Testy\Form\TestyFormType',
        'post' => 'App\Features\Post\Form\PostFormType',
        //'posts' => 'App\Features\Post\Form\PostFormType',
        // Add more as needed: 'users' => 'App\Features\Users\Form\UsersFormType',
    ],
    'lists' => [
        'testy' => 'App\Features\Testy\List\TestyListType',
        //'posts' => 'App\Features\Post\List\PostListType',
        // Add more as needed: 'users' => 'App\Features\Users\List\UsersListType',
    ],
];
