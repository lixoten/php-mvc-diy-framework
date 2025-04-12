<?php

return [
    'development' => [
        'rate_limits' => [
            'login' => [
                'max_attempts' => 5,
                'ip_max_attempts' => 15,
                'lockout_time' => 900 // 15 minutes
            ],
            'password_reset' => [
                'max_attempts' => 3,
                'ip_max_attempts' => 10,
                'lockout_time' => 1800 // 30 minutes
            ],
            'registration' => [
                'max_attempts' => 3,
                'ip_max_attempts' => 10,
                'lockout_time' => 3600 // 60 minutes
            ],
            'activation_resend' => [
                'max_attempts' => 3,
                'ip_max_attempts' => 9,
                'lockout_time' => 3600 // 60 minutes
            ],
            'email_verification' => [
                'max_attempts' => 5,
                'ip_max_attempts' => 15,
                'lockout_time' => 900 // 15 minutes
            ]
        ]
    ],
    'production' => [
        'rate_limits' => [
            'login' => [
                'max_attempts' => 5,
                'ip_max_attempts' => 15,
                'lockout_time' => 1800 // 30 minutes (stricter in production)
            ],
            // Other settings would be copied here...
        ]
    ],
    'captcha' => [
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
];
