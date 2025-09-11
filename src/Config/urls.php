<?php

declare(strict_types=1);

return [
    'development' => [
        'core' => [
            'home' => ['/', 'Home'],
            'about' => ['/about', 'About Us'],
            'contact' => ['/contact', 'Contact Us']
        ],
        'account' => [
            'dashboard' => ['/account/dashboard', 'Dashboard'],
            'profile' => ['/account/profile', 'Profile']
        ],
        'stores' => [
            'dashboard' => ['/stores/dashboard', 'Store Dashboard'],
            'posts' => [
                'url' => '/stores/posts',
                'view' => 'Account/Stores/Posts/index',
                'label' => 'Posts'
            ],
            'posts.create' => [
                'url' => '/stores/posts/create',
                'view' => 'Account/Stores/Posts/create',
                'label' => 'Create Post'
            ],
            'posts.edit' => [
                'url' => '/stores/posts/edit/{id}',
                'view' => 'Account/Stores/Posts/edit',
                'label' => 'Edit Post'
            ]
        ]
    ],
    'production' => [
        // Same structure but possibly with absolute URLs or different paths
    ]
];
