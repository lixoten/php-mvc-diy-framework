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
    ]
];
