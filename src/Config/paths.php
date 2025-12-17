<?php

declare(strict_types=1);

/**
 * Application path configuration.
 *
 * These paths are relative to the project root and can be overridden via environment variables.
 */
return [
    // Web-accessible directory (entry point location)
    'public' => $_ENV['APP_PUBLIC_PATH'] ?? 'public_html',

    // Private storage directory (uploads, cache, logs)
    'storage' => $_ENV['APP_STORAGE_PATH'] ?? 'storage',

    // Variable runtime data (cache, compiled views, sessions)
    'var' => $_ENV['APP_VAR_PATH'] ?? 'var',

    // Configuration files
    'config' => $_ENV['APP_CONFIG_PATH'] ?? 'src/Config',

    // Application source code
    'src' => $_ENV['APP_SRC_PATH'] ?? 'src',
];
