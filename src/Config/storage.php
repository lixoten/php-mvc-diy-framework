<?php

declare(strict_types=1);

/**
 * Storage Configuration
 *
 * This configuration focuses solely on multi-tenant image storage and processing.
 * All images are stored with SHA-256 hash filenames for deduplication and integrity.
 */

return [
    /**
     * Default storage provider.
     * Only 'local' is supported for images in this simplified setup.
     */
    'default' => $_ENV['STORAGE_PROVIDER'] ?? 'local',

    // GENERIC FILE STORAGE (for StorageProviderInterface)
    'local' => [
        'base_path' => $_ENV['STORAGE_BASE_PATH'] ?? __DIR__ . '/../../public_html',
        'base_url' => $_ENV['STORAGE_BASE_URL'] ?? '',
    ],

    // MULTI-TENANT IMAGE STORAGE
    'multi_tenant_images' => [
        // 'public_html_root' => $_ENV['IMAGE_PUBLIC_ROOT'] ?? __DIR__ . '/../../public_html',
        // 'storage_root' => $_ENV['IMAGE_STORAGE_ROOT'] ?? __DIR__ . '/../../storage',
        'public_base_url' => $_ENV['IMAGE_BASE_URL'] ?? '/store',
    ],

     // IMAGE PROCESSING PRESETS
    'image_presets' => [
        'thumbs' => [
            'width' => 150,
            'height' => 150,
            'quality' => 85,
            'crop' => true,
        ],
        'web' => [
            'width' => 800,
            'height' => null, // Maintain aspect ratio
            'quality' => 90,
            'crop' => false,
        ],
        'large' => [
            'width' => 1200,
            'height' => null,
            'quality' => 92,
            'crop' => false,
        ],
        'original' => [
            'width' => null,
            'height' => null,
            'quality' => 100,
            'crop' => false,
        ],
    ],

    // IMAGE GENERATION SETTINGS
    'image_generation' => [
        'preferred_formats' => ['avif', 'webp', 'jpg'],
    ],

    // ✅ IMAGE UPLOAD CONSTRAINTS
    'upload' => [
        'max_size' => 5242880, // 5MB
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/avif',
        ],
    ],
];
