<?php

declare(strict_types=1);

// src/Config/view.php
return [
    // View engine settings
    'engine' => $_ENV['VIEW_ENGINE'] ?? 'php', // php, twig, blade, etc.

    // Path settings
    'paths' => [
        'views' => dirname(__DIR__) . '/App/Views',
        'cache' => dirname(dirname(__DIR__)) . '/storage/cache/views',
        'compiled' => dirname(dirname(__DIR__)) . '/storage/framework/views',
    ],

    // Layouts
    'layouts' => [
        'default' => 'layouts/base5simple',
        'auth' => 'layouts/auth',
        'admin' => 'layouts/admin',
        'error' => 'layouts/error',
    ],

    // CSS Frameworks (renamed from 'themes')
    'css_frameworks' => [
        'default' => $_ENV['DEFAULT_CSS_FRAMEWORK'] ?? 'bootstrap',
        // 'form' => $_ENV['FORM_CSS_FRAMEWORK'] ?? 'bootstrap',
        'available' => [
            'bootstrap' => [
                'css' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
                'js' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
            ],
        ],
    ],

    // Form display settings
    'form' => [
        'css_framework' => $_ENV['FORM_CSS_FRAMEWORK'] ?? 'bootstrap',
        'layout_type' => $_ENV['FORM_LAYOUT_TYPE'] ?? 'none',
        'error_display' => $_ENV['FORM_ERROR_DISPLAY'] ?? 'inline',
        'themes' => [
            'default' => [
                'css' => ''
            ],
            'dotted' => [
                'css' => '/assets/css/themes/forms/dotted.css',
                'class' => 'form-theme-dotted'  // Class applied to form container
            ],
            'rounded' => [
                'css' => '/assets/css/themes/forms/rounded.css',
                'class' => 'form-theme-rounded'
            ]
        ]
    ],


    // Visual Themes (to be implemented later)
    'visual_themes' => [
        'active' => $_ENV['VISUAL_THEME'] ?? '',
        'available' => [
            'standard' => [
                'css' => '',
            ],
            'christmas' => [
                'for_framework' => 'bootstrap', // Which framework this theme works with
                'css' => '/assets/css/themes/christmas-theme.css',
            ],
            'halloween' => [
                'for_framework' => 'bootstrap',
                'css' => '/assets/css/themes/halloween-theme.css',
            ],
        ],
    ],

    // Assets
    'assets' => [
        'url' => $_ENV['ASSET_URL'] ?? '/assets',
        'version' => $_ENV['ASSET_VERSION'] ?? '1.0',
        'cache_bust' => filter_var($_ENV['ASSET_CACHE_BUST'] ?? true, FILTER_VALIDATE_BOOLEAN),
    ],

    // Caching
    'cache' => [
        'enabled' => filter_var($_ENV['VIEW_CACHE'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'ttl' => $_ENV['VIEW_CACHE_TTL'] ?? 3600, // seconds
    ],

    // Extensions
    'extensions' => [
        'enabled' => true,
        'auto_escape' => true,
    ],
];
