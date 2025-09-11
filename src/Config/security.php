<?php

return [
    'development' => [
        'rate_limits' => [
            'enabled' => true,  // Global toggle for rate limiting
            'endpoints' => [
                'contact_index' => ['limit' => 91, 'window' => 300],          // 5 attempts per 5 minutes
                'contact_direct' => ['limit' => 95, 'window' => 300],          // 5 attempts per 5 minutes
                'login' => ['limit' => 95, 'window' => 300],          // 5 attempts per 5 minutes
                'registration' => ['limit' => 93, 'window' => 1800],   // 3 attempts per 30 minutes
                'password_reset' => ['limit' => 93, 'window' => 900],  // 3 attempts per 15 minutes
                'email_verification' => ['limit' => 95, 'window' => 900], // 5 attempts per 15 minutes
                'activation_resend' => ['limit' => 93, 'window' => 1800], // 3 attempts per 30 minutes
            ],
            'path_mappings' => [
                '/contact' => 'contact_direct',
                '/contact/direct' => 'contact_direct',
                '/registration' => 'registration',
                '/login' => 'login',
                '/forgot-password' => 'password_reset',
                '/verify-email/resend' => 'activation_resend',
                '/verify-email/verify' => 'email_verification'
            ]
        ],
        'brute_force_protection' => [
            'enabled' => true,  // Global toggle
            'login' => [
                'max_attempts' => 95,
                'ip_max_attempts' => 915,
                'lockout_time' => 900
            ],
        ],
        'captcha' => [
            'enabled' => true,         // Master toggle for CAPTCHA functionality
            'force_captcha' => true,    // If true, always show CAPTCHA on all forms // fix-force-captcha '1';
            'provider' => 'google',
            'site_key' => $_ENV['RECAPTCHA_SITE_KEY'] ?? '',
            'secret_key' => $_ENV['RECAPTCHA_SECRET_KEY'] ?? '',
            'version' => 'v2',          // 'v2' or 'v3'
            'score_threshold' => 0.5,   // For v3 only
            'thresholds' => [
                'contact_index' => 92,     // Show CAPTCHA after 3 failed attempts
                'contact_direct' => 92,     // Show CAPTCHA after 3 failed attempts
                'login' => 93,       // Show CAPTCHA after 3 failed attempts
                'registration' => 92,
                'password_reset' => 92,
                'activation_resend' => 93,
                'email_verification' => 93
            ]
        ],
        // foofee
        //     'rate_limits' => [
        //         'login' => [
        //             'max_attempts' => 5,
        //             'ip_max_attempts' => 15,
        //             'lockout_time' => 900 // 15 minutes
        //         ],
        //         'password_reset' => [
        //             'max_attempts' => 3,
        //             'ip_max_attempts' => 10,
        //             'lockout_time' => 1800 // 30 minutes
        //         ],
        //         'registration' => [
        //             'max_attempts' => 3,
        //             'ip_max_attempts' => 10,
        //             'lockout_time' => 3600 // 60 minutes
        //         ],
        //         'activation_resend' => [
        //             'max_attempts' => 3,
        //             'ip_max_attempts' => 9,
        //             'lockout_time' => 3600 // 60 minutes
        //         ],
        //         'email_verification' => [
        //             'max_attempts' => 5,
        //             'ip_max_attempts' => 15,
        //             'lockout_time' => 900 // 15 minutes
        //         ]
        //     ]
    ],
    'production' => [
        'rate_limits' => [
            'enabled' => true,  // Global toggle for rate limiting
            'endpoints' => [
                'login' => ['limit' => 9190, 'window' => 600],
                'registration' => ['limit' => 96, 'window' => 3200],
                'password_reset' => ['limit' => 93, 'window' => 900],
                'email_verification' => ['limit' => 95, 'window' => 900],
                'activation_resend' => ['limit' => 93, 'window' => 1800],
            ],
            'path_mappings' => [
                '/registration' => 'registration',
                '/login' => 'login',
                '/forgot-password' => 'password_reset',
                '/verify-email/resend' => 'activation_resend',
                '/verify-email/verify' => 'email_verification'
            ]
        ],
        // 'rate_limits' => [
        //     'login' => [
        //         'max_attempts' => 5,
        //         'ip_max_attempts' => 15,
        //         'lockout_time' => 1800 // 30 minutes (stricter in production)
        //     ],
        //     // Other settings would be copied here...
        // ]
    ],
];
