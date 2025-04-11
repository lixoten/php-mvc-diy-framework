<?php

return [
    'development' => [
        'test_email_recipient' => $_ENV['TEST_EMAIL_RECIPIENT'] ?? 'aaalixoten@gail.commmmmmm',
        'from_email' => 'noreply@mvclixo.tv',
        'from_name' => 'MVCLixo',
        'providers' => [
            'default' => $_ENV['MAIL_API_DEFAULT'] ?? 'mailgun',
            'mailgun' => [
                'api_key' => $_ENV['MAILGUN_API_KEY'] ?? '',
                'domain' => $_ENV['MAILGUN_DOMAIN'] ?? '',
                'base_url' => 'https://api.mailgun.net/v3'
            ],
            'smtp' => [
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => $_ENV['SMTP_USERNAME'] ?? '',
                'password' => $_ENV['SMTP_PASSWORD'] ?? '',
            ]
        ]
    ],
    'production' => [
        'from_email' => 'noreply@mvclixo.tv',
        'from_name' => 'MVCLixo',
        'providers' => [
            'default' => $_ENV['MAIL_API_DEFAULT'] ?? 'mailgun',
            'mailgun' => [
                'api_key' => $_ENV['MAILGUN_API_KEY'] ?? '',
                'domain' => $_ENV['MAILGUN_DOMAIN'] ?? '',
                'base_url' => 'https://api.mailgun.net/v3'
            ]
        ]
    ]
];
