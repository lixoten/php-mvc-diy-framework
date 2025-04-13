<?php

return [
    'development' => [
        'rate_limits' => [
            'login' => ['limit' => 5, 'window' => 300],          // 5 attempts per 5 minutes
            'registration' => ['limit' => 3, 'window' => 1800],   // 3 attempts per 30 minutes
            'password_reset' => ['limit' => 3, 'window' => 900],  // 3 attempts per 15 minutes
            'email_verification' => ['limit' => 5, 'window' => 900], // 5 attempts per 15 minutes
            'activation_resend' => ['limit' => 3, 'window' => 1800], // 3 attempts per 30 minutes
        ],
        'brute_force_protection' => [
            'enabled' => true,  // Global toggle
            'login' => [
                'max_attempts' => 5,
                'ip_max_attempts' => 15,
                'lockout_time' => 900
            ],
        ],
        'captcha' => [
            'enabled' => false,  // Master toggle for CAPTCHA functionality
            'provider' => 'google',
            'site_key' => $_ENV['RECAPTCHA_SITE_KEY'] ?? '',
            'secret_key' => $_ENV['RECAPTCHA_SECRET_KEY'] ?? '',
            'version' => 'v2',  // 'v2' or 'v3'
            'score_threshold' => 0.5,  // For v3 only
            'thresholds' => [
                'login' => 3,  // Show CAPTCHA after 3 failed attempts
                'registration' => 2,
                'password_reset' => 2,
                'activation_resend' => 3,
                'email_verification' => 3
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
            'login' => ['limit' => 10, 'window' => 600],
            'registration' => ['limit' => 6, 'window' => 3200],
            'password_reset' => ['limit' => 3, 'window' => 900],
            'email_verification' => ['limit' => 5, 'window' => 900],
            'activation_resend' => ['limit' => 3, 'window' => 1800],
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
