<?php

/**
 * Logger configuration
 */

declare(strict_types=1);

return [
    'development' => [
        'debug_mode' => true,
        'sampling_rate' => 1.0,
        'min_level' => 100, // DEBUG
        'directory' => __DIR__ . '/../../logs',
        'rotation' => true,     // Use daily log rotation
        'retention_days' => 30, // Keep logs for 30 days
    ],
    'production' => [
        'debug_mode' => false,
        'sampling_rate' => 0.1, // Log only 10% of normal requests
        'min_level' => 300,     // WARNING level and higher
        'directory' => __DIR__ . '/../../logs',
        'rotation' => true,
        'retention_days' => 90, // Keep logs longer in production
    ],
];
