<?php

declare(strict_types=1);

return [
    // Application settings
    'name' => $_ENV['APP_NAME'] ?? 'MVC Lixo',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',

    // Environment settings
    'env' => $_ENV['APP_ENV'] ?? 'development', // production, development, testing
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),

    // Time and locale settings
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
    'locale' => $_ENV['APP_LOCALE'] ?? 'en',
    'fallback_locale' => 'en',

    // Security
    'secret' => $_ENV['APP_KEY'], // No Fallback needed: 'xxx';
    'cipher' => 'AES-256-CBC',

    // Session
    'session' => [
        'lifetime' => $_ENV['SESSION_LIFETIME'] ?? 120, // minutes
        'secure' => filter_var($_ENV['SESSION_SECURE_COOKIE'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'same_site' => $_ENV['SESSION_SAME_SITE'] ?? 'lax',
    ],

    // Error handling
    'errors' => [
        'display' => filter_var($_ENV['DISPLAY_ERRORS'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'log' => filter_var($_ENV['LOG_ERRORS'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'detail_level' => $_ENV['ERROR_DETAIL'] ?? 'full', // none, basic, full
    ],

    // Paths
    'paths' => [
        'base' => dirname(__DIR__),
        'public' => dirname(dirname(__DIR__)) . '/public',
        'storage' => dirname(dirname(__DIR__)) . '/storage',
        'logs' => dirname(dirname(__DIR__)) . '/storage/logs',
    ],

    // Feature flags
    'features' => [
        'cache' => filter_var($_ENV['FEATURE_CACHE'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'api' => filter_var($_ENV['FEATURE_API'] ?? true, FILTER_VALIDATE_BOOLEAN),
    ],

    // Dynamic-me
    'pages' => [
        'about' => [
            'title' => 'About Us',
            'content' => '<p>Welcome to our company! We are dedicated to...</p>',
            'meta_description' => 'Learn about our company and mission',
            'last_updated' => '2023-06-15'
        ],
        'terms' => [
            'title' => 'Terms of Service',
            'content' => '<p>By using our service, you agree to the following terms...</p>',
            'meta_description' => 'Our terms of service and legal information',
            'last_updated' => '2023-04-22'
        ],
        // Add more pages as needed
    ]
];
